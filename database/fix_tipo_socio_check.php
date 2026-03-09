<?php
// Fix CHECK constraint on 'soci.tipo_socio' replacing 'allievo' with 'socio'
// Usage: php fix_tipo_socio_check.php

require_once __DIR__ . '/../includes/bootstrap.php';

$dbPath = __DIR__ . '/musicall.sqlite';
if (!file_exists($dbPath)) {
    echo "Database not found at $dbPath\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get create SQL for soci
    $row = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='soci'")->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "Table 'soci' not found.\n";
        exit(1);
    }

    $createSql = $row['sql'];
    // Only replace the specific CHECK values, to avoid accidental changes
    $oldList = "('allievo', 'docente', 'esterno', 'admin')";
    $newList = "('socio', 'docente', 'esterno', 'admin')";
    $combinedList = "('allievo', 'socio', 'docente', 'esterno', 'admin')";

    if (strpos($createSql, $oldList) === false) {
        echo "No legacy CHECK list found in table definition; nothing to do.\n";
        exit(0);
    }

    $newCreateSqlStep1 = str_replace($oldList, $combinedList, $createSql);
    // Build new table name for step1
    $tmpTable1 = 'soci_tmp1_' . time();

    echo "STEP 1: creating temporary table $tmpTable1 allowing both 'allievo' and 'socio'...\n";
    $newCreateSqlStep1 = preg_replace('/CREATE TABLE\s+soci\s*/i', 'CREATE TABLE ' . $tmpTable1 . ' ', $newCreateSqlStep1, 1);

    $pdo->beginTransaction();
    // disable foreign keys to allow drop/rename
    $pdo->exec('PRAGMA foreign_keys = OFF');

    // create tmp1 table
    $pdo->exec($newCreateSqlStep1);

    // copy data into tmp1 (no normalization yet)
    $pdo->exec("INSERT INTO $tmpTable1 SELECT * FROM soci");

    // drop old table and rename tmp1 to soci
    $pdo->exec('DROP TABLE soci');
    $pdo->exec("ALTER TABLE $tmpTable1 RENAME TO soci");

    // Now normalize: convert legacy 'allievo' to 'socio'
    echo "STEP 2: normalizing existing tipo_socio values (allievo -> socio)...\n";
    $pdo->exec("UPDATE soci SET tipo_socio = 'socio' WHERE tipo_socio = 'allievo'");

    // We stop here: table 'soci' now allows both 'allievo' and 'socio'.
    // Re-enable foreign keys and commit.
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->commit();

    echo "Recreated 'soci' table allowing both 'allievo' and 'socio'. Normalization done.\n";

    // validate
    $chk = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='soci'")->fetch(PDO::FETCH_ASSOC);
    echo $chk['sql'] . "\n";

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
