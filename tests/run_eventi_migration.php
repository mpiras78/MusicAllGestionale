<?php
/**
 * Esegue la migration per il sistema Eventi Calendario & Pagamenti
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRATION: Sistema Eventi Calendario & Pagamenti ===\n\n";

$db = Database::getInstance();
$migrationFile = __DIR__ . '/../database/migration_eventi_calendario.sql';

if (!file_exists($migrationFile)) {
    die("❌ File migration non trovato: $migrationFile\n");
}

echo "📄 Lettura file migration...\n";
$sql = file_get_contents($migrationFile);

if ($sql === false) {
    die("❌ Errore nella lettura del file migration\n");
}

echo "🔄 Esecuzione migration tramite sqlite3...\n\n";

try {
    // Esegui migration direttamente con sqlite3
    $dbPath = __DIR__ . '/../database/musicall.sqlite';
    $command = "sqlite3 \"$dbPath\" < \"$migrationFile\"";
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("Errore nell'esecuzione della migration (codice: $returnCode)");
    }
    
    echo "✅ Migration eseguita con successo!\n\n";
    
    // Verifica tabelle create
    echo "📊 Verifica tabelle create:\n";
    $tables = [
        'tipologie_evento',
        'soci_occasionali',
        'iscrizioni',
        'eventi_calendario',
        'listini_prezzi',
        'pagamenti',
        'iscrizioni_dettagli'
    ];
    
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch()['cnt'];
        echo "  ✓ $table: $count record\n";
    }
    
    // Verifica VIEW
    echo "\n📊 Verifica VIEW:\n";
    $viewExists = $db->query("SELECT name FROM sqlite_master WHERE type='view' AND name='v_calendario_unificato'")->fetch();
    if ($viewExists) {
        echo "  ✓ v_calendario_unificato: creata\n";
    } else {
        echo "  ✗ v_calendario_unificato: NON creata\n";
    }
    
    // Conta eventi migrati
    echo "\n📊 Eventi migrati dalla tabella lezioni:\n";
    $eventiMigrati = $db->query("SELECT COUNT(*) as cnt FROM eventi_calendario")->fetch()['cnt'];
    echo "  ✓ Totale eventi calendario: $eventiMigrati\n";
    
    echo "\n✅ MIGRATION COMPLETATA CON SUCCESSO!\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE durante la migration:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}