<?php
// Script per abilitare logging debug in un file locale

// Abilita error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Imposta file di log locale
$log_file = __DIR__ . '/caso3_debug.log';
ini_set('error_log', $log_file);

echo "✅ Logging abilitato!\n";
echo "📋 File di log: $log_file\n\n";

// Pulisci log esistente
if (file_exists($log_file)) {
    unlink($log_file);
    echo "🗑️ Log precedente pulito\n";
}

// Crea file vuoto
file_put_contents($log_file, "=== LOG DEBUG CASO 3 - " . date('Y-m-d H:i:s') . " ===\n");

echo "\n✨ Ora ricarica la pagina calendario.php e controlla il file:\n";
echo "   $log_file\n\n";
echo "📝 Per vedere i log in tempo reale su Windows:\n";
echo "   Get-Content -Path \"$log_file\" -Wait\n";
echo "   (oppure usa un editor di testo e ricarica il file)\n";