<?php
/**
 * Script per rimuovere le vecchie tabelle iscrizioni e pagamenti
 * e ricrearle con la nuova struttura
 * 
 * Uso: php tests/cleanup_old_iscrizioni_pagamenti.php
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "\n";
echo "========================================\n";
echo "CLEANUP: Rimozione vecchie tabelle\n";
echo "========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "⚠️  ATTENZIONE: Questo script eliminerà le vecchie tabelle:\n";
    echo "   - iscrizioni (vecchia struttura)\n";
    echo "   - pagamenti (vecchia struttura)\n\n";
    
    // Check se esistono dati
    $stmt = $db->query("SELECT COUNT(*) as count FROM iscrizioni");
    $iscrizioniCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM pagamenti");
    $pagamentiCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($iscrizioniCount > 0 || $pagamentiCount > 0) {
        echo "❌ ABORT: Esistono dati nelle tabelle!\n";
        echo "   - iscrizioni: $iscrizioniCount record\n";
        echo "   - pagamenti: $pagamentiCount record\n\n";
        echo "Per procedere, assicurati di fare un backup prima.\n\n";
        exit(1);
    }
    
    echo "✓ Tabelle vuote, procedo con il drop...\n\n";
    
    // Drop vecchie tabelle
    echo "🗑️  Dropping tabella 'iscrizioni'...\n";
    $db->exec("DROP TABLE IF EXISTS iscrizioni");
    
    echo "🗑️  Dropping tabella 'pagamenti'...\n";
    $db->exec("DROP TABLE IF EXISTS pagamenti");
    
    echo "🗑️  Dropping view 'v_pagamenti_dettagliati' se esiste...\n";
    $db->exec("DROP VIEW IF EXISTS v_pagamenti_dettagliati");
    
    echo "\n✅ Vecchie tabelle rimosse con successo!\n\n";
    
    echo "📋 Prossimo passo:\n";
    echo "Esegui: php tests/run_iscrizioni_migration.php\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRORE PDO:\n";
    echo "Messaggio: " . $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERRORE:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

echo "✨ Cleanup completato!\n\n";