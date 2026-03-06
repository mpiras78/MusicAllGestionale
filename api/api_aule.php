<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("
        SELECT id, nome
        FROM aule
        WHERE attiva = 1
        ORDER BY ordine_visualizzazione, nome
    ");
    
    $aule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $aule]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
