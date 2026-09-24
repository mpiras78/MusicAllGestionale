#!/usr/bin/env php
<?php
/**
 * Set Admin Password - Diagnostic Version
 * Versione standalone senza dipendenze da bootstrap.php
 */

if (php_sapi_name() !== 'cli') {
    die("❌ This script must be run from command line\n");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║   🔑 MUSICALL - Set Admin Password (DEBUG)    ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

$password = $argv[1] ?? 'admin123';

// Step 1: Check bootstrap
echo "[1/6] Checking bootstrap.php...\n";
$bootstrapPath = __DIR__ . '/includes/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    echo "❌ bootstrap.php not found at: $bootstrapPath\n";
    exit(1);
}
echo "✅ bootstrap.php found\n";

// Step 2: Load bootstrap
echo "[2/6] Loading bootstrap.php...\n";
try {
    require_once $bootstrapPath;
    echo "✅ bootstrap.php loaded\n";
} catch (Exception $e) {
    echo "❌ Error loading bootstrap: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Get database instance
echo "[3/6] Getting database instance...\n";
try {
    if (!class_exists('Database')) {
        echo "❌ Database class not found\n";
        echo "   Available classes: " . implode(', ', get_declared_classes()) . "\n";
        exit(1);
    }
    
    $db = Database::getInstance();
    echo "✅ Database instance obtained\n";
} catch (Exception $e) {
    echo "❌ Error getting database: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Check users table
echo "[4/6] Checking users table...\n";
try {
    $tableCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='users' LIMIT 1"
    )->fetch();
    
    if (!$tableCheck) {
        echo "⚠️  Users table doesn't exist, creating...\n";
        
        $createSQL = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role TEXT DEFAULT 'segreteria' CHECK(role IN ('admin', 'docente', 'segreteria')),
    active INTEGER DEFAULT 1,
    last_login DATETIME,
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL;
        
        $db->query($createSQL);
        echo "✅ Users table created\n";
    } else {
        echo "✅ Users table exists\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking/creating table: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 5: Hash password and check admin
echo "[5/6] Processing password...\n";
try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "✅ Password hashed\n";
    echo "  Hash preview: " . substr($hash, 0, 30) . "...\n";
    
    // Check if admin exists
    $adminResult = $db->query(
        "SELECT id, username FROM users WHERE username = 'admin' LIMIT 1"
    );
    
    $adminUser = null;
    if ($adminResult) {
        $adminUser = $adminResult->fetch();
    }
    
    if ($adminUser) {
        echo "  Admin user exists (ID: {$adminUser['id']})\n";
    } else {
        echo "  Admin user doesn't exist\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 6: Create or update admin
echo "[6/6] Creating/updating admin user...\n";
try {
    if ($adminUser) {
        echo "  Updating existing admin...\n";
        $db->execute(
            "UPDATE users SET password = ?, updated_at = datetime('now') WHERE username = 'admin'",
            [$hash]
        );
        echo "✅ Admin password updated\n";
    } else {
        echo "  Creating new admin...\n";
        $db->execute(
            "INSERT INTO users (username, password, email, role, active, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
            ['admin', $hash, 'admin@musicall.it', 'admin', 1]
        );
        echo "✅ Admin user created\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Final verification
echo "\n";
echo "✅ VERIFICATION\n";
try {
    $verify = $db->query(
        "SELECT id, username, email, role FROM users WHERE username = 'admin' LIMIT 1"
    )->fetch();
    
    if ($verify) {
        echo "  ID: " . $verify['id'] . "\n";
        echo "  Username: " . $verify['username'] . "\n";
        echo "  Email: " . $verify['email'] . "\n";
        echo "  Role: " . $verify['role'] . "\n";
    } else {
        echo "  ⚠️  Verification failed - user not found\n";
    }
} catch (Exception $e) {
    echo "  ⚠️  Verification error: " . $e->getMessage() . "\n";
}

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║         ✅ PASSWORD SET SUCCESS              ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";
echo "🌐 Login at: http://localhost:8000/login.php\n";
echo "👤 Username: admin\n";
echo "🔑 Password: " . $password . "\n";
echo "\n";

exit(0);
?>
