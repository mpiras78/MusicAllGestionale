<?php
/**
 * Lista recuperi sincronizzati
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/config.php';
    
    $db = new PDO('sqlite:' . __DIR__ . '/../database/musicall.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ultimi 20 recuperi
    $result = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            r.socio_id,
            r.aula_id,
            a.cognome || ' ' || a.nome as socio_nome,
            au.nome as aula_nome,
            r.annullato,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM eventi_calendario e
                    WHERE e.data_evento = r.data_recupero
                    AND e.ora_inizio = r.ora_inizio
                    AND e.socio_id = r.socio_id
                ) THEN 'SÌ'
                ELSE 'NO'
            END as sincronizzato
        FROM recuperi r
        LEFT JOIN soci a ON r.socio_id = a.id
        LEFT JOIN aule au ON r.aula_id = au.id
        WHERE r.annullato = 0
        ORDER BY r.data_recupero DESC
        LIMIT 20
    ");
    
    $recuperi = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($recuperi),
        'recuperi' => $recuperi
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
