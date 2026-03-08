<?php
/**
 * Debug: Verifica lezioni di Garau
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

echo "=== DEBUG LEZIONI GARAU ===\n\n";

// Trova Garau nella nuova anagrafica `soci` -> `persone`
$garau = $db->queryOne(
    "SELECT s.id as id, p.cognome as cognome, p.nome as nome
    FROM soci s
    JOIN persone p ON s.persona_id = p.id
    WHERE p.cognome LIKE '%Garau%' OR p.nome LIKE '%Garau%'
    ORDER BY s.id
    LIMIT 1"
);

if (!$garau) {
    echo "❌ Garau non trovato nel database!\n";
    exit;
}

echo "✅ Trovato: {$garau['cognome']} {$garau['nome']} (ID: {$garau['id']})\n\n";

// Query lezioni SENZA filtro giorno (per vedere tutti i giorni)
echo "=== TUTTE LE LEZIONI DI GARAU ===\n";
$tutte_lezioni = $db->query(
    "SELECT 
        l.*,
        m.nome as materia,
        d.cognome || ' ' || d.nome as docente
    FROM lezioni l
    LEFT JOIN materie m ON l.materia_id = m.id
    LEFT JOIN docenti d ON l.docente_id = d.id
    WHERE l.allievo_id = ?",
    [$garau['id']]
);

if (empty($tutte_lezioni)) {
    echo "❌ NESSUNA LEZIONE trovata per Garau!\n";
} else {
    echo "✅ Trovate " . count($tutte_lezioni) . " lezioni:\n\n";
    foreach ($tutte_lezioni as $lez) {
        echo "Lezione ID: {$lez['id']}\n";
        echo "  Giorno: '{$lez['giorno_settimana']}'\n";
        echo "  Orario: {$lez['ora_inizio']} - {$lez['ora_fine']}\n";
        echo "  Materia: {$lez['materia']}\n";
        echo "  Docente: {$lez['docente']}\n";
        echo "  Attiva: {$lez['attiva']}\n";
        echo "\n";
    }
}

// Test query API con diversi formati
echo "\n=== TEST QUERY API CON DIVERSI CASE ===\n\n";

$test_cases = [
    "lunedi",
    "Lunedì",
    "LUNEDI",
    "lunedì",
    "Lunedi"
];

foreach ($test_cases as $day_format) {
    $count = $db->queryOne(
        "SELECT COUNT(*) as cnt 
        FROM lezioni 
        WHERE allievo_id = ? 
        AND giorno_settimana = ?",
        [$garau['id'], $day_format]
    );
    
    echo "Formato '{$day_format}': {$count['cnt']} lezioni\n";
}

echo "\n=== TEST CASE-INSENSITIVE ===\n";
$count_ci = $db->queryOne(
    "SELECT COUNT(*) as cnt 
    FROM lezioni 
    WHERE allievo_id = ? 
    AND LOWER(giorno_settimana) = LOWER('lunedì')",
    [$garau['id']]
);

echo "LOWER() match: {$count_ci['cnt']} lezioni\n";

echo "\n=== FINE DEBUG ===\n";