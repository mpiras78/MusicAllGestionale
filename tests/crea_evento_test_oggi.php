<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Crea evento di test per oggi
$db = Database::getInstance()->getConnection();

$oggi = date('Y-m-d');
$giorno_oggi = strtolower(date('l'));
$mapping = [
    'monday' => 'lunedi',
    'tuesday' => 'martedi',
    'wednesday' => 'mercoledi',
    'thursday' => 'giovedi',
    'friday' => 'venerdi',
    'saturday' => 'sabato',
    'sunday' => 'domenica'
];
$giorno_it = $mapping[$giorno_oggi] ?? 'sabato';

echo "=== CREA EVENTO TEST PER OGGI ===\n\n";
echo "Data: $oggi ($giorno_it)\n\n";

// Trova una lezione per oggi
$stmt = $db->prepare("
    SELECT l.*, au.nome as aula_nome, p.cognome || ' ' || p.nome as allievo_nome
    FROM lezioni l
    INNER JOIN aule au ON l.aula_id = au.id
    INNER JOIN soci s ON l.allievo_id = s.id
    INNER JOIN persone p ON s.persona_id = p.id
    WHERE l.giorno_settimana = ?
    LIMIT 1
");
$stmt->execute([$giorno_it]);
$lezione = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lezione) {
    echo "❌ Nessuna lezione trovata per $giorno_it\n";
    echo "Non posso creare evento di test.\n";
    exit(1);
}

echo "Lezione trovata:\n";
echo "- Aula: {$lezione['aula_nome']} (ID: {$lezione['aula_id']})\n";
echo "- Socio: {$lezione['allievo_nome']} (ID: {$lezione['allievo_id']})\n";
echo "- Orario: {$lezione['ora_inizio']} - {$lezione['ora_fine']}\n\n";

// Crea prenotazione 15 minuti dopo inizio lezione
$ora_inizio_evento = date('H:i:s', strtotime($lezione['ora_inizio']) + 15*60);
$ora_fine_evento = date('H:i:s', strtotime($ora_inizio_evento) + 45*60);

echo "Creando prenotazione test:\n";
echo "- Orario: $ora_inizio_evento - $ora_fine_evento\n";
echo "- Aula: {$lezione['aula_nome']}\n";
echo "- Socio: {$lezione['allievo_nome']}\n\n";

// Inserisci evento
$stmt = $db->prepare("
    INSERT INTO eventi_calendario (
        tipologia_id, data_evento, ora_inizio, ora_fine,
        aula_id, allievo_id, docente_id, materia_id,
        titolo, note, attivo, confermato, created_at
    ) VALUES (
        6, ?, ?, ?,
        ?, ?, ?, ?,
        'TEST - Prenotazione Debug', 'Evento di test per debug visualizzazione', 1, 1, datetime('now')
    )
");

$result = $stmt->execute([
    $oggi,
    $ora_inizio_evento,
    $ora_fine_evento,
    $lezione['aula_id'],
    $lezione['allievo_id'],
    $lezione['docente_id'],
    $lezione['materia_id']
]);

if ($result) {
    $evento_id = $db->lastInsertId();
    echo "✅ Evento creato con successo! ID: $evento_id\n\n";
    echo "Ora apri il calendario su http://localhost e vai alla data di OGGI ($oggi)\n";
    echo "Dovresti vedere la prenotazione sovrapposta alla lezione!\n";
} else {
    echo "❌ Errore durante creazione evento\n";
    print_r($stmt->errorInfo());
}
?>