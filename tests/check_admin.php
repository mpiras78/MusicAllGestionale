<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

echo "=== VERIFICA UTENTE ADMIN ===\n\n";

// Controlla se esiste l'utente admin
$user = $db->queryOne("SELECT * FROM users WHERE username = 'admin'");

if ($user) {
    echo "✅ Utente admin trovato:\n";
    echo "   ID: " . $user['id'] . "\n";
    echo "   Username: " . $user['username'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   Role: " . $user['role'] . "\n";
    echo "   Active: " . $user['active'] . "\n";
    echo "   Password Hash: " . substr($user['password'], 0, 30) . "...\n\n";
    
    // Verifica la password
    $password = 'admin123';
    if (password_verify($password, $user['password'])) {
        echo "✅ Password 'admin123' CORRETTA!\n";
    } else {
        echo "❌ Password 'admin123' NON corrisponde!\n";
        echo "\n🔧 Rigenerando password corretta...\n";
        
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $db->execute("UPDATE users SET password = ? WHERE username = 'admin'", [$newHash]);
        
        echo "✅ Password aggiornata! Riprova il login.\n";
    }
} else {
    echo "❌ Utente admin NON trovato!\n\n";
    echo "🔧 Creazione utente admin...\n";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $db->execute(
        "INSERT INTO users (username, password, email, role, active, created_at) VALUES (?, ?, ?, ?, 1, datetime('now'))",
        ['admin', $password, 'admin@musicall.it', 'admin']
    );
    
    echo "✅ Utente admin creato!\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
}

echo "\n=== FINE VERIFICA ===\n";