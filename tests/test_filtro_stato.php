<?php
/**
 * Test Filtro Stato Recupero
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== TEST FILTRO STATO RECUPERO ===\n\n";

$assenzeCtrl = new AssenzeController();

// Test 1: Tutte le assenze
echo "1. TUTTE LE ASSENZE:\n";
$tutte = $assenzeCtrl->getAllAssenze([]);
echo "Totale: " . count($tutte) . "\n";
foreach ($tutte as $a) {
    echo "  - Assenza #{$a['id']}: da_recuperare={$a['da_recuperare']}, ha_recupero={$a['ha_recupero']}\n";
}
echo "\n";

// Test 2: Programmato (ha_recupero > 0)
echo "2. PROGRAMMATO (ha_recupero > 0):\n";
$programmato = $assenzeCtrl->getAllAssenze(['stato_recupero' => 'programmato']);
echo "Totale: " . count($programmato) . "\n";
foreach ($programmato as $a) {
    echo "  - Assenza #{$a['id']}: da_recuperare={$a['da_recuperare']}, ha_recupero={$a['ha_recupero']}\n";
}
echo "\n";

// Test 3: Da programmare (da_recuperare=1 AND ha_recupero=0)
echo "3. DA PROGRAMMARE (da_recuperare=1 AND ha_recupero=0):\n";
$da_programmare = $assenzeCtrl->getAllAssenze(['stato_recupero' => 'da_programmare']);
echo "Totale: " . count($da_programmare) . "\n";
foreach ($da_programmare as $a) {
    echo "  - Assenza #{$a['id']}: da_recuperare={$a['da_recuperare']}, ha_recupero={$a['ha_recupero']}\n";
}
echo "\n";

// Test 4: Non necessario (da_recuperare=0)
echo "4. NON NECESSARIO (da_recuperare=0):\n";
$non_necessario = $assenzeCtrl->getAllAssenze(['stato_recupero' => 'non_necessario']);
echo "Totale: " . count($non_necessario) . "\n";
foreach ($non_necessario as $a) {
    echo "  - Assenza #{$a['id']}: da_recuperare={$a['da_recuperare']}, ha_recupero={$a['ha_recupero']}\n";
}
echo "\n";

echo "=== FINE TEST ===\n";