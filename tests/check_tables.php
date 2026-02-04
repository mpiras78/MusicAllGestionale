<?php
/**
 * Verifica tabelle nel database SQLite
 */

$dbPath = __DIR__ . '/../database/musicall.sqlite';

if (!file_exists($dbPath)) {
    echo "❌ Database non trovato: $dbPath\n";
    exit(1);
}

echo "Database: $dbPath\n\n";

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lista tabelle
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelle presenti nel database:\n";
    echo "==============================\n\n";
    
    if (empty($tables)) {
        echo "❌ Nessuna tabella presente\n";
    } else {
        foreach ($tables as $table) {
            echo "✅ $table\n";
            
            // Conta i record
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "   Record: $count\n";
            
            // Mostra struttura
            $columns = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
            echo "   Colonne: ";
            $colNames = array_map(function($col) { return $col['name']; }, $columns);
            echo implode(', ', $colNames) . "\n\n";
        }
    }
    
    echo "\nDimensione database: " . filesize($dbPath) . " bytes\n";
    
} catch (PDOException $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    exit(1);
}