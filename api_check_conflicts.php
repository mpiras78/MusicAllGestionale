<?php
require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$giorno = $_GET['giorno'] ?? 0;
$oraInizio = $_GET['ora_inizio'] ?? '';
$aulaId = $_GET['aula_id'] ?? 0;
$tipoCorsoId = $_GET['tipo_corso_id'] ?? 0;

$db = Database::getInstance()->getConnection();

// Calcola ora fine in base alla durata del corso
$stmt = $db->prepare("SELECT durata_lezione FROM tipi_corso_config WHERE id = ?");
$stmt->execute([$tipoCorsoId]);
$durata = $stmt->fetchColumn();

if (!$durata) {
    echo json_encode(['conflict' => false]);
    exit;
}

$oraFine = date('H:i', strtotime($oraInizio) + ($durata * 60));

// Cerca conflitti nella stessa aula, stesso giorno
$stmt = $db->prepare("
    SELECT l.*, 
    CONCAT(a.cognome, ' ', a.nome) as allievo,
    CONCAT(d.cognome, ' ', d.nome) as docente,
    m.nome as materia
    FROM lezioni l
    LEFT JOIN allievi a ON l.allievo_id = a.id
    LEFT JOIN docenti d ON l.docente_id = d.id
    LEFT JOIN materie m ON l.materia_id = m.id
    WHERE l.giorno_settimana = ?
    AND l.aula_id = ?
    AND l.attiva = 1
    AND (
        (l.ora_inizio < ? AND l.ora_fine > ?) OR
        (l.ora_inizio >= ? AND l.ora_inizio < ?) OR
        (l.ora_fine > ? AND l.ora_fine <= ?)
    )
    LIMIT 1
");

$stmt->execute([
    $giorno, 
    $aulaId,
    $oraFine, $oraInizio,  // Overlap start
    $oraInizio, $oraFine,  // Overlap middle
    $oraInizio, $oraFine   // Overlap end
]);

$conflict = $stmt->fetch(PDO::FETCH_ASSOC);

$response = ['conflict' => false];

if ($conflict) {
    $response['conflict'] = $conflict;
    
    // Cerca aule alternative disponibili
    $stmt = $db->prepare("
        SELECT au.id, au.nome
        FROM aule au
        WHERE au.attiva = 1
        AND au.id NOT IN (
            SELECT l.aula_id
            FROM lezioni l
            WHERE l.giorno_settimana = ?
            AND l.attiva = 1
            AND l.aula_id IS NOT NULL
            AND (
                (l.ora_inizio < ? AND l.ora_fine > ?) OR
                (l.ora_inizio >= ? AND l.ora_inizio < ?) OR
                (l.ora_fine > ? AND l.ora_fine <= ?)
            )
        )
        ORDER BY au.ordine_visualizzazione, au.nome
    ");
    
    $stmt->execute([
        $giorno,
        $oraFine, $oraInizio,
        $oraInizio, $oraFine,
        $oraInizio, $oraFine
    ]);
    
    $response['alternatives'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($response);
