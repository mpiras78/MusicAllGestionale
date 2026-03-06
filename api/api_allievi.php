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

$controller = new AllieviController();
$db = Database::getInstance();

try {
    switch ($action) {
        case 'list':
            $allievi = $controller->getAllievi();
            echo json_encode($allievi);
            break;
        
        case 'get':
            // Ottieni i dati di un singolo allievo
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID allievo mancante');
            }
            
            $allievo = $controller->getAllievoById($id);
            if (!$allievo) {
                throw new Exception('Allievo non trovato');
            }
            
            echo json_encode(['success' => true, 'data' => $allievo]);
            break;
        
        case 'create':
            // Crea un nuovo allievo
            if (!isset($data['nome']) || !isset($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            $sql = "INSERT INTO allievi (nome, cognome, email, telefono, data_nascita, indirizzo, note, attivo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $result = $db->execute($sql, [
                $data['nome'],
                $data['cognome'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['data_nascita'] ?? null,
                $data['indirizzo'] ?? null,
                $data['note'] ?? null,
                1
            ]);
            
            if (!$result) {
                throw new Exception('Errore durante l\'inserimento dell\'allievo');
            }
            
            echo json_encode(['success' => true, 'message' => 'Allievo creato correttamente']);
            break;
        
        case 'update':
            // Aggiorna un allievo esistente
            if (!isset($data['id'])) {
                throw new Exception('ID allievo mancante');
            }
            
            if (!isset($data['nome']) || !isset($data['cognome'])) {
                throw new Exception('Nome e cognome sono obbligatori');
            }
            
            $sql = "UPDATE allievi SET nome = ?, cognome = ?, email = ?, telefono = ?, 
                    data_nascita = ?, indirizzo = ?, note = ? WHERE id = ?";
            
            $result = $db->execute($sql, [
                $data['nome'],
                $data['cognome'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['data_nascita'] ?? null,
                $data['indirizzo'] ?? null,
                $data['note'] ?? null,
                $data['id']
            ]);
            
            if (!$result) {
                throw new Exception('Errore durante l\'aggiornamento dell\'allievo');
            }
            
            echo json_encode(['success' => true, 'message' => 'Allievo aggiornato correttamente']);
            break;
        
        case 'delete':
            // Disattiva un allievo
            if (!isset($data['id'])) {
                throw new Exception('ID allievo mancante');
            }
            
            // Controlla se ha iscrizioni attive nel mese corrente
            $mese_corrente = date('Y-m');
            $sql_check = "SELECT COUNT(*) as count FROM iscrizioni 
                         WHERE allievo_id = ? 
                         AND stato = 'attiva'
                         AND date(data_inizio) <= date('now')
                         AND (data_fine IS NULL OR date(data_fine) >= date('now'))";
            
            $stmt = $db->prepare($sql_check);
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();
            
            if ($result && $result['count'] > 0) {
                throw new Exception('L\'utente ha una iscrizione attiva in questo mese');
            }
            
            // Soft delete - imposta attivo a 0 invece di eliminare
            $sql = "UPDATE allievi SET attivo = 0 WHERE id = ?";
            $result = $db->execute($sql, [$data['id']]);
            
            if (!$result) {
                throw new Exception('Errore durante la disattivazione dell\'allievo');
            }
            
            echo json_encode(['success' => true, 'message' => 'Allievo disattivato correttamente']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

