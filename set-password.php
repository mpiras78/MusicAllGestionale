#!/usr/bin/env php
<?php
/**
 * Set Admin Password - Script Facile
 * Uso: php set-password.php admin123
 */

if (php_sapi_name() !== 'cli') {
    die("❌ This script must be run from command line\n");
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$password = $argv[1] ?? 'admin123';

try {
    // Load bootstrap
    require_once __DIR__ . '/includes/bootstrap.php';
} catch (Exception $e) {
    die("❌ Bootstrap Error: " . $e->getMessage() . "\n");
}

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║     🔑 MUSICALL - Set Admin Password          ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

try {
    $db = Database::getInstance();
    
    // Crea tabella users se non esiste
    $tableCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='users' LIMIT 1"
    )->fetch();
    
    if (!$tableCheck) {
        echo "Creating users table...\n";
        
        $createTableSQL = <<<SQL
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
        
        try {
            $db->query($createTableSQL);
            echo "✅ Users table created\n\n";
        } catch (Exception $e) {
            echo "⚠️  Table creation warning: " . $e->getMessage() . " (might already exist)\n\n";
        }
    } else {
        echo "Users table already exists\n\n";
    }
    
    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Processing:\n";
    echo "  Username: admin\n";
    echo "  Password: " . str_repeat('*', strlen($password)) . "\n";
    echo "  Hash: " . substr($hash, 0, 40) . "...\n\n";
    
    // Verifica se admin esiste
    $adminCheck = null;
    try {
        $result = $db->query(
            "SELECT id FROM users WHERE username = 'admin' LIMIT 1"
        );
        if ($result) {
            $adminCheck = $result->fetch();
        }
    } catch (Exception $e) {
        echo "⚠️  Query check warning: " . $e->getMessage() . "\n";
        $adminCheck = null;
    }
    
    if ($adminCheck) {
        echo "Admin exists - updating password...\n";
        try {
            $db->execute(
                "UPDATE users SET password = ?, updated_at = datetime('now') WHERE username = 'admin'",
                [$hash]
            );
            echo "✅ Password updated\n";
        } catch (Exception $e) {
            echo "❌ Update failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    } else {
        echo "Admin not found - creating with password...\n";
        try {
            $db->execute(
                "INSERT INTO users (username, password, email, role, active, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
                ['admin', $hash, 'admin@musicall.it', 'admin', 1]
            );
            echo "✅ Admin user created\n";
        } catch (Exception $e) {
            echo "❌ Insert failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    // Verifica
    echo "\n";
    echo "✅ Verification:\n";
    try {
        $user = $db->query(
            "SELECT username, email, role FROM users WHERE username = 'admin' LIMIT 1"
        )->fetch();
        
        if ($user) {
            echo "  Username: " . $user['username'] . "\n";
            echo "  Email: " . $user['email'] . "\n";
            echo "  Role: " . $user['role'] . "\n";
        } else {
            echo "  ⚠️  User not found in verification\n";
        }
    } catch (Exception $e) {
        echo "  ⚠️  Verification query failed: " . $e->getMessage() . "\n";
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
    
} catch (Exception $e) {
    echo "\n";
    echo "╔═══════════════════════════════════════════════╗\n";
    echo "║              ❌ ERROR OCCURRED               ║\n";
    echo "╚═══════════════════════════════════════════════╝\n";
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    exit(1);
}

exit(0);
?>
