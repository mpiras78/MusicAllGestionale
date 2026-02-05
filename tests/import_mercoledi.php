<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "📥 Importazione lezioni MERCOLEDÌ...\n\n";
    
    // Leggi file SQL
    $sql = file_get_contents(__DIR__ . '/../database/insert_lezioni_MERCOLEDI_DEMO.sql');
    
    // Dividi in singole query
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $failed = 0;
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($query);
            $success++;
        } catch (PDOException $e) {
            $failed++;
            echo "❌ Errore: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 RISULTATI:\n";
    echo "✅ Importate: $success\n";
    echo "❌ Fallite: $failed\n\n";
    
    // Conta lezioni mercoledì
    $stmt = $db->query("SELECT COUNT(*) as count FROM lezioni WHERE giorno_settimana = 'mercoledi'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "🎯 Totale lezioni MERCOLEDÌ nel database: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}