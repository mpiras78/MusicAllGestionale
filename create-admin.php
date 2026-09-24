#!/usr/bin/env php
<?php
/**
 * Crea/Verifica Utente Admin
 * Script semplice per risolvere problemi di login
 */

require_once __DIR__ . '/includes/bootstrap.php';

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║    🔐 MUSICALL - Setup Admin Credentials      ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

try {
    $db = Database::getInstance();
    
    // 1. Verifica tabella users
    echo "1️⃣  Checking 'users' table...\n";
    
    $tableCheck = $db->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='users' LIMIT 1"
    )->fetch();
    
    if (!$tableCheck) {
        echo "❌ Table 'users' NOT found!\n";
        echo "   Creazione tabella users...\n";
        
        // Crea tabella users
        $createTableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role TEXT DEFAULT 'segreteria' CHECK(role IN ('admin', 'docente', 'segreteria')),
    active INTEGER DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL;
        
        $db->query($createTableSQL);
        echo "✅ Table 'users' created!\n";
    } else {
        echo "✅ Table 'users' exists\n";
    }
    
    echo "\n";
    
    // 2. Verifica se admin esiste
    echo "2️⃣  Checking admin user...\n";
    
    $adminCheck = $db->query(
        "SELECT id, username, email, role FROM users WHERE username = 'admin' LIMIT 1"
    )->fetch();
    
    if ($adminCheck) {
        echo "✅ Admin user EXISTS:\n";
        echo "   ID: " . $adminCheck['id'] . "\n";
        echo "   Username: " . $adminCheck['username'] . "\n";
        echo "   Email: " . $adminCheck['email'] . "\n";
        echo "   Role: " . $adminCheck['role'] . "\n";
    } else {
        echo "❌ Admin user NOT found - Creating...\n";
        
        // Crea admin con password hashata
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        echo "   Password: admin123\n";
        echo "   Hash: " . substr($hash, 0, 30) . "...\n";
        
        $db->execute(
            "INSERT INTO users (username, password, email, role, active, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
            ['admin', $hash, 'admin@musicall.it', 'admin', 1]
        );
        
        echo "✅ Admin user created successfully!\n";
    }
    
    echo "\n";
    
    // 3. Verifica password
    echo "3️⃣  Verifying password hash...\n";
    
    $userWithHash = $db->query(
        "SELECT id, username, password FROM users WHERE username = 'admin' LIMIT 1"
    )->fetch();
    
    if ($userWithHash) {
        $storedHash = $userWithHash['password'];
        $testPassword = 'admin123';
        
        echo "   Stored hash: " . substr($storedHash, 0, 30) . "...\n";
        
        if (password_verify($testPassword, $storedHash)) {
            echo "✅ Password 'admin123' MATCHES the hash!\n";
        } else {
            echo "❌ Password 'admin123' DOES NOT match!\n";
            echo "   Recreating user with correct hash...\n";
            
            // Elimina e ricrea
            $db->execute("DELETE FROM users WHERE username = 'admin'");
            
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $db->execute(
                "INSERT INTO users (username, password, email, role, active, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
                ['admin', $hash, 'admin@musicall.it', 'admin', 1]
            );
            
            echo "✅ User recreated with correct hash!\n";
        }
    } else {
        echo "❌ Could not find admin user for verification\n";
    }
    
    echo "\n";
    
    // 4. Mostra tutti gli utenti
    echo "4️⃣  All users in database:\n";
    
    $allUsers = $db->query(
        "SELECT id, username, email, role, active FROM users ORDER BY id"
    )->fetchAll();
    
    if (count($allUsers) > 0) {
        foreach ($allUsers as $user) {
            $active = $user['active'] ? '✅' : '❌';
            echo "   $active [{$user['id']}] {$user['username']} ({$user['role']}) - {$user['email']}\n";
        }
    } else {
        echo "❌ No users found!\n";
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════╗\n";
    echo "║         ✅ SETUP COMPLETE                     ║\n";
    echo "╚═══════════════════════════════════════════════╝\n";
    echo "\n";
    echo "🌐 Login credentials:\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "\n";
    echo "🔗 Try login:\n";
    echo "   http://localhost:8000/login.php\n";
    echo "\n";
    echo "⚠️  After successful login, change password at:\n";
    echo "   http://localhost:8000/profile.php\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nDebug info:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
