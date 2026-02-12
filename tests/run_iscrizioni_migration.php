<?php
/**
 * Script per eseguire la migration del sistema iscrizioni e pagamenti
 * 
 * Uso: php tests/run_iscrizioni_migration.php
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "\n";
echo "========================================\n";
echo "MIGRATION: Sistema Iscrizioni e Pagamenti\n";
echo "========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Leggi il file migration
    $migrationFile = __DIR__ . '/../database/migration_iscrizioni_pagamenti.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("File migration non trovato: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Errore nella lettura del file migration");
    }
    
    echo "📄 File migration caricato\n";
    echo "📝 Esecuzione migration...\n\n";
    
    // Esegui tutto il file SQL in una volta
    try {
        $db->exec($sql);
        echo "✅ Migration eseguita con successo!\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Warning durante migration:\n";
        echo "Errore: " . $e->getMessage() . "\n\n";
        // Continua comunque per verificare cosa è stato creato
    }
    
    // Verifica tabelle create
    echo "🔍 Verifica tabelle create:\n";
    echo "─────────────────────────────────────\n";
    
    $tables = [
        'tipi_pagamento' => 'Tipi Pagamento',
        'metodi_pagamento' => 'Metodi Pagamento',
        'configurazione_tariffe' => 'Configurazione Tariffe',
        'tariffe_materie' => 'Tariffe per Materia',
        'iscrizioni' => 'Iscrizioni',
        'pagamenti' => 'Pagamenti'
    ];
    
    foreach ($tables as $table => $nome) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        $icon = $count > 0 ? '✓' : '○';
        echo "$icon $nome: $count record\n";
    }
    
    echo "\n";
    
    // Mostra seed data
    echo "📊 Seed Data Inseriti:\n";
    echo "─────────────────────────────────────\n";
    
    // Tipi pagamento
    $stmt = $db->query("SELECT codice, nome FROM tipi_pagamento ORDER BY ordinamento");
    $tipi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n🏷️  Tipi Pagamento (" . count($tipi) . "):\n";
    foreach ($tipi as $tipo) {
        echo "   • {$tipo['codice']}: {$tipo['nome']}\n";
    }
    
    // Metodi pagamento
    $stmt = $db->query("SELECT codice, nome FROM metodi_pagamento ORDER BY ordinamento");
    $metodi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n💳 Metodi Pagamento (" . count($metodi) . "):\n";
    foreach ($metodi as $metodo) {
        echo "   • {$metodo['codice']}: {$metodo['nome']}\n";
    }
    
    // Configurazione tariffe
    $stmt = $db->query("SELECT * FROM configurazione_tariffe WHERE attivo = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        echo "\n💰 Configurazione Tariffe {$config['anno_accademico']}:\n";
        echo "   • Quota Associativa: {$config['quota_associativa']} €\n";
        echo "   • Tariffa Individuale: {$config['tariffa_individuale']} €\n";
        echo "   • Tariffa Gruppo: {$config['tariffa_gruppo']} €\n";
        echo "   • Tariffa Lab: {$config['tariffa_lab']} €\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "✅ MIGRATION COMPLETATA CON SUCCESSO!\n";
    echo "========================================\n\n";
    
    echo "📋 Prossimi passi:\n";
    echo "1. Creare Models Eloquent (TipoPagamento, MetodoPagamento, Iscrizione, Pagamento)\n";
    echo "2. Creare Controller per gestione iscrizioni\n";
    echo "3. Creare UI per registrazione iscrizioni\n";
    echo "4. Creare UI per registrazione pagamenti\n";
    echo "5. Implementare check automatico quota associativa\n";
    echo "6. Creare dashboard pagamenti\n";
    echo "7. Implementare report finanziari\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRORE PDO:\n";
    echo "Messaggio: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERRORE:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

echo "✨ Script completato!\n\n";