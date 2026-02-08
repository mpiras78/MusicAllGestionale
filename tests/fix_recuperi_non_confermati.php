<?php
/**
 * Script di Fix: Conferma recuperi esistenti non confermati
 * I recuperi creati prima del flusso semplificato non erano auto-confermati
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== FIX RECUPERI NON CONFERMATI ===\n\n";

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
echo "Vuoi confermare questi recuperi come creati dalla segreteria? (y/n): ";
$handle = fopen("php://stdin", "r");
$confirm = trim(fgets($handle));

if ($confirm !== 'y' && $confirm !== 'Y') {
    echo "❌ Operazione annullata\n";
    exit(0);
}

// 2. Conferma recuperi
echo "\nConfermo i recuperi...\n";

$result = $db->execute("
    UPDATE recuperi
    SET confermata_da_segreteria = 1,
        data_conferma = CURRENT_TIMESTAMP,
        confermata_da_user_id = 1
    WHERE annullato = 0
    AND confermata_da_docente = 0
    AND confermata_da_segreteria = 0
");

echo "✅ Recuperi confermati con successo!\n\n";

// 3. Verifica
$dopo = $db->query("
    SELECT 
        COUNT(*) as totale,
        SUM(CASE WHEN confermata_da_segreteria = 1 THEN 1 ELSE 0 END) as confermati,
        SUM(CASE WHEN annullato = 0 AND data_recupero >= DATE('now') THEN 1 ELSE 0 END) as futuri
    FROM recuperi
");

echo "=== RIEPILOGO ===\n";
echo "Totale recuperi: {$dopo[0]['totale']}\n";
echo "Confermati: {$dopo[0]['confermati']}\n";
echo "Futuri/Programmati: {$dopo[0]['futuri']}\n\n";

echo "✅ Fix completato! I recuperi ora sono visibili.\n";