<?php
/**
 * API per ottenere lista soci esterni attivi
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
    
    // Ottieni tutti i soci esterni attivi
    $stmt = $db->query("
        SELECT id, nome, cognome, email, telefono
        FROM soci_esterni
        WHERE attivo = 1
        ORDER BY cognome, nome
    ");
    
    $soci = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $soci
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}