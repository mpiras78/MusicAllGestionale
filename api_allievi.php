<?php
/**
 * API CRUD Allievi
 * Gestisce creazione, lettura, aggiornamento e cancellazione allievi
 */

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

// Headers JSON
header('Content-Type: application/json');

// Inizializza controller
$allieviCtrl = new AllieviController();

try {
    // Determina action
    $action = $_GET['action'] ?? $_POST['action'] ?? json_decode(file_get_contents('php://input'), true)['action'] ?? null;
    
    switch ($action) {
        case 'get':
            // GET singolo allievo
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID allievo mancante');
            }
            
            $allievo = $allieviCtrl->getAllievoById($id);
            if (!$allievo) {
                throw new Exception('Allievo non trovato');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $allievo
            ]);
            break;
            
        case 'create':
            // POST crea nuovo allievo
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione
            if (empty($data['nome']) || empty($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            // Crea allievo
            $id = $allieviCtrl->createAllievo($data);
            
            if (!$id) {
                throw new Exception('Errore durante la creazione dell\'allievo');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Allievo creato con successo',
                'id' => $id
            ]);
            break;
            
        case 'update':
            // POST aggiorna allievo
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione
            if (empty($data['id'])) {
                throw new Exception('ID allievo mancante');
            }
            if (empty($data['nome']) || empty($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            // Aggiorna allievo
            $result = $allieviCtrl->updateAllievo($data['id'], $data);
            
            if (!$result) {
                throw new Exception('Errore durante l\'aggiornamento dell\'allievo');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Allievo aggiornato con successo'
            ]);
            break;
            
        case 'delete':
            // POST disattiva allievo
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                throw new Exception('ID allievo mancante');
            }
            
            // Disattiva allievo
            $result = $allieviCtrl->disattivaAllievo($data['id']);
            
            if (!$result) {
                throw new Exception('Errore durante la disattivazione dell\'allievo');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Allievo disattivato con successo'
            ]);
            break;
            
        default:
            throw new Exception('Azione non valida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}