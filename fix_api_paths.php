<?php
// Script per correggere i percorsi require_once nei file API

$dir = __DIR__ . '/api';
$count = 0;

foreach (glob($dir . '/*.php') as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Sostituisci require_once 'includes/bootstrap.php' con require_once '../includes/bootstrap.php'
    $content = str_replace("require_once 'includes/bootstrap.php'", "require_once '../includes/bootstrap.php'", $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        $count++;
        echo "✅ Corretto: " . basename($file) . "\n";
    }
}

echo "\nTotale file corretti: $count\n";
