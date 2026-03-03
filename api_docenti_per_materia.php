<?php
require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

$materia_id = $_GET['materia_id'] ?? null;

if (!$materia_id) {
    echo json_encode(['success' => false, 'error' => 'Materia ID mancante']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $docenti = $db->query("
        SELECT DISTINCT d.id, d.cognome, d.nome
        FROM docenti d
        JOIN docenti_materie dm ON d.id = dm.docente_id
        WHERE dm.materia_id = ? AND d.attivo = 1
        ORDER BY d.cognome, d.nome
    ", [$materia_id]);
    
    echo json_encode(['success' => true, 'docenti' => $docenti]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
