<?php
/**
 * TOOL: Auto-patch per aggiungere nonce a tutti i file PHP
 * Usage: php auto_add_nonce.php
 * 
 * NOTA: Questo file modifica in-place tutti i file PHP
 * Esegui UNA SOLA VOLTA, poi elimina questo file!
 */

$base_dir = __DIR__;
$php_files = array_filter(
    array_merge(
        glob($base_dir . '/*.php'),
        glob($base_dir . '/includes/*.php'),
        glob($base_dir . '/includes/**/*.php'),
        glob($base_dir . '/api/*.php'),
    ),
    function($f) {
        return is_file($f) && strpos($f, 'vendor') === false && strpos($f, 'tests') === false;
    }
);

$modified_count = 0;
$errors = [];

foreach ($php_files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Solo modifica se il file ha <script>
    if (strpos($content, '<script>') === false && strpos($content, '<script nonce=') !== false) {
        continue; // Già done
    }
    
    // Aggiungi nonce ai <script> (ma non a <script src=...)
    $content = preg_replace(
        '/<script>\n/',
        '<script nonce="<?= $_SESSION[\'csp_nonce\'] ?>">' . "\n",
        $content
    );
    
    // Se il contenuto è cambiato, salva il file
    if ($content !== $original) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed: " . str_replace($base_dir, '.', $file) . "\n";
            $modified_count++;
        } else {
            $errors[] = "Errore scrittura: $file";
        }
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Modificati: $modified_count file\n";

if (!empty($errors)) {
    echo "\n❌ Errori:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}

echo "\n💡 Ricorda di eliminare questo file (auto_add_nonce.php) dopo l'esecuzione!\n";
