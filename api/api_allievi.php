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

// Reindirizzare a SociController ma mantenere compatibility aliases
$controller = new SociController();
$db = Database::getInstance();

try {
    switch ($action) {
        case 'list':
            $soci = $controller->getSoci();
            echo json_encode($soci);
            break;
        
        case 'get':
            // Ottieni i dati di un singolo socio
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID socio mancante');
            }
            
            $socio = $controller->getSocioById($id);
            if (!$socio) {
                throw new Exception('Socio non trovato');
            }
            
            echo json_encode(['success' => true, 'data' => $socio]);
            break;
        
        case 'create':
            // Crea un nuovo socio
            if (!isset($data['nome']) || !isset($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            $id = $controller->createSocio($data);
            
            if (!$id) {
                throw new Exception('Errore durante l\'inserimento del socio');
            }
            
            echo json_encode(['success' => true, 'message' => 'Socio creato correttamente']);
            break;
        
        case 'update':
            // Aggiorna un socio esistente
            if (!isset($data['id'])) {
                throw new Exception('ID socio mancante');
            }
            
            if (!isset($data['nome']) || !isset($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            $controller->updateSocio($data['id'], $data);
            
            echo json_encode(['success' => true, 'message' => 'Socio aggiornato correttamente']);
            break;
        
        case 'delete':
            // Disattiva un socio
            if (!isset($data['id'])) {
                throw new Exception('ID socio mancante');
            }
            
            $controller->disattivaSocio($data['id']);
            
            echo json_encode(['success' => true, 'message' => 'Socio disattivato correttamente']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

