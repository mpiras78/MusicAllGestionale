<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "=== RECUPERI per 18 febbraio 2026 ===\n\n";

// Check tabella recuperi
$stmt = $db->query(
"SELECT r.*, a.cognome, a.nome, au.nome as aula_nome
    FROM recuperi r
    LEFT JOIN soci s ON r.allievo_id = s.id
    LEFT JOIN persone a ON s.persona_id = a.id
    LEFT JOIN aule au ON r.aula_id = au.id
    WHERE r.data_recupero = '2026-02-18'
    ORDER BY r.id DESC
");

$recuperi_table = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Trovati " . count($recuperi_table) . " recuperi in tabella 'recuperi':\n";
foreach ($recuperi_table as $rec) {
    echo "- ID: {$rec['id']}, Allievo: {$rec['cognome']} {$rec['nome']}, ";
    echo "Orario: {$rec['ora_inizio']}-{$rec['ora_fine']}, ";
    echo "Aula: {$rec['aula_nome']}, Stato: {$rec['stato']}\n";
}

echo "\n=== EVENTI_CALENDARIO per 18 febbraio 2026 ===\n\n";

// Check tabella eventi_calendario
$stmt = $db->query("
        SELECT e.*, t.nome as tipologia_nome, t.categoria,
            a.cognome, a.nome, au.nome as aula_nome
        FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        LEFT JOIN soci s ON e.allievo_id = s.id
        LEFT JOIN persone a ON s.persona_id = a.id
        LEFT JOIN aule au ON e.aula_id = au.id
        WHERE e.data_evento = '2026-02-18'
        AND e.attivo = 1
        ORDER BY e.id DESC
");

$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Trovati " . count($eventi) . " eventi in 'eventi_calendario':\n";
foreach ($eventi as $evt) {
    echo "- ID: {$evt['id']}, Tipo: {$evt['tipologia_nome']} ({$evt['categoria']}), ";
    echo "Allievo: {$evt['cognome']} {$evt['nome']}, ";
    echo "Orario: {$evt['ora_inizio']}-{$evt['ora_fine']}, ";
    echo "Aula: {$evt['aula_nome']}\n";
}

echo "\n=== ANALISI ===\n";
if (count($recuperi_table) > 0 && count($eventi) == 0) {
    echo "PROBLEMA: Ci sono recuperi nella tabella 'recuperi' ma NON in 'eventi_calendario'!\n";
    echo "Il calendario carica solo da 'eventi_calendario'.\n";
    echo "Soluzione: I recuperi devono essere creati ANCHE in eventi_calendario.\n";
} elseif (count($recuperi_table) == 0 && count($eventi) == 0) {
    echo "Nessun recupero trovato in nessuna tabella.\n";
} else {
    echo "OK: Recuperi presenti in eventi_calendario.\n";
}