#!/usr/bin/env php
<?php
/**
 * Set Admin Password - Minimal Standalone
 * Bypasses bootstrap.php entirely
 */

if (php_sapi_name() !== 'cli') {
    die("❌ This script must be run from command line\n");
}

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║     🔑 MUSICALL - Set Admin Password          ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

$password = $argv[1] ?? 'admin123';

// Get database path from config
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    die("❌ config.php not found\n");
}

require_once $configPath;

echo "[1/3] Connecting to database...\n";

// Database path
$dbPath = __DIR__ . '/database/musicall.sqlite';
if (!file_exists(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
    echo "  Created database directory\n";
}

// Connect to SQLite
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected: $dbPath\n";
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n[2/3] Creating users table (if not exists)...\n";

try {
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
    
    $pdo->exec($createSQL);
    echo "✅ Users table ready\n";
} catch (Exception $e) {
    die("❌ Table creation failed: " . $e->getMessage() . "\n");
}

echo "\n[3/3] Setting admin password...\n";

try {
    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "  Password hashed: " . substr($hash, 0, 30) . "...\n";
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $adminExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminExists) {
        echo "  Updating existing admin user...\n";
        $stmt = $pdo->prepare(
            "UPDATE users SET password = ? WHERE username = 'admin'"
        );
        $stmt->execute([$hash]);
        echo "✅ Admin password updated\n";
    } else {
        echo "  Creating new admin user...\n";
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password, email, role, active) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute(['admin', $hash, 'admin@musicall.it', 'admin', 1]);
        echo "✅ Admin user created\n";
    }
    
    // Verify
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n";
    echo "✅ VERIFICATION\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Username: " . $user['username'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
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
