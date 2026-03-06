<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

try {
    $db = Database::getInstance()->getConnection();
    
    $evento_id = $_POST['evento_id'] ?? '';
    $ora_inizio = $_POST['ora_inizio'] ?? '';
    $ora_fine = $_POST['ora_fine'] ?? '';
    $aula_id = $_POST['aula_id'] ?? '';
    
    if (!$evento_id || !$ora_inizio || !$ora_fine || !$aula_id) {
        throw new Exception('Parametri obbligatori mancanti');
    }
    
    // Aggiorna evento
    $stmt = $db->prepare("
        UPDATE eventi_calendario 
        SET ora_inizio = ?, ora_fine = ?, aula_id = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$ora_inizio, $ora_fine, $aula_id, $evento_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
