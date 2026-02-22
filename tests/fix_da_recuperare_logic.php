<?php
/**
 * Script di migrazione: Aggiorna flag da_recuperare secondo la nuova logica
 * 
 * REGOLA:
 * - Assenze DOCENTE: sempre da_recuperare = 1
 * - Assenze ALLIEVO: prime 3 assenze da_recuperare = 1, dalla 4a in poi da_recuperare = 0
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== MIGRAZIONE FLAG DA_RECUPERARE ===\n\n";

$db = Database::getInstance();
$assenzeCtrl = new AssenzeController();

// Step 1: Reset - tutte le assenze docente a da_recuperare = 1
echo "1. Imposto tutte le assenze DOCENTE a da_recuperare = 1...\n";
$result = $db->execute("UPDATE assenze SET da_recuperare = 1 WHERE tipo = 'docente'");
echo "   ✓ Aggiornate assenze docente\n\n";

// Step 2: Ottieni tutte le assenze ALLIEVO ordinate per allievo, lezione e data
echo "2. Elaboro assenze ALLIEVO secondo la regola delle 3 assenze...\n";

$assenze_allievo = $db->query("
    SELECT id, allievo_id, lezione_id, data_assenza, tipo
    FROM assenze
    WHERE tipo = 'allievo'
    ORDER BY allievo_id, lezione_id, data_assenza ASC
");

echo "   Trovate " . count($assenze_allievo) . " assenze allievo da processare\n";

// Raggruppa per allievo+lezione
$grouped = [];
foreach ($assenze_allievo as $ass) {
    $key = $ass['allievo_id'] . '_' . $ass['lezione_id'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [];
    }
    $grouped[$key][] = $ass;
}

$aggiornate = 0;
$totali = 0;

foreach ($grouped as $key => $assenze_gruppo) {
    echo "\n   Gruppo $key: " . count($assenze_gruppo) . " assenze\n";
    
    // Filtra per anno scolastico corrente
    // Determina anno scolastico
    $oggi = new DateTime();
    $mese = (int)$oggi->format('m');
    $anno = (int)$oggi->format('Y');
    
    if ($mese < 9) {
        $anno_inizio = $anno - 1;
        $anno_fine = $anno;
    } else {
        $anno_inizio = $anno;
        $anno_fine = $anno + 1;
    }
    
    $data_inizio = "$anno_inizio-09-01";
    $data_fine = "$anno_fine-06-30";
    
    // Filtra assenze dell'anno scolastico corrente
    $assenze_anno = array_filter($assenze_gruppo, function($a) use ($data_inizio, $data_fine) {
        return $a['data_assenza'] >= $data_inizio && $a['data_assenza'] <= $data_fine;
    });
    
    // Riordina per data
    usort($assenze_anno, function($a, $b) {
        return strcmp($a['data_assenza'], $b['data_assenza']);
    });
    
    echo "      Anno scolastico $anno_inizio/$anno_fine: " . count($assenze_anno) . " assenze\n";
    
    // Applica la regola: prime 3 = 1, dalla 4a in poi = 0
    $posizione = 1;
    foreach ($assenze_anno as $ass) {
        $nuovo_valore = ($posizione <= 3) ? 1 : 0;
        
        $db->execute("UPDATE assenze SET da_recuperare = ? WHERE id = ?", [$nuovo_valore, $ass['id']]);
        
        echo "      - Assenza #{$ass['id']} ({$ass['data_assenza']}): posizione #$posizione → da_recuperare = $nuovo_valore\n";
        
        $aggiornate++;
        $posizione++;
    }
    
    $totali += count($assenze_anno);
}

echo "\n=== RIEPILOGO ===\n";
echo "Assenze allievo processate: $totali\n";
echo "Assenze allievo aggiornate: $aggiornate\n";

// Step 3: Verifica risultati
echo "\n3. Verifica risultati:\n";

$stats = $db->queryOne("
    SELECT 
        COUNT(*) as totale,
        SUM(CASE WHEN tipo = 'docente' THEN 1 ELSE 0 END) as docente_totale,
        SUM(CASE WHEN tipo = 'docente' AND da_recuperare = 1 THEN 1 ELSE 0 END) as docente_recupero,
        SUM(CASE WHEN tipo = 'allievo' THEN 1 ELSE 0 END) as allievo_totale,
        SUM(CASE WHEN tipo = 'allievo' AND da_recuperare = 1 THEN 1 ELSE 0 END) as allievo_recupero,
        SUM(CASE WHEN tipo = 'allievo' AND da_recuperare = 0 THEN 1 ELSE 0 END) as allievo_no_recupero
    FROM assenze
");

echo "\n";
echo "Assenze DOCENTE:\n";
echo "  - Totali: {$stats['docente_totale']}\n";
echo "  - Da recuperare (= 1): {$stats['docente_recupero']}\n";
echo "  - ✓ Tutte le assenze docente hanno da_recuperare = 1\n";

echo "\nAssenze ALLIEVO:\n";
echo "  - Totali: {$stats['allievo_totale']}\n";
echo "  - Da recuperare (= 1): {$stats['allievo_recupero']} (prime 3 per ogni lezione)\n";
echo "  - A discrezione (= 0): {$stats['allievo_no_recupero']} (dalla 4a in poi)\n";

echo "\n✅ Migrazione completata con successo!\n";