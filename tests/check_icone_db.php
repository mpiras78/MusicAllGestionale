<?php
/**
 * Verifica tutte le icone salvate nel database
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== ICONE NEL DATABASE ===\n\n";

$db = Database::getInstance();

// Cerca tutte le icone
$tipologie = $db->query("
    SELECT id, codice, nome, categoria, icona 
    FROM tipologie_evento 
    ORDER BY categoria, ordine_visualizzazione
");

echo "Tutte le tipologie con le loro icone:\n\n";

$icone_cercate = ['bi-mortarboard', 'bi-person-x', 'bi-person-workspace'];
$trovate = [];

foreach ($tipologie as $t) {
    echo sprintf("%-25s %-35s %-20s %s\n", 
        "[{$t['categoria']}]",
        $t['nome'],
        $t['codice'],
        $t['icona'] ?? '(nessuna)'
    );
    
    if ($t['icona'] && in_array($t['icona'], $icone_cercate)) {
        $trovate[] = $t['icona'];
    }
}

echo "\n=== RICERCA ICONE SPECIFICHE ===\n";
foreach ($icone_cercate as $icona) {
    $presente = in_array($icona, $trovate);
    $status = $presente ? '✓ TROVATA' : '✗ NON TROVATA';
    echo "$icona: $status\n";
}

// Cerca anche nel codice
echo "\n=== RICERCA NEL CODICE (assets/js/eventi.js) ===\n";
$eventi_js = file_get_contents(__DIR__ . '/../assets/js/eventi.js');

foreach ($icone_cercate as $icona) {
    $count = substr_count($eventi_js, $icona);
    echo "$icona: $count occorrenze in eventi.js\n";
}