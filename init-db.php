#!/usr/bin/env php
<?php
/**
 * Script Inizializzazione Database MusicAll
 * Crea le tabelle e popola utenti di default
 */

require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance();

echo "\n";
echo "╔═══════════════════════════════════════════════╗\n";
echo "║  🎵 MUSICALL - Database Initialization       ║\n";
echo "╚═══════════════════════════════════════════════╝\n";
echo "\n";

try {
    // 1. Verifica se le tabelle esistono
    echo "📋 Verifying tables...\n";
    
    $tableCheck = $db->query(
        "SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'"
    )->fetch();
    
    $tableCount = $tableCheck['count'] ?? 0;
    
    if ($tableCount === 0) {
        echo "❌ No tables found - initializing schema...\n\n";
        
        // Leggi schema SQLite
        $schemaFile = __DIR__ . '/database/script/schema_sqlite.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $schema = file_get_contents($schemaFile);
        
        // Esegui schema
        $statements = array_filter(
            array_map('trim', explode(';', $schema)),
            fn($s) => !empty($s) && !preg_match('/^--/', $s)
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $db->query($statement);
                } catch (Exception $e) {
                    // Ignora errori di CREATE TABLE IF EXISTS
                    if (stripos($e->getMessage(), 'already exists') === false) {
                        error_log("Warning: " . $e->getMessage());
                    }
                }
            }
        }
        
        echo "✅ Database schema created!\n\n";
    } else {
        echo "✅ Tables already exist ($tableCount tables found)\n\n";
    }
    
    // 2. Verifica se utente admin esiste
    echo "👤 Checking admin user...\n";
    
    $adminCheck = $db->query(
        "SELECT * FROM users WHERE username = 'admin' LIMIT 1"
    )->fetch();
    
    if (!$adminCheck) {
        echo "❌ Admin user not found - creating...\n";
        
        // Crea utente admin con password hashata
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $db->execute(
            "INSERT INTO users (username, password, email, role, active, created_at) 
             VALUES (?, ?, ?, ?, ?, datetime('now'))",
            ['admin', $hash, 'admin@musicall.it', 'admin', 1]
        );
        
        echo "✅ Admin user created!\n";
        echo "   Username: admin\n";
        echo "   Password: admin123\n";
        echo "   Role: admin\n";
        echo "   Email: admin@musicall.it\n\n";
    } else {
        echo "✅ Admin user exists\n";
        echo "   Email: " . ($adminCheck['email'] ?? 'N/A') . "\n";
        echo "   Role: " . ($adminCheck['role'] ?? 'N/A') . "\n\n";
    }
    
    // 3. Verifica altri utenti di default
    echo "👥 Checking example users...\n";
    
    $usersCheck = $db->query(
        "SELECT COUNT(*) as count FROM users WHERE username != 'admin'"
    )->fetch();
    
    $userCount = $usersCheck['count'] ?? 0;
    
    if ($userCount === 0) {
        echo "⚠️  No other users found\n\n";
    } else {
        echo "✅ Found $userCount other user(s)\n\n";
    }
    
    // 4. Verifica tabelle importanti
    echo "📊 Tables status:\n";
    
    $tables = [
        'users' => '👤 Users',
        'soci' => '👥 Soci',
        'docenti' => '👨‍🏫 Docenti',
        'lezioni' => '📚 Lezioni',
        'assenze' => '❌ Assenze',
        'recuperi' => '🔄 Recuperi',
        'iscrizioni' => '📝 Iscrizioni',
        'activity_log' => '📋 Activity Log',
        'rate_limit_log' => '⏱️  Rate Limit',
    ];
    
    foreach ($tables as $table => $label) {
        $check = $db->query(
            "SELECT COUNT(*) as count FROM sqlite_master WHERE type='table' AND name='$table'"
        )->fetch();
        
        $exists = $check['count'] > 0 ? '✅' : '❌';
        echo "   $exists $label\n";
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════╗\n";
    echo "║           ✅ INITIALIZATION COMPLETE          ║\n";
    echo "╚═══════════════════════════════════════════════╝\n";
    echo "\n";
    echo "🌐 Start server:\n";
    echo "   php -S localhost:8000\n";
    echo "\n";
    echo "🔗 Access app:\n";
    echo "   http://localhost:8000\n";
    echo "\n";
    echo "👤 Login credentials:\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "\n";
    echo "⚠️  IMPORTANT: Change password at first login!\n";
    echo "   http://localhost:8000/profile.php\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
