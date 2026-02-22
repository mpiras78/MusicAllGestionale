<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "=== CHECK MARTEDÌ 10 FEBBRAIO 2026 - AULA PIANO ===\n\n";

$data = '2026-02-10';
$aula_piano_id = 2; // AULA PIANO

// 1. Cerca lezione alle 18:15
echo "1. LEZIONE RICORRENTE (18:15)\n";
$stmt = $db->prepare("
    SELECT l.*, 
           al.cognome || ' ' || al.nome as allievo,
           d.cognome || ' ' || d.nome as docente,
           au.nome as aula
    FROM lezioni l
    LEFT JOIN allievi al ON l.allievo_id = al.id
    LEFT JOIN docenti d ON l.docente_id = d.id
    LEFT JOIN aule au ON l.aula_id = au.id
    WHERE l.giorno_settimana = 'martedi'
    AND l.aula_id = ?
    AND l.ora_inizio = '18:15:00'
");
$stmt->execute([$aula_piano_id]);
$lezione = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lezione) {
    echo "✅ Lezione trovata:\n";
    echo "   ID: {$lezione['id']}\n";
    echo "   Allievo: {$lezione['allievo']}\n";
    echo "   Docente: {$lezione['docente']}\n";
    echo "   Orario: {$lezione['ora_inizio']} - {$lezione['ora_fine']}\n";
    echo "   Aula: {$lezione['aula']}\n\n";
    
    $lezione_id = $lezione['id'];
    
    // 2. Verifica se è annullata
    echo "2. VERIFICA ASSENZA\n";
    $stmt = $db->prepare("
        SELECT * FROM assenze 
        WHERE lezione_id = ? 
        AND data_assenza = ?
    ");
    $stmt->execute([$lezione_id, $data]);
    $assenza = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($assenza) {
        echo "✅ Assenza trovata:\n";
        echo "   ID: {$assenza['id']}\n";
        echo "   Attiva: {$assenza['attiva']}\n";
        echo "   Causata da: {$assenza['causata_da']}\n";
        echo "   Note: " . ($assenza['note'] ?: 'N/D') . "\n\n";
    } else {
        echo "❌ Nessuna assenza trovata per questa lezione\n\n";
    }
} else {
    echo "❌ Nessuna lezione trovata alle 18:15 in AULA PIANO martedì\n\n";
}

// 3. Cerca eventi per quel giorno e aula
echo "3. EVENTI AULA PIANO - 10 FEBBRAIO\n";
$stmt = $db->prepare("
    SELECT e.*, 
           t.codice as tipologia,
           t.nome as tipologia_nome,
           COALESCE(al.cognome || ' ' || al.nome, d.cognome || ' ' || d.nome, se.cognome || ' ' || se.nome, 'N/D') as partecipante
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN allievi al ON e.allievo_id = al.id
    LEFT JOIN docenti d ON e.docente_id = d.id
    LEFT JOIN soci_esterni se ON e.socio_occasionale_id = se.id
    WHERE e.data_evento = ?
    AND e.aula_id = ?
    ORDER BY e.ora_inizio
");
$stmt->execute([$data, $aula_piano_id]);
$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Eventi trovati: " . count($eventi) . "\n\n";

if (count($eventi) > 0) {
    foreach ($eventi as $evt) {
        echo "─────────────────────────────────\n";
        echo "ID: {$evt['id']}\n";
        echo "Tipologia: {$evt['tipologia_nome']} ({$evt['tipologia']})\n";
        echo "Orario: {$evt['ora_inizio']} - {$evt['ora_fine']}\n";
        echo "Partecipante: {$evt['partecipante']}\n";
        echo "Attivo: " . ($evt['attivo'] ? 'SÌ' : 'NO') . "\n";
        echo "Confermato: " . ($evt['confermato'] ? 'SÌ' : 'NO') . "\n";
    }
    echo "─────────────────────────────────\n\n";
} else {
    echo "⚠️ Nessun evento trovato!\n\n";
}

// 4. Test query controller
echo "4. TEST QUERY CONTROLLER\n";
require_once __DIR__ . '/../includes/controllers/EventiController.php';
$eventiCtrl = new EventiController();
$eventi_controller = $eventiCtrl->getEventiPerData($data);

echo "Eventi caricati da controller: " . count($eventi_controller) . "\n";
if (count($eventi_controller) > 0) {
    echo "Eventi per Aula Piano (ID 2):\n";
    foreach ($eventi_controller as $e) {
        if ($e['aula_id'] == 2) {
            echo "  - {$e['ora_inizio']}-{$e['ora_fine']}: {$e['allievo']} (Tipo: {$e['tipo']})\n";
        }
    }
} else {
    echo "⚠️ Controller non ha caricato eventi!\n";
}

echo "\n✅ Check completato!\n";
?>