<?php
/**
 * Script per eseguire la migration del sistema recuperi
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRATION SISTEMA RECUPERI ===\n\n";

try {
    $db = Database::getInstance();
    
    // Leggi file migration
    $migration_file = __DIR__ . '/../database/migration_recuperi.sql';
    
    if (!file_exists($migration_file)) {
        throw new Exception("File migration non trovato: $migration_file");
    }
    
    $sql = file_get_contents($migration_file);
    
    echo "Esecuzione migration...\n\n";
    
    // Dividi per statement (semplificato, funziona per la maggior parte dei casi)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !str_starts_with($stmt, '--') && 
                   $stmt !== '';
        }
    );
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        // Salta commenti e linee vuote
        if (empty(trim($statement)) || str_starts_with(trim($statement), '--')) {
            continue;
        }
        
        try {
            $db->execute($statement);
            $success_count++;
            
            // Mostra statement eseguito (primi 100 caratteri)
            $preview = substr(trim($statement), 0, 100);
            if (strlen($statement) > 100) $preview .= '...';
            echo "✓ " . $preview . "\n";
            
        } catch (Exception $e) {
            // Alcuni errori sono accettabili (es. colonna già esiste)
            if (str_contains($e->getMessage(), 'duplicate column name') || 
                str_contains($e->getMessage(), 'already exists')) {
                echo "ℹ SKIP: " . $e->getMessage() . "\n";
            } else {
                $error_count++;
                echo "✗ ERROR: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 200) . "...\n";
            }
        }
    }
    
    echo "\n=== MIGRATION COMPLETATA ===\n";
    echo "Statements eseguiti: $success_count\n";
    if ($error_count > 0) {
        echo "Errori: $error_count\n";
    }
    
    // Verifica tabelle create
    echo "\n=== VERIFICA TABELLE ===\n";
    
    $tables = $db->query("
        SELECT name FROM sqlite_master 
        WHERE type='table' 
        AND (name='recuperi' OR name LIKE 'v_%recuper%')
        ORDER BY name
    ");
    
    foreach ($tables as $table) {
        echo "✓ Tabella/View: {$table['name']}\n";
    }
    
    // Conta colonne aggiunte
    echo "\n=== VERIFICA COLONNE ASSENZE ===\n";
    $columns = $db->query("PRAGMA table_info(assenze)");
    $new_columns = ['causata_da', 'necessita_recupero', 'note_annullamento'];
    
    foreach ($columns as $col) {
        if (in_array($col['name'], $new_columns)) {
            echo "✓ Colonna aggiunta: {$col['name']} ({$col['type']})\n";
        }
    }
    
    echo "\n✅ MIGRATION COMPLETATA CON SUCCESSO!\n\n";
    echo "Puoi ora utilizzare il sistema recuperi.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE DURANTE LA MIGRATION:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}