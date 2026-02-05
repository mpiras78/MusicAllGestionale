<?php
/**
 * Ricrea tabella activation_tokens con nuova struttura
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== RICREAZIONE TABELLA activation_tokens ===\n\n";

$db = Database::getInstance();

try {
    // 1. Drop tabella esistente
    echo "1. Drop tabella esistente... ";
    $db->execute("DROP TABLE IF EXISTS activation_tokens");
    echo "✓ OK\n";
    
    // 2. Crea nuova tabella con url_token e activation_code
    echo "2. Creazione nuova tabella... ";
    $sql = "
    CREATE TABLE activation_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        url_token VARCHAR(64) NOT NULL,
        activation_code VARCHAR(6) NOT NULL,
        expires_at DATETIME NOT NULL,
        used INTEGER DEFAULT 0,
        used_at DATETIME,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->execute($sql);
    echo "✓ OK\n";
    
    // 3. Crea indici
    echo "3. Creazione indici... ";
    $db->execute("CREATE INDEX idx_activation_url_token ON activation_tokens(url_token, expires_at)");
    $db->execute("CREATE INDEX idx_activation_code ON activation_tokens(activation_code)");
    $db->execute("CREATE INDEX idx_activation_user ON activation_tokens(user_id)");
    echo "✓ OK\n";
    
    echo "\n✅ TABELLA RICREATA CON SUCCESSO!\n";
    
    // Verifica struttura
    echo "\n=== STRUTTURA TABELLA ===\n";
    $columns = $db->query("PRAGMA table_info(activation_tokens)");
    foreach ($columns as $col) {
        echo "✓ {$col['name']} ({$col['type']})\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}