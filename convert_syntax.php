<?php
/**
 * Converti calendario.php da sintassi : endif a sintassi { }
 */

$file = 'calendario.php';
$content = file_get_contents($file);

// Conversioni da fare (in ordine):
// 1. if (...): → if (...) {
// 2. elseif (...): → } elseif (...) {
// 3. else: → } else {
// 4. endif; → }

// Per evitare problemi, usiamo regex con attenzione

// Passo 1: Convertiif con semplici parentesi
$content = preg_replace('/\bif\s*\((.*?)\):\s*$/m', 'if ($1) {' . "\n", $content);

// Passo 2: elseif
$content = preg_replace('/\belseif\s*\((.*?)\):\s*$/m', '} elseif ($1) {' . "\n", $content);

// Passo 3: else
$content = preg_replace('/\belse:\s*$/m', '} else {' . "\n", $content);

// Passo 4: endif
$content = preg_replace('/\bendif;\s*$/m', '}' . "\n", $content);

// Salva il file
file_put_contents($file, $content);

echo "Conversione completata! Controllare il file...\n";
?>
