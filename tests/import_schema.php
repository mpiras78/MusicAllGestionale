<?php
/**
 * Importa schema nel database SQLite
 * Converte automaticamente sintassi MySQL -> SQLite
 */

$dbPath = __DIR__ . '/../database/musicall.sqlite';
$schemaPath = __DIR__ . '/../database/schema.sql';

if (!file_exists($schemaPath)) {
    echo "❌ Schema non trovato: $schemaPath\n";
    exit(1);
}

echo "Importazione schema in SQLite...\n";
echo "Database: $dbPath\n";
echo "Schema: $schemaPath\n\n";

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Leggi lo schema
    $sql = file_get_contents($schemaPath);
    
    // Conversioni MySQL -> SQLite
    $sql = str_replace('ENGINE=InnoDB', '', $sql);
    $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
    $sql = str_replace('DATETIME DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT (datetime(\'now\',\'localtime\'))', $sql);
    $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT (datetime(\'now\',\'localtime\'))', $sql);
    $sql = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $sql);
    $sql = preg_replace('/INT\(\d+\)/', 'INTEGER', $sql);
    $sql = preg_replace('/VARCHAR\(\d+\)/', 'TEXT', $sql);
    $sql = str_replace('TINYINT(1)', 'INTEGER', $sql);
    $sql = str_replace('TEXT COLLATE utf8mb4_unicode_ci', 'TEXT', $sql);
    $sql = str_replace('UNIQUE KEY', 'UNIQUE', $sql);
    
    // Dividi in statement singoli
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { 
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt); 
        }
    );
    
    echo "Trovati " . count($statements) . " statement SQL\n\n";
    
    $created = 0;
    $errors = 0;
    
    foreach ($statements as $i => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            // Estrai nome tabella
            if (preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "Creazione tabella: $tableName... ";
                
                $db->exec($statement);
                echo "✅\n";
                $created++;
            } else if (preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "Inserimento dati in: $tableName... ";
                
                $db->exec($statement);
                echo "✅\n";
            } else {
                $db->exec($statement);
            }
        } catch (PDOException $e) {
            echo "⚠️  " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n========================================\n";
    echo "RISULTATO IMPORTAZIONE\n";
    echo "========================================\n";
    echo "Tabelle create: $created\n";
    echo "Errori: $errors\n\n";
    
    // Verifica tabelle create
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelle presenti:\n";
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  ✅ $table ($count record)\n";
    }
    
    echo "\n✅ Importazione completata!\n";
    
} catch (Exception $e) {
    echo "\n❌ Errore generale: " . $e->getMessage() . "\n";
    exit(1);
}