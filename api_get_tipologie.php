<?php
/**
 * API Get Tipologie Eventi
 * Restituisce lista tipologie per select forms
 */

require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

// Autenticazione richiesta
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT id, codice, nome, categoria, descrizione, 
               colore_bg, colore_border, icona
        FROM tipologie_evento 
        WHERE attiva = 1 AND categoria = 'prenotazione'
        ORDER BY ordine_visualizzazione, nome
    ");
    
    $stmt->execute();
    $tipologie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $tipologie
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
