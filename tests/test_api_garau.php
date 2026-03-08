<?php
/**
 * Test API per Garau
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

// Trova Garau
// Cerca Garau nella tabella `soci` (la tabella `soci` è collegata a `persone` tramite persona_id)
$garau = $db->queryOne("SELECT s.id as id, p.cognome as cognome, p.nome as nome
    FROM soci s
    JOIN persone p ON s.persona_id = p.id
    WHERE p.cognome LIKE '%Garau%'");

echo "=== TEST API LEZIONI GARAU ===\n\n";
echo "Socio: " . ($garau ? ($garau['cognome'] . ' ' . $garau['nome'] . ' (ID: ' . $garau['id'] . ')') : 'NON TROVATO') . "\n\n";

// Simula query API (stessa query di api_get_lezioni_allievo.php)
$lezioni = $db->query("
    SELECT 
        l.id,
        l.giorno_settimana,
        l.ora_inizio,
        l.ora_fine,
        m.nome as materia,
        d.cognome || ' ' || d.nome as docente
    FROM lezioni l
    JOIN materie m ON l.materia_id = m.id
    JOIN docenti d ON l.docente_id = d.id
    -- la tabella `lezioni` nel DB attuale usa ancora la colonna `allievo_id`
    WHERE l.allievo_id = ?
    ORDER BY 
        CASE l.giorno_settimana
            WHEN 'Lunedì' THEN 1
            WHEN 'Martedì' THEN 2
            WHEN 'Mercoledì' THEN 3
            WHEN 'Giovedì' THEN 4
            WHEN 'Venerdì' THEN 5
            WHEN 'Sabato' THEN 6
            WHEN 'Domenica' THEN 7
        END,
        l.ora_inizio
", [$garau['id']]);

echo "Risultato query API:\n";
if (empty($lezioni)) {
    echo "❌ NESSUNA lezione trovata (QUESTO È IL PROBLEMA!)\n\n";
    
    // Ora prova senza ORDER BY
    $lezioni_no_order = $db->query("
        SELECT 
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
        -- la tabella `lezioni` nel DB attuale usa ancora la colonna `allievo_id`
        WHERE l.allievo_id = ?
    ", [$garau['id']]);
    
    echo "Risultato SENZA ORDER BY:\n";
    if (empty($lezioni_no_order)) {
        echo "❌ Ancora nessuna lezione! (Problema nei JOIN?)\n";
    } else {
        echo "✅ CON SENZA ORDER BY FUNZIONA! Trovate " . count($lezioni_no_order) . " lezioni:\n";
        print_r($lezioni_no_order);
    }
} else {
    echo "✅ Trovate " . count($lezioni) . " lezioni:\n";
    print_r($lezioni);
}

echo "\n=== FINE TEST ===\n";