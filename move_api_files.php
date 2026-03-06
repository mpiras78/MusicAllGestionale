<?php
// Script per spostare i file API nella cartella api/
$apiDir = __DIR__ . '/api';
$rootDir = __DIR__;

// Crea la cartella se non esiste
if (!is_dir($apiDir)) {
    mkdir($apiDir, 0755, true);
}

// Trova tutti i file api_*.php nella root
$files = glob($rootDir . '/api_*.php');

echo "File da spostare: " . count($files) . "\n";

foreach ($files as $file) {
    $fileName = basename($file);
    $newPath = $apiDir . '/' . $fileName;
    
    if (rename($file, $newPath)) {
        echo "✓ Spostato: $fileName\n";
    } else {
        echo "✗ Errore: $fileName\n";
    }
}

echo "\nFine spostamento\n";
echo "File nella cartella api: " . count(glob($apiDir . '/api_*.php')) . "\n";
?>
