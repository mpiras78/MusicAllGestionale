<?php
/**
 * Script: Inverti icone prenotazioni soci/docente
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== INVERSIONE ICONE PRENOTAZIONI ===\n\n";

$db = Database::getInstance();

// 1. Verifica icone attuali
echo "1. Icone attuali:\n";
$prenotazioni = $db->query("
    SELECT codice, nome, icona 
    FROM tipologie_evento 
    WHERE categoria = 'prenotazione' 
    ORDER BY codice
");

foreach ($prenotazioni as $p) {
    echo "   - {$p['codice']}: {$p['nome']} → {$p['icona']}\n";
}

// 2. Inverti le icone
echo "\n2. Inversione icone...\n";

// Icona attuale prenotazione soci
$icona_soci = $db->queryOne("
    SELECT icona FROM tipologie_evento WHERE codice = 'PREN_SALA_SOCI'
")['icona'];

// Icona attuale prenotazione docente
$icona_docente = $db->queryOne("
    SELECT icona FROM tipologie_evento WHERE codice = 'PREN_DOCENTE'
")['icona'];

echo "   Prenotazione Soci aveva: $icona_soci\n";
echo "   Prenotazione Docente aveva: $icona_docente\n\n";

// Swap
$db->execute("UPDATE tipologie_evento SET icona = ? WHERE codice = 'PREN_SALA_SOCI'", [$icona_docente]);
$db->execute("UPDATE tipologie_evento SET icona = ? WHERE codice = 'PREN_DOCENTE'", [$icona_soci]);

echo "   ✓ Icone invertite!\n";

// 3. Verifica risultato
echo "\n3. Nuove icone:\n";
$prenotazioni_nuove = $db->query("
    SELECT codice, nome, icona 
    FROM tipologie_evento 
    WHERE categoria = 'prenotazione' 
    ORDER BY codice
");

foreach ($prenotazioni_nuove as $p) {
    echo "   - {$p['codice']}: {$p['nome']} → {$p['icona']}\n";
}

echo "\n✅ Inversione completata!\n";