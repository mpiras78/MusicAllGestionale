<?php
/**
 * API Helper: Recupera soci con lezioni per UI
 * Sostituisce l'helper per soci
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'soci_con_lezioni':
            $controller = new SociController();
            $soci = $controller->getSociConLezioni();
            echo json_encode(['success' => true, 'data' => $soci]);
            break;
            
        case 'soci_con_lezioni':
            // Retrocompatibilità - restituisce stessi dati con nome diverso
            $controller = new SociController();
            $soci = $controller->getSociConLezioni();
            echo json_encode(['success' => true, 'data' => $soci]);
            break;
            
        default:
            throw new Exception('Tipo helper non riconosciuto: ' . $type);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
