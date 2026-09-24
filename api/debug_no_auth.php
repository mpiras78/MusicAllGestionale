<?php
/**
 * Debug - No auth required
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/bootstrap.php';
    
    $db = Database::getInstance()->getConnection();
    
    $data = $_GET['data'] ?? date('Y-m-d');
    
    $recuperi = $db->query("
        SELECT COUNT(*) as count FROM recuperi 
        WHERE data_recupero = ? AND annullato = 0
    ", [$data])->fetch(PDO::FETCH_ASSOC)['count'];
    
    $evento_cal = $db->query("
        SELECT COUNT(*) as count FROM eventi_calendario e
        LEFT JOIN tipologie_evento t ON e.tipologia_id = t.id
        WHERE e.data_evento = ?
    ", [$data])->fetch(PDO::FETCH_ASSOC)['count'];
    
    $tipologia = $db->query("
        SELECT attiva FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO'
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'recuperi_db' => (int)$recuperi,
        'evento_calendario' => (int)$evento_cal,
        'tipologia_lez_recupero' => [
            'exists' => $tipologia !== false,
            'attiva' => $tipologia ? (bool)$tipologia['attiva'] : null
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
