<?php
/**
 * Debug Ultrapuro - Solo Database
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Carica solo config
    require_once __DIR__ . '/../config/config.php';
    
    // Crea connessione SQLite direttamente
    $db = new PDO('sqlite:' . __DIR__ . '/../database/musicall.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = $_GET['data'] ?? date('Y-m-d');
    
    // Query 1: Recuperi
    $result1 = $db->prepare("
        SELECT COUNT(*) as count FROM recuperi 
        WHERE data_recupero = ? AND annullato = 0
    ");
    $result1->execute([$data]);
    $recuperi = $result1->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Query 2: evento_calendario
    $result2 = $db->prepare("
        SELECT COUNT(*) as count FROM eventi_calendario 
        WHERE data_evento = ?
    ");
    $result2->execute([$data]);
    $evento_cal = $result2->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Query 3: Tipologia
    $result3 = $db->prepare("
        SELECT id, codice, attiva FROM tipologie_evento 
        WHERE codice = 'LEZ_RECUPERO'
    ");
    $result3->execute();
    $tipologia = $result3->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'recuperi_db' => (int)$recuperi,
        'evento_calendario_totali' => (int)$evento_cal,
        'tipologia_lez_recupero' => [
            'exists' => $tipologia !== false,
            'attiva' => $tipologia ? (bool)$tipologia['attiva'] : null,
            'id' => $tipologia ? $tipologia['id'] : null
        ],
        'diagnostica' => [
            'recuperi_senza_calendario' => ((int)$recuperi > 0 && (int)$evento_cal == 0),
            'tipologia_problema' => $tipologia && !(bool)$tipologia['attiva']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
