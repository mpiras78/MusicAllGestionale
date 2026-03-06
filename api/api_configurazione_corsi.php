<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$controller = new ConfigurazioneCorsiController();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? $_GET['action'] ?? '';
$tipo = $data['tipo'] ?? $_GET['tipo'] ?? '';

try {
    switch ($action) {
        case 'list':
            $tipiCorso = $controller->getTipiCorso(true);
            echo json_encode(['success' => true, 'data' => $tipiCorso]);
            break;
            
        case 'get':
            $id = $data['id'] ?? $_GET['id'] ?? 0;
            if ($tipo === 'corso') {
                $result = $controller->getTipoCorsoById($id);
            } elseif ($tipo === 'laboratorio') {
                $result = $controller->getTipoLaboratorioById($id);
            }
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'create':
            if ($tipo === 'corso') {
                $result = $controller->creaTipoCorso($data['data']);
            } elseif ($tipo === 'laboratorio') {
                $result = $controller->creaTipoLaboratorio($data['data']);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Creato con successo' : 'Errore']);
            break;
            
        case 'update':
            $id = $data['id'];
            if ($tipo === 'corso') {
                $result = $controller->aggiornaTipoCorso($id, $data['data']);
            } elseif ($tipo === 'laboratorio') {
                $result = $controller->aggiornaTipoLaboratorio($id, $data['data']);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Aggiornato con successo' : 'Errore']);
            break;
            
        case 'delete':
            $id = $data['id'];
            if ($tipo === 'corso') {
                $result = $controller->eliminaTipoCorso($id);
            } elseif ($tipo === 'laboratorio') {
                $result = $controller->eliminaTipoLaboratorio($id);
            }
            
            if (is_array($result)) {
                echo json_encode($result);
            } else {
                echo json_encode(['success' => $result, 'message' => $result ? 'Eliminato con successo' : 'Errore']);
            }
            break;
            
        case 'partecipanti':
            $id = $_GET['id'] ?? 0;
            $result = $controller->getPartecipantiLaboratorio($id);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
