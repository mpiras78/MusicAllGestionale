<?php
/**
 * Test API per browser - simula chiamata AJAX
 */

require_once 'includes/bootstrap.php';

// Simula parametro GET
$socio_id = $_GET['socio_id'] ?? 98; // Garau di default

header('Content-Type: application/json');

try {
    // Stessa query dell'API
    $lezioni = $db->query("
        SELECT 
            l.id,
            l.giorno_settimana,
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
    
    // Response strutturata come l'API reale
    echo json_encode([
        'success' => true,
        'lezioni' => $lezioni ?: [],
        'count' => count($lezioni ?: []),
        'message' => empty($lezioni) ? 'Nessuna lezione trovata per questo socio' : null,
        'debug' => [
            'socio_id' => $socio_id,
            'query_executed' => true,
            'raw_count' => count($lezioni)
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}