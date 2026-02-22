<?php
/**
 * Esegui Migrazione Rate Limiting
 * Crea tabella per protezione brute force
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== Migrazione Rate Limiting ===\n\n";

try {
    $db = Database::getInstance();
    
    // Leggi SQL migration
    $sql = file_get_contents(__DIR__ . '/../database/migration_rate_limiting.sql');
    
    // Esegui migration
    $db->execute($sql);
    
    echo "✅ Tabella rate_limit_log creata con successo\n";
    echo "✅ Indici creati\n\n";
    
    // Verifica tabella
    $result = $db->queryOne("SELECT COUNT(*) as count FROM rate_limit_log");
    echo "✅ Verifica: tabella accessibile (record: {$result['count']})\n\n";
    
    echo "=== Migrazione completata con successo! ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}
