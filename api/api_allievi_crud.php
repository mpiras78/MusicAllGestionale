<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? '';

$controller = new AllieviController();

try {
    switch ($action) {
        case 'create':
            $postData = $data['data'];
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO allievi (cognome, nome, data_nascita, email, telefono, indirizzo, attivo)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $postData['cognome'],
                $postData['nome'],
                $postData['data_nascita'] ?? null,
                $postData['email'] ?? null,
                $postData['telefono'] ?? null,
                $postData['indirizzo'] ?? null,
                $postData['attivo'] ?? 1
            ]);
            
            $id = $db->lastInsertId();
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'delete':
            $id = $data['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID allievo mancante');
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE allievi SET attivo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
