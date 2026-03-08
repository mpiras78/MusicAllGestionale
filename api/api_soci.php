<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

// Estrai action da GET, POST o JSON body
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Se non trovato nei parametri standard, controlla il JSON body
if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? '';
} else {
    // Leggi il JSON body una volta per essere riusato dopo
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
}

$controller = new SociController();
$db = Database::getInstance();

try {
    switch ($action) {
        case 'list':
            $soci = $controller->getSoci();
            echo json_encode($soci);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $data['id'] ?? null;
            if (!$id) {
                throw new Exception('ID socio richiesto');
            }
            $socio = $controller->getSocioById($id);
            if (!$socio) {
                throw new Exception('Socio non trovato');
            }
            echo json_encode(['success' => true, 'data' => $socio]);
            break;
            
        case 'search':
            $query = $_GET['query'] ?? $data['query'] ?? '';
            if (empty($query)) {
                throw new Exception('Parametro di ricerca richiesto');
            }
            $results = $controller->searchSoci($query);
            echo json_encode(['success' => true, 'data' => $results]);
            break;
            
        case 'create':
            $required = ['nome', 'cognome'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo '$field' richiesto");
                }
            }
            $id = $controller->createSocio($data);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Socio creato correttamente']);
            break;
            
        case 'update':
            $id = $data['id'] ?? null;
            if (!$id) {
                throw new Exception('ID socio richiesto per aggiornamento');
            }
            $required = ['nome', 'cognome'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo '$field' richiesto");
                }
            }
            $controller->updateSocio($id, $data);
            echo json_encode(['success' => true, 'message' => 'Socio aggiornato correttamente']);
            break;
            
        case 'delete':
            $id = $data['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID socio richiesto per eliminazione');
            }
            $controller->disattivaSocio($id);
            echo json_encode(['success' => true, 'message' => 'Socio disattivato correttamente']);
            break;
            
        case 'count':
            $attivi_only = $_GET['attivi_only'] ?? $data['attivi_only'] ?? true;
            $count = $controller->countSoci($attivi_only);
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        case 'stats':
            $stats = $controller->getStatisticheLezioni();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            throw new Exception('Azione non riconosciuta: ' . $action);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
