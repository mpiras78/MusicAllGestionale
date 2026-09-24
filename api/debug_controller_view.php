<?php
/**
 * Verifica esatta: Cosa vede il controller
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/config.php';
    
    $db = new PDO('sqlite:' . __DIR__ . '/../database/musicall.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = $_GET['data'] ?? '2026-07-27';
    
    // Query identica al controller
    $query = "
        SELECT 
            e.id,
            e.ora_inizio,
            e.ora_fine,
            e.attivo,
            e.confermato,
            t.id as tipologia_id,
            t.codice as tipo,
            t.attiva as tipologia_attiva,
            e.data_evento,
            e.socio_id,
            a.cognome || ' ' || a.nome as socio,
            au.nome as aula
        FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        LEFT JOIN soci a ON e.socio_id = a.id
        LEFT JOIN aule au ON e.aula_id = au.id
        WHERE e.data_evento = ?
        ORDER BY e.ora_inizio
    ";
    
    $result = $db->prepare($query);
    $result->execute([$data]);
    $tutti_eventi = $result->fetchAll(PDO::FETCH_ASSOC);
    
    // Applica filtri del controller
    $eventi_filtrati = array_filter($tutti_eventi, function($e) {
        return $e['attivo'] == 1 && $e['tipologia_attiva'] == 1;
    });
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'query_result' => [
            'totali' => count($tutti_eventi),
            'dopo_filtro' => count($eventi_filtrati)
        ],
        'tutti_eventi' => $tutti_eventi,
        'dopo_filtri_controller' => array_values($eventi_filtrati),
        'filtri_applicati' => [
            'attivo = 1',
            'tipologia_attiva = 1'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
