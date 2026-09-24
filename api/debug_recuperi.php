<?php
/**
 * Debug Ultrasemplice - Output JSON
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $data = $_GET['data'] ?? date('Y-m-d');
    
    $recuperi = $db->query("
        SELECT COUNT(*) as count FROM recuperi 
        WHERE data_recupero = ? AND annullato = 0
    ", [$data])->fetch(PDO::FETCH_ASSOC)['count'];
    
    $evento_cal = $db->query("
        SELECT COUNT(*) as count FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        WHERE e.data_evento = ? AND t.categoria = 'recupero'
    ", [$data])->fetch(PDO::FETCH_ASSOC)['count'];
    
    $tipologia = $db->query("
        SELECT attiva FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO'
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'recuperi_db' => $recuperi,
        'evento_calendario' => $evento_cal,
        'tipologia_attiva' => $tipologia ? (bool)$tipologia['attiva'] : null,
        'message' => $recuperi > 0 && $evento_cal == 0 ? 'Recuperi non sincronizzati' : 'OK'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
