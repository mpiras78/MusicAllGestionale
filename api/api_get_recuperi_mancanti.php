<?php
/**
 * API: Ottieni recuperi non sincronizzati
 * Endpoint: GET /api/api_get_recuperi_mancanti.php
 */

require_once '../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non autorizzato');
    }
    
    $db = Database::getInstance()->getConnection();
    
    $query = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            r.socio_id,
            al.cognome || ' ' || al.nome as socio_nome,
            r.aula_id,
            au.nome as aula_nome,
            r.docente_id,
            d.cognome || ' ' || d.nome as docente_nome
        FROM recuperi r
        LEFT JOIN soci al ON r.socio_id = al.id
        LEFT JOIN aule au ON r.aula_id = au.id
        LEFT JOIN docenti d ON r.docente_id = d.id
        WHERE r.annullato = 0 
        AND NOT EXISTS (
            SELECT 1 FROM eventi_calendario e
            WHERE e.data_evento = r.data_recupero
            AND e.ora_inizio = r.ora_inizio
            AND e.socio_id = r.socio_id
        )
        ORDER BY r.data_recupero DESC
        LIMIT 50
    ");
    
    $recuperi = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($recuperi),
        'data' => $recuperi
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
