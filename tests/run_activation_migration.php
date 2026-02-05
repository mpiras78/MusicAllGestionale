<?php
/**
 * Esegue migration per sistema attivazione utenti
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRATION: Sistema Attivazione Utenti ===\n\n";

$db = Database::getInstance();

try {
    // 1. Crea tabella activation_tokens
    echo "1. Creazione tabella activation_tokens... ";
    $sql = "
    CREATE TABLE IF NOT EXISTS activation_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token VARCHAR(6) NOT NULL,
        expires_at DATETIME NOT NULL,
        used INTEGER DEFAULT 0,
        used_at DATETIME,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->execute($sql);
    echo "✓ OK\n";
    
    // 2. Crea indici
    echo "2. Creazione indici... ";
    $db->execute("CREATE INDEX IF NOT EXISTS idx_activation_token ON activation_tokens(token, expires_at)");
    $db->execute("CREATE INDEX IF NOT EXISTS idx_activation_user ON activation_tokens(user_id)");
    echo "✓ OK\n";
    
    // 3. Aggiungi colonna force_password_change
    echo "3. Aggiunta colonna force_password_change... ";
    try {
        $db->execute("ALTER TABLE users ADD COLUMN force_password_change INTEGER DEFAULT 0");
        echo "✓ OK\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "⚠ Già esistente\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n✅ MIGRATION COMPLETATA CON SUCCESSO!\n";
    
    // Verifica tabelle
    echo "\n=== VERIFICA TABELLE ===\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('activation_tokens')");
    foreach ($tables as $table) {
        echo "✓ Tabella: " . $table['name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}