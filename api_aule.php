<?php
require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$action = $_GET['action'] ?? '';

$controller = new AuleController();

try {
    switch ($action) {
        case 'list':
            $aule = $controller->getAule();
            echo json_encode(['success' => true, 'data' => $aule]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
