<?php
/**
 * Database Diagnostic Script
 * Verifica stato del database e credenziali admin
 */

echo "=== DATABASE DIAGNOSTIC ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Controlla file config.php
echo "[1] Verifico config.php...\n";
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    echo "✓ config.php trovato\n";
    require_once $configPath;
    echo "  - DB_PATH definito: " . (defined('DB_PATH') ? DB_PATH : 'NO') . "\n";
} else {
    echo "✗ config.php NON trovato!\n";
    exit(1);
}

// 2. Controlla file database SQLite
echo "\n[2] Verifico database SQLite...\n";
if (defined('DB_PATH') && file_exists(DB_PATH)) {
    echo "✓ Database file trovato: " . DB_PATH . "\n";
    echo "  - Dimensione: " . filesize(DB_PATH) . " bytes\n";
} else {
    echo "✗ Database file NON trovato: " . (defined('DB_PATH') ? DB_PATH : 'UNDEFINED') . "\n";
    exit(1);
}

// 3. Connessione PDO
echo "\n[3] Connessione PDO...\n";
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connessione PDO riuscita\n";
} catch (Exception $e) {
    echo "✗ Errore connessione: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Controlla tabella users
echo "\n[4] Verifico tabella 'users'...\n";
try {
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
    if ($tables) {
        echo "✓ Tabella 'users' trovata\n";
        
        // Schema
        $columns = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
        echo "  Colonne:\n";
        foreach ($columns as $col) {
            echo "    - {$col['name']} ({$col['type']})\n";
        }
    } else {
        echo "✗ Tabella 'users' NON trovata!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Errore: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Query utente admin
echo "\n[5] Query utente admin...\n";
try {
    $stmt = $db->prepare("SELECT id, username, email, password, role, active, created_at, updated_at FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✓ Utente admin trovato:\n";
        echo "  - ID: " . $admin['id'] . "\n";
        echo "  - Username: " . $admin['username'] . "\n";
        echo "  - Email: " . $admin['email'] . "\n";
        echo "  - Password (hash): " . substr($admin['password'], 0, 50) . "...\n";
        echo "  - Role: " . $admin['role'] . "\n";
        echo "  - Active: " . ($admin['active'] ? 'YES' : 'NO') . "\n";
        echo "  - Created: " . $admin['created_at'] . "\n";
        echo "  - Updated: " . $admin['updated_at'] . "\n";
    } else {
        echo "✗ Utente admin NON trovato!\n";
        echo "  Tutti gli utenti nel database:\n";
        
        $all = $db->query("SELECT id, username, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
        if (count($all) > 0) {
            foreach ($all as $user) {
                echo "    - [{$user['id']}] {$user['username']} ({$user['email']}) - {$user['role']}\n";
            }
        } else {
            echo "    ⚠ Nessun utente nel database!\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Errore query: " . $e->getMessage() . "\n";
    exit(1);
}

// 6. Statistiche database
echo "\n[6] Statistiche Database:\n";
try {
    $userCount = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch(PDO::FETCH_ASSOC);
    echo "  - Totale utenti: " . $userCount['cnt'] . "\n";
    
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "  - Totale tabelle: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch(PDO::FETCH_ASSOC);
        echo "    - $table: " . $count['cnt'] . " righe\n";
    }
} catch (Exception $e) {
    echo "⚠ Errore statistiche: " . $e->getMessage() . "\n";
}

echo "\n=== FINE DIAGNOSTIC ===\n";
