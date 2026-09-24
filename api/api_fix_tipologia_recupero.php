<?php
/**
 * API: Fix tipologia LEZ_RECUPERO
 * Attiva la tipologia se disattivata
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'segreteria'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Controlla lo stato attuale
    $tipologia = $db->query("
        SELECT id, codice, attiva FROM tipologie_evento
        WHERE codice = 'LEZ_RECUPERO'
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipologia) {
        // Crea la tipologia se non esiste
        $db->prepare("
            INSERT INTO tipologie_evento (codice, nome, categoria, colore_bg, colore_border, attiva)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            'LEZ_RECUPERO',
            'Lezione Recupero',
            'recupero',
            '#FFC107',  // Yellow
            '#FF9800',  // Orange
            1
        ]);
        
        echo json_encode([
            'success' => true,
            'action' => 'created',
            'message' => 'Tipologia LEZ_RECUPERO creata e attivata'
        ]);
    } else if ($tipologia['attiva'] == 0) {
        // Attiva la tipologia
        $db->prepare("
            UPDATE tipologie_evento 
            SET attiva = 1 
            WHERE id = ?
        ")->execute([$tipologia['id']]);
        
        echo json_encode([
            'success' => true,
            'action' => 'activated',
            'message' => 'Tipologia LEZ_RECUPERO attivata'
        ]);
    } else {
        // Già attiva
        echo json_encode([
            'success' => true,
            'action' => 'already_active',
            'message' => 'Tipologia LEZ_RECUPERO già attiva'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
