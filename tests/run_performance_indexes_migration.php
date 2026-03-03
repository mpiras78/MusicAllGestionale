<?php
/**
 * Script per eseguire migration indici performance
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRATION: Performance Optimization Indexes ===\n\n";

try {
    $db = Database::getInstance();
    
    // Leggi file SQL
    $sql = file_get_contents(__DIR__ . '/../database/migration_performance_indexes.sql');
    
    if (!$sql) {
        throw new Exception("Impossibile leggere il file migration_performance_indexes.sql");
    }
    
    // Dividi in statement singoli (rimuovi commenti e statement vuoti)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !str_starts_with($stmt, '--') && 
                   !str_starts_with($stmt, '/*');
        }
    );
    
    echo "Trovati " . count($statements) . " statement da eseguire\n\n";
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $i => $statement) {
        // Salta commenti multi-linea
        if (preg_match('/^\/\*.*\*\/$/s', $statement)) {
            continue;
        }
        
        // Estrai nome indice per output
        if (preg_match('/CREATE INDEX.*?(idx_\w+)/i', $statement, $matches)) {
            $indexName = $matches[1];
            echo "[$i] Creazione indice: $indexName... ";
            
            try {
                $db->execute($statement);
                echo "✓ OK\n";
                $success++;
            } catch (Exception $e) {
                // Ignora errore se indice già esiste
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "⚠ Già esistente\n";
                } else {
                    echo "✗ ERRORE: " . $e->getMessage() . "\n";
                    $errors++;
                }
            }
        } elseif (preg_match('/^SELECT/i', $statement)) {
            // Esegui query di verifica
            echo "\n--- Verifica indici creati ---\n";
            $result = $db->query($statement);
            foreach ($result as $row) {
                echo "  - {$row['table_name']}.{$row['index_name']}\n";
            }
            echo "\n";
        }
    }
    
    echo "\n=== RIEPILOGO ===\n";
    echo "✓ Indici creati con successo: $success\n";
    if ($errors > 0) {
        echo "✗ Errori: $errors\n";
    }
    echo "\n✅ Migration completata!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE FATALE: " . $e->getMessage() . "\n";
    exit(1);
}
