<?php
/**
 * Script per eseguire migration tabella soci_esterni
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRATION SOCI ESTERNI ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Leggi SQL migration
    $sql = file_get_contents(__DIR__ . '/../database/migration_soci_esterni.sql');
    
    if ($sql === false) {
        throw new Exception("Impossibile leggere file migration");
    }
    
    echo "Esecuzione migration...\n";
    
    // Esegui SQL
    $db->exec($sql);
    
    echo "✓ Tabella soci_esterni creata con successo!\n\n";
    
    // Verifica creazione
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='soci_esterni'");
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($table) {
        echo "✓ Verifica: tabella soci_esterni presente nel database\n\n";
        
        // Mostra struttura
        $stmt = $db->query("PRAGMA table_info(soci_esterni)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Struttura tabella:\n";
        foreach ($columns as $col) {
            echo "  - {$col['name']} ({$col['type']})\n";
        }
        
        echo "\n✓ Migration completata con successo!\n";
    } else {
        throw new Exception("Tabella non trovata dopo la creazione");
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}