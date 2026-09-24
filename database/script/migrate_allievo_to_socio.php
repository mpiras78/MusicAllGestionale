<?php
/**
 * Migration helper: rename socio_id -> socio_id across tables
 *
 * Usage (recommended):
 * 1) Create a DB copy (script does this by default):
 *    php migrate_socio_to_socio.php /absolute/path/to/database/musicall.sqlite
 *    (if no path provided, uses database/musicall.sqlite in repo root)
 *
 * 2) Review output and test the app pointing to the copy (or inspect the copy manually).
 *
 * Notes:
 * - The script attempts `ALTER TABLE <tbl> RENAME COLUMN socio_id TO socio_id`.
 *   If the SQLite version doesn't support it or it fails, the script falls back to
 *   creating a new table with the adjusted schema, copies data (mapping socio_id->socio_id),
 *   recreates indices and triggers (best-effort), then renames the table.
 * - Always run on a copy. Do not run directly on production DB without a verified backup.
 */

if (php_sapi_name() !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$argv0 = array_shift($argv);
$srcPath = isset($argv[0]) ? $argv[0] : __DIR__ . DIRECTORY_SEPARATOR . 'musicall.sqlite';
if (!file_exists($srcPath)) {
    echo "Source DB not found: $srcPath\n";
    exit(1);
}

$ts = date('Ymd_His');
$copyPath = dirname($srcPath) . DIRECTORY_SEPARATOR . 'musicall_mig_copy_' . $ts . '.sqlite';
if (!copy($srcPath, $copyPath)) {
    echo "Failed to copy DB to $copyPath\n";
    exit(1);
}

echo "Created DB copy: $copyPath\n";

try {
    $pdo = new PDO('sqlite:' . $copyPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure foreign keys off for schema operations
    $pdo->exec('PRAGMA foreign_keys = OFF');

    $tablesStmt = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_ASSOC);

    $renamed = [];

    // Backup and drop views to avoid dependency errors during schema changes
    $views = [];
    $viewsStmt = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='view'");
    $viewRows = $viewsStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($viewRows as $v) {
        $views[$v['name']] = $v['sql'];
    }
    if (count($views) > 0) {
        echo "Dropping " . count($views) . " views to allow schema changes...\n";
        foreach (array_keys($views) as $vname) {
            try { $pdo->exec("DROP VIEW IF EXISTS \"$vname\""); } catch (Exception $e) {
                echo "  ! Failed to drop view $vname: " . $e->getMessage() . "\n";
            }
        }
    }
    foreach ($tables as $t) {
        $table = $t['name'];
        // skip sqlite_sequence etc
        if (stripos($table, 'sqlite_') === 0) continue;

        $colsStmt = $pdo->query("PRAGMA table_info(`$table`)");
        $cols = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
        $colNames = array_map(function($r){return $r['name'];}, $cols);
        if (!in_array('socio_id', $colNames)) {
            continue; // nothing to do
        }

        echo "Processing table: $table (contains socio_id)\n";

        // First, try the easy rename (SQLite >= 3.25 supports it)
        $alterSupported = true;
        try {
            $sqlAlter = "ALTER TABLE \"$table\" RENAME COLUMN \"socio_id\" TO \"socio_id\"";
            $pdo->exec($sqlAlter);
            echo "  -> ALTER TABLE RENAME COLUMN succeeded for $table\n";
            $renamed[] = $table;
            continue;
        } catch (Exception $e) {
            echo "  -> ALTER TABLE RENAME COLUMN failed for $table: " . $e->getMessage() . "\n";
            $alterSupported = false;
        }

        // Fallback: reconstruct table
        echo "  -> Falling back to recreate-table method for $table\n";
        $createRowStmt = $pdo->prepare("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?");
        $createRowStmt->execute([$table]);
        $createSql = $createRowStmt->fetchColumn();
        if (!$createSql) {
            echo "  ! Could not retrieve CREATE TABLE for $table. Skipping.\n";
            continue;
        }

        // Build new CREATE TABLE SQL for a unique temp table (avoid collisions)
        $tmpTable = '__tmp_mig_' . $table . '_' . date('U');
        $newCreateSql = str_ireplace("CREATE TABLE \"$table\"", "CREATE TABLE \"$tmpTable\"", $createSql);
        // rename column name inside CREATE SQL
        $newCreateSql = str_replace('socio_id', 'socio_id', $newCreateSql);

        // Ensure no leftover tmp table and execute create
        try {
            $pdo->exec("DROP TABLE IF EXISTS \"$tmpTable\"");
            $pdo->exec($newCreateSql);
        } catch (Exception $e) {
            echo "  ! Failed to create temp table for $table: " . $e->getMessage() . "\n";
            continue;
        }

        // Prepare column lists for copy
        $origCols = $colNames; // original order
        $insertCols = [];
        $selectCols = [];
        foreach ($origCols as $c) {
            if ($c === 'socio_id') {
                $insertCols[] = 'socio_id';
                $selectCols[] = 'socio_id AS socio_id';
            } else {
                $insertCols[] = $c;
                $selectCols[] = $c;
            }
        }

        $insertColsList = implode(', ', array_map(function($c){ return "\"$c\""; }, $insertCols));
        $selectColsList = implode(', ', $selectCols);

        // Copy data
        $copySql = "INSERT INTO \"$tmpTable\" ($insertColsList) SELECT $selectColsList FROM \"$table\"";
        try {
            $pdo->exec($copySql);
        } catch (Exception $e) {
            echo "  ! Data copy failed for $table: " . $e->getMessage() . "\n";
            // attempt to drop tmp and continue
            $pdo->exec("DROP TABLE IF EXISTS \"$tmpTable\"");
            continue;
        }

        // Recreate indices (best-effort) for this table
        $indexesStmt = $pdo->prepare("SELECT name, sql FROM sqlite_master WHERE type='index' AND tbl_name = ? AND sql NOT NULL");
        $indexesStmt->execute([$table]);
        $indexes = $indexesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($indexes as $ix) {
            $ixSql = $ix['sql'];
            // change table name and column name occurrences
            $ixSqlNew = str_ireplace(" ON \"$table\"", " ON \"$tmpTable\"", $ixSql);
            $ixSqlNew = str_replace('socio_id', 'socio_id', $ixSqlNew);
            try {
                $pdo->exec($ixSqlNew);
            } catch (Exception $e) {
                echo "    ! Recreate index {$ix['name']} failed: " . $e->getMessage() . "\n";
            }
        }

        // Recreate triggers (best-effort)
        $triggersStmt = $pdo->prepare("SELECT name, sql FROM sqlite_master WHERE type='trigger' AND tbl_name = ?");
        $triggersStmt->execute([$table]);
        $triggers = $triggersStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($triggers as $tr) {
            $trSql = $tr['sql'];
            $trSqlNew = str_ireplace("ON \"$table\"", "ON \"$tmpTable\"", $trSql);
            $trSqlNew = str_replace('socio_id', 'socio_id', $trSqlNew);
            try {
                $pdo->exec($trSqlNew);
            } catch (Exception $e) {
                echo "    ! Recreate trigger {$tr['name']} failed: " . $e->getMessage() . "\n";
            }
        }

        // Drop old table and rename tmp
        try {
            $pdo->exec("DROP TABLE \"$table\"");
            $pdo->exec("ALTER TABLE \"$tmpTable\" RENAME TO \"$table\"");
            echo "  -> Recreated and renamed $table successfully.\n";
            $renamed[] = $table;
        } catch (Exception $e) {
            echo "  ! Final rename for $table failed: " . $e->getMessage() . "\n";
            // leave tmp table for inspection
        }
    }

    // Re-enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Recreate views (best-effort). Update SQL to replace socio -> socio naming.
    if (count($views) > 0) {
        echo "Recreating views...\n";
        foreach ($views as $vname => $vsql) {
            // Adjust common naming in view SQL
            $vsqlNew = str_replace(['soci', 'socio', 'socio_id'], ['soci', 'socio', 'socio_id'], $vsql);
            try {
                $pdo->exec($vsqlNew);
                echo "  -> Recreated view $vname\n";
            } catch (Exception $e) {
                echo "  ! Failed to recreate view $vname: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\nSummary:\n";
    if (count($renamed) === 0) {
        echo "  No tables needed renaming or operation failed.\n";
    } else {
        foreach ($renamed as $r) echo "  - $r\n";
    }

    echo "Migration completed on copy: $copyPath\n";
    echo "Please point your app to this copy for verification.\n";

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
