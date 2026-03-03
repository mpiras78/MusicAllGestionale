<?php
require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT * FROM materie WHERE attiva = 1 ORDER BY nome");
            $materie = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $materie]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
