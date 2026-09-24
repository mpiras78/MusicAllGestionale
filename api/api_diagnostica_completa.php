<?php
/**
 * Debug API: Diagnostica completa recuperi
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo json_encode([
        'success' => true,
        'diagnostica' => [
            'tipologia_recupero' => (function() use ($db) {
                $result = $db->query("
                    SELECT id, codice, nome, categoria, attiva
                    FROM tipologie_evento
                    WHERE codice = 'LEZ_RECUPERO'
                ")->fetch(PDO::FETCH_ASSOC);
                return $result ? $result : ['error' => 'Non trovata'];
            })(),
            
            'recuperi_totali_attivi' => (function() use ($db) {
                return $db->query("SELECT COUNT(*) as count FROM recuperi WHERE annullato = 0")->fetch(PDO::FETCH_ASSOC)['count'];
            })(),
            
            'evento_calendario_recuperi' => (function() use ($db) {
                return $db->query("
                    SELECT COUNT(*) as count FROM eventi_calendario e
                    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
                    WHERE t.codice = 'LEZ_RECUPERO'
                ")->fetch(PDO::FETCH_ASSOC)['count'];
            })(),
            
            'ultimo_recupero_sincronizzato' => (function() use ($db) {
                $result = $db->query("
                    SELECT 
                        e.id,
                        e.data_evento,
                        e.ora_inizio,
                        e.attivo,
                        e.confermato,
                        t.attiva as tipologia_attiva
                    FROM eventi_calendario e
                    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
                    WHERE t.codice = 'LEZ_RECUPERO'
                    ORDER BY e.created_at DESC
                    LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                return $result ? $result : ['message' => 'Nessuno'];
            })(),
            
            'filter_attivo' => (function() use ($db) {
                return $db->query("
                    SELECT COUNT(*) as count FROM eventi_calendario e
                    WHERE e.data_evento >= DATE('now')
                    AND e.attivo = 0
                    AND EXISTS (
                        SELECT 1 FROM tipologie_evento t 
                        WHERE t.id = e.tipologia_id 
                        AND t.codice = 'LEZ_RECUPERO'
                    )
                ")->fetch(PDO::FETCH_ASSOC)['count'];
            })()
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
