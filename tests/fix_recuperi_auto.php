<?php
/**
 * Script di Fix AUTOMATICO: Conferma recuperi esistenti non confermati
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== FIX AUTOMATICO RECUPERI NON CONFERMATI ===\n\n";

$db = Database::getInstance();

// 1. Verifica recuperi non confermati
$non_confermati = $db->query("
    SELECT id, data_recupero, annullato,
           confermata_da_docente, confermata_da_segreteria
    FROM recuperi
    WHERE annullato = 0
    AND confermata_da_docente = 0
    AND confermata_da_segreteria = 0
");

echo "Recuperi non confermati trovati: " . count($non_confermati) . "\n\n";

if (empty($non_confermati)) {
    echo "✅ Nessun recupero da fixare!\n";
    exit(0);
}

// Mostra dettagli
foreach ($non_confermati as $rec) {
    echo "- ID: {$rec['id']}, Data: {$rec['data_recupero']}\n";
}

echo "\n";

// 2. Conferma recuperi AUTOMATICAMENTE
echo "Confermo i recuperi automaticamente...\n";

$result = $db->execute("
    UPDATE recuperi
    SET confermata_da_segreteria = 1,
        data_conferma = CURRENT_TIMESTAMP,
        confermata_da_user_id = 1
    WHERE annullato = 0
    AND confermata_da_docente = 0
    AND confermata_da_segreteria = 0
");

echo "✅ " . count($non_confermati) . " recuperi confermati con successo!\n\n";

// 3. Verifica
$dopo = $db->query("
    SELECT 
        COUNT(*) as totale,
        SUM(CASE WHEN confermata_da_segreteria = 1 THEN 1 ELSE 0 END) as confermati,
        SUM(CASE WHEN annullato = 0 AND data_recupero >= DATE('now') THEN 1 ELSE 0 END) as futuri
    FROM recuperi
");

echo "=== RIEPILOGO FINALE ===\n";
echo "Totale recuperi nel sistema: {$dopo[0]['totale']}\n";
echo "Recuperi confermati: {$dopo[0]['confermati']}\n";
echo "Recuperi futuri/programmati: {$dopo[0]['futuri']}\n\n";

echo "✅ Fix completato! I recuperi ora sono visibili a admin e docenti.\n";