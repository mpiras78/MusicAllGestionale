<?php
/**
 * API CRUD Docenti
 * Gestisce creazione, lettura, aggiornamento e cancellazione docenti
 */

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

// Headers JSON
header('Content-Type: application/json');

// Inizializza controller
$docentiCtrl = new DocentiController();

try {
    // Determina action
    $action = $_GET['action'] ?? $_POST['action'] ?? json_decode(file_get_contents('php://input'), true)['action'] ?? null;
    
    switch ($action) {
        case 'get':
            // GET singolo docente
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID docente mancante');
            }
            
            $docente = $docentiCtrl->getDocenteById($id);
            if (!$docente) {
                throw new Exception('Docente non trovato');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $docente
            ]);
            break;
            
        case 'create':
            // POST crea nuovo docente
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione
            if (empty($data['nome']) || empty($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            // Crea docente
            $id = $docentiCtrl->createDocente($data);
            
            if (!$id) {
                throw new Exception('Errore durante la creazione del docente');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Docente creato con successo',
                'id' => $id
            ]);
            break;
            
        case 'update':
            // POST aggiorna docente
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione
            if (empty($data['id'])) {
                throw new Exception('ID docente mancante');
            }
            if (empty($data['nome']) || empty($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            // Verifica che docente esista
            $docente = $docentiCtrl->getDocenteById($data['id']);
            if (!$docente) {
                throw new Exception('Docente non trovato');
            }
            
            // Aggiorna docente
            $docentiCtrl->updateDocente($data['id'], $data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Docente aggiornato con successo'
            ]);
            break;
            
        case 'delete':
            // POST disattiva docente
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                throw new Exception('ID docente mancante');
            }
            
            // Disattiva docente
            $result = $docentiCtrl->disattivaDocente($data['id']);
            
            if (!$result) {
                throw new Exception('Errore durante la disattivazione del docente');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Docente disattivato con successo'
            ]);
            break;
            
        case 'get_materie':
            // GET materie docente
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID docente mancante');
            }
            
            $materie_ids = $docentiCtrl->getMaterieIds($id);
            
            echo json_encode([
                'success' => true,
                'data' => $materie_ids
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