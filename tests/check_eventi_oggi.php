<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Test: Verifica eventi salvati oggi
$db = Database::getInstance()->getConnection();

echo "=== CHECK EVENTI OGGI ===\n\n";

// Data di oggi
$oggi = date('Y-m-d');
echo "Data controllo: $oggi\n\n";

// Query eventi
$stmt = $db->prepare("
    SELECT 
        e.id,
        e.data_evento,
        e.ora_inizio,
        e.ora_fine,
        e.aula_id,
        e.attivo,
        t.codice as tipologia,
        t.nome as tipologia_nome,
        au.nome as aula,
        COALESCE(a.cognome || ' ' || a.nome, d.cognome || ' ' || d.nome, se.cognome || ' ' || se.nome, 'N/D') as partecipante
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN soci s ON e.allievo_id = s.id
    LEFT JOIN persone a ON s.persona_id = a.id
    LEFT JOIN docenti d ON e.docente_id = d.id
    LEFT JOIN soci_esterni se ON e.socio_occasionale_id = se.id
    LEFT JOIN aule au ON e.aula_id = au.id
    WHERE e.data_evento = ?
    ORDER BY e.ora_inizio, e.aula_id
");

$stmt->execute([$oggi]);
$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Eventi trovati: " . count($eventi) . "\n\n";

if (count($eventi) > 0) {
    foreach ($eventi as $evento) {
        echo "─────────────────────────────────\n";
        echo "ID: {$evento['id']}\n";
        echo "Tipologia: {$evento['tipologia_nome']} ({$evento['tipologia']})\n";
        echo "Aula: {$evento['aula']} (ID: {$evento['aula_id']})\n";
        echo "Orario: {$evento['ora_inizio']} - {$evento['ora_fine']}\n";
        echo "Partecipante: {$evento['partecipante']}\n";
        echo "Attivo: " . ($evento['attivo'] ? 'SÌ' : 'NO') . "\n";
    }
    echo "─────────────────────────────────\n\n";
} else {
    echo "⚠️ Nessun evento trovato per oggi!\n\n";
}

// Cerca eventi negli ultimi 7 giorni
echo "=== TUTTI GLI EVENTI ULTIMI 7 GIORNI ===\n\n";

$stmt = $db->prepare("
    SELECT 
        e.id,
        e.data_evento,
        e.ora_inizio,
        e.ora_fine,
        e.aula_id,
        e.attivo,
        t.codice as tipologia,
        au.nome as aula
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN aule au ON e.aula_id = au.id
    WHERE e.data_evento >= date('now', '-7 days')
    ORDER BY e.data_evento DESC, e.ora_inizio
");

$stmt->execute();
$tutti_eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Eventi totali ultimi 7 giorni: " . count($tutti_eventi) . "\n\n";

if (count($tutti_eventi) > 0) {
    foreach ($tutti_eventi as $e) {
        echo "ID: {$e['id']} | Data: {$e['data_evento']} | {$e['ora_inizio']}-{$e['ora_fine']} | Aula: {$e['aula']} (ID:{$e['aula_id']}) | Tipo: {$e['tipologia']}\n";
    }
    echo "\n";
}

echo "\n✅ Check completato!\n";
?>