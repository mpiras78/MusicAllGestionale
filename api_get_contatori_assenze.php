<?php
/**
 * API: Ottieni contatori assenze/recuperi anno scolastico
 * Usata dal form creazione assenze
 */

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once 'includes/bootstrap.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verifica autenticazione
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

// Verifica permessi
if (!$auth->hasRole(['admin', 'segreteria'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Ottieni parametri
$allievo_id = $_GET['allievo_id'] ?? null;
$lezione_id = $_GET['lezione_id'] ?? null;

if (!$allievo_id || !$lezione_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Parametri allievo_id e lezione_id richiesti']);
    exit;
}

try {
    $assenzeCtrl = new AssenzeController();
    $contatori = $assenzeCtrl->getContatoriAnnoScolastico($allievo_id, $lezione_id);
    
    echo json_encode([
        'success' => true,
        'contatori' => $contatori
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}