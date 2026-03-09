<?php
// Safe replacement script for controllers: change SQL table/column occurrences
// BACKS UP original files as .bak before modifying.
$dir = __DIR__ . '/../includes/controllers';
$files = glob($dir . '/*.php');
$replacements = [
    // column
    '/\bsocio_id\b/' => 'socio_id',
    // SQL table joins and FROM
    '/\bFROM\s+soci\b/i' => 'FROM soci',
    '/\bJOIN\s+soci\b/i' => 'JOIN soci',
    // alias in SQL
    '/\bas\s+socio\b/i' => 'as socio',
    // common plural table reference (in comments or edge cases) - handled conservatively
    '/\bsoci\b/i' => 'soci',
];

foreach ($files as $f) {
    $orig = file_get_contents($f);
    $modified = $orig;
    foreach ($replacements as $pat => $repl) {
        $modified = preg_replace($pat, $repl, $modified);
    }
    if ($modified !== $orig) {
        // backup
        copy($f, $f . '.bak');
        file_put_contents($f, $modified);
        echo "Updated: $f\n";
    }
}
echo "Done replacements in controllers (backups .bak created).\n";
