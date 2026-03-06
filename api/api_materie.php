<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $auth->requireLogin();
    
    // Estrai l'action dai parametri GET, POST o JSON
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $action = $data['action'] ?? '';
    }
    
    if (!$action) {
        throw new Exception('Azione non valida', 400);
    }
    
    $db = Database::getInstance();
    
    switch ($action) {
        case 'get':
            // Ottiene una materia specifica
            if (!isset($_GET['id'])) {
                throw new Exception('ID non fornito', 400);
            }
            
            $id = $_GET['id'];
            $sql = "SELECT * FROM materie WHERE id = ?";
            $materia = $db->queryOne($sql, [$id]);
            
            if (!$materia) {
                throw new Exception('Materia non trovata', 404);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $materia
            ]);
            break;
        
        case 'list':
            // Elenca tutte le materie con conteggio iscrizioni
            $sql = "
                SELECT m.*, COUNT(DISTINCT i.id) as num_iscrizioni_attive
                FROM materie m
                LEFT JOIN iscrizioni i ON m.id = i.materia_id AND i.stato = 'attiva'
                GROUP BY m.id
                ORDER BY m.nome
            ";
            $materie = $db->query($sql);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $materie ?: []
            ]);
            break;
        
        case 'create':
            // Crea una nuova materia
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!isset($data['nome']) || empty($data['nome'])) {
                throw new Exception('Nome materia obbligatorio', 400);
            }
            
            // Verifica se già esiste una materia con lo stesso nome
            $esistente = $db->queryOne("SELECT id FROM materie WHERE nome = ?", [$data['nome']]);
            if ($esistente) {
                throw new Exception('Una materia con questo nome esiste già', 409);
            }
            
            $sql = "INSERT INTO materie (nome) VALUES (?)";
            $result = $db->execute($sql, [$data['nome']]);
            
            if (!$result) {
                throw new Exception('Errore durante la creazione della materia', 500);
            }
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Materia creata correttamente'
            ]);
            break;
        
        case 'update':
            // Aggiorna una materia
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!isset($data['id'])) {
                throw new Exception('ID non fornito', 400);
            }
            
            if (!isset($data['nome']) || empty($data['nome'])) {
                throw new Exception('Nome materia obbligatorio', 400);
            }
            
            // Verifica che la materia esista
            $materia = $db->queryOne("SELECT id FROM materie WHERE id = ?", [$data['id']]);
            if (!$materia) {
                throw new Exception('Materia non trovata', 404);
            }
            
            // Verifica se esiste un'altra materia con lo stesso nome
            $esistente = $db->queryOne("SELECT id FROM materie WHERE nome = ? AND id != ?", [$data['nome'], $data['id']]);
            if ($esistente) {
                throw new Exception('Una materia con questo nome esiste già', 409);
            }
            
            $sql = "UPDATE materie SET nome = ? WHERE id = ?";
            $result = $db->execute($sql, [$data['nome'], $data['id']]);
            
            if (!$result) {
                throw new Exception('Errore durante l\'aggiornamento della materia', 500);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Materia aggiornata correttamente'
            ]);
            break;
        
        case 'delete':
            // Elimina una materia
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!isset($data['id'])) {
                throw new Exception('ID non fornito', 400);
            }
            
            // Verifica che la materia esista
            $materia = $db->queryOne("SELECT id FROM materie WHERE id = ?", [$data['id']]);
            if (!$materia) {
                throw new Exception('Materia non trovata', 404);
            }
            
            // Verifica se la materia è usata da iscrizioni attive
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM iscrizioni 
                WHERE materia_id = ? AND stato = 'attiva'
            ");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['count'] > 0) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'message' => 'Questa materia è utilizzata da ' . $result['count'] . ' iscrizione/i attiva/e'
                ]);
                exit;
            }
            
            // Esegui l'eliminazione
            $sql = "DELETE FROM materie WHERE id = ?";
            $delResult = $db->execute($sql, [$data['id']]);
            
            if (!$delResult) {
                throw new Exception('Errore durante l\'eliminazione della materia', 500);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Materia eliminata correttamente'
            ]);
            break;
        
        default:
            throw new Exception('Azione non riconosciuta: ' . $action, 400);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
