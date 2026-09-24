<?php
/**
 * Auto-fix script: Aggiunge nonce a tutti gli script inline senza nonce
 * Usa il pattern: <script> → <script nonce="<?= $_SESSION['csp_nonce'] ?>">
 * 
 * Esclude:
 * - script con src attribute (CDN/external)
 * - script che hanno già nonce
 * - file di test/configurazione
 */

// Liste di file da escludere
$excludeFiles = [
    'fix-all-nonce.php',
    'auto_add_nonce.php',
    'NONCE_MIGRATION_GUIDE.php',
    'test_api_helpers_browser.php',
    'test_api_eventi_browser.php',
];

$excludeDirs = [
    'tests',
    'backups',
    '.git',
];

$scriptDir = __DIR__;
$filesProcessed = [];
$filesSkipped = [];
$filesModified = [];

echo "🔧 Avvio scan per aggiungere nonce...\n";
echo "📁 Cartella: $scriptDir\n\n";

// Scansiona tutti i .php
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($scriptDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    // Salta se non è .php
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $relativePath = str_replace($scriptDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $filename = $file->getFilename();

    // Salta file nella lista di esclusione
    if (in_array($filename, $excludeFiles)) {
        $filesSkipped[$relativePath] = 'File escluso (blacklist)';
        continue;
    }

    // Salta cartelle nella lista di esclusione
    $skip = false;
    foreach ($excludeDirs as $dir) {
        if (strpos($relativePath, $dir . DIRECTORY_SEPARATOR) === 0) {
            $filesSkipped[$relativePath] = 'Cartella esclusa (blacklist)';
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    $filesProcessed[$relativePath] = false;

    // Leggi file
    $content = file_get_contents($file->getPathname());
    $originalContent = $content;

    // Pattern: <script> che non ha src o nonce
    // Usa regex per trovare <script> senza attributi o con attributi che NON sono src/nonce
    
    // Sostituisci TUTTI i <script> senza attributi o senza nonce
    // Pattern: <script> oppure <script   > (con spazi)
    $patterns = [
        // <script> con fine tag (niente src, niente nonce)
        '/(<script)\s*>/i' => '<script nonce="<?= $_SESSION[\'csp_nonce\'] ?>">',
    ];

    $modified = false;
    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $content)) {
            // Ma controlla che NON abbia già nonce
            if (strpos($content, 'nonce=') === false) {
                $newContent = preg_replace($pattern, $replacement, $content);
                
                // Conta i cambiamenti
                $oldCount = substr_count($content, '<script');
                $newCount = substr_count($newContent, 'nonce=');
                
                if ($newCount > 0 && $newContent !== $originalContent) {
                    $content = $newContent;
                    $modified = true;
                }
            }
        }
    }

    // Se modificato, salva
    if ($modified && $content !== $originalContent) {
        file_put_contents($file->getPathname(), $content);
        $filesModified[$relativePath] = true;
        $filesProcessed[$relativePath] = true;
        echo "✅ MODIFICATO: $relativePath\n";
    } else {
        if (strpos($originalContent, '<script') !== false) {
            if (strpos($originalContent, 'nonce=') !== false) {
                $filesSkipped[$relativePath] = 'Già ha nonce';
            } else if (strpos($originalContent, 'src=') !== false) {
                $filesSkipped[$relativePath] = 'Solo script CDN (ha src)';
            } else {
                $filesSkipped[$relativePath] = 'Nessuno script inline trovato';
            }
        }
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "📊 RISULTATI\n";
echo str_repeat('=', 60) . "\n";
echo "✅ Processati: " . count($filesProcessed) . "\n";
echo "✏️  Modificati: " . count($filesModified) . "\n";
echo "⏭️  Saltati: " . count($filesSkipped) . "\n\n";

if (count($filesModified) > 0) {
    echo "📝 MODIFICATI:\n";
    foreach ($filesModified as $file => $status) {
        echo "   ✅ $file\n";
    }
    echo "\n";
}

if (count($filesSkipped) > 0) {
    echo "⏭️  SALTATI:\n";
    foreach ($filesSkipped as $file => $reason) {
        echo "   ⏭️  $file ($reason)\n";
    }
}

echo "\n✨ COMPLETATO!\n";
?>
