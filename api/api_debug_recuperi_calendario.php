<?php
/**
 * Debug API: Verifica stato recuperi appena sincronizzati
 */

require_once '../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non autorizzato');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // 1. Ultimi 5 evento_calendario creati per recuperi
    $result = $db->query("
        SELECT 
            e.id,
            e.data_evento,
            e.ora_inizio,
            e.ora_fine,
            e.attivo,
            e.confermato,
            e.socio_id,
            e.created_at,
            t.codice,
            t.categoria,
            al.cognome || ' ' || al.nome as socio_nome
        FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        LEFT JOIN soci al ON e.socio_id = al.id
        WHERE t.codice = 'LEZ_RECUPERO'
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    
    $eventi = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($eventi),
        'message' => 'Ultimi evento_calendario creati per recuperi',
        'data' => $eventi
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
