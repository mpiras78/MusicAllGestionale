<?php
/**
 * API: Ottieni lezioni per socio
 * Usata dal form creazione assenze
 */

// Previeni output HTML indesiderato
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/bootstrap.php';

// Pulisci buffer e imposta header JSON PRIMA di tutto
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verifica autenticazione PRIMA
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato', 'requiresLogin' => true]);
    exit;
}

// Verifica permessi
if (!$auth->hasRole(['admin', 'segreteria'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato', 'role' => $auth->getUserRole()]);
    exit;
}

// Ottieni parametro
$socio_id = $_GET['socio_id'] ?? null;

if (!$socio_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Parametro socio_id mancante']);
    exit;
}

try {
    // Ottieni istanza Database
    $db = Database::getInstance();
    
    // Query lezioni dell'socio
    $lezioni = $db->query("
        SELECT 
            l.id,
            l.giorno_settimana,
            LOWER(l.giorno_settimana) as giorno_settimana_lower,
            l.ora_inizio,
            l.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
        WHERE l.socio_id = ?
        ORDER BY 
            CASE l.giorno_settimana
                WHEN 'Lunedì' THEN 1
                WHEN 'Martedì' THEN 2
                WHEN 'Mercoledì' THEN 3
                WHEN 'Giovedì' THEN 4
                WHEN 'Venerdì' THEN 5
                WHEN 'Sabato' THEN 6
                WHEN 'Domenica' THEN 7
            END,
            l.ora_inizio
    ", [$socio_id]);
    
    echo json_encode([
        'success' => true,
        'lezioni' => $lezioni ?: [],
        'count' => count($lezioni ?: []),
        'message' => empty($lezioni) ? 'Nessuna lezione trovata per questo socio' : null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}