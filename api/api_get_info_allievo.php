<?php
/**
 * API: Ottiene informazioni complete allievo
 * Per modal info da calendario
 */

header('Content-Type: application/json');
require_once '../includes/bootstrap.php';

// Verifica autenticazione
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

// Ottieni allievo_id
$allievo_id = $_GET['allievo_id'] ?? null;

if (!$allievo_id) {
    http_response_code(400);
    echo json_encode(['error' => 'allievo_id richiesto']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Dati allievo
    $allievo = $db->queryOne("
        SELECT id, cognome, nome, email, telefono, data_nascita, indirizzo, note
        FROM allievi
        WHERE id = ?
    ", [$allievo_id]);
    
    if (!$allievo) {
        http_response_code(404);
        echo json_encode(['error' => 'Allievo non trovato']);
        exit;
    }
    
    // Conta assenze totali
    $assenze = $db->queryOne("
        SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN da_recuperare = 1 THEN 1 ELSE 0 END) as da_recuperare,
            SUM(CASE WHEN tipo = 'allievo' THEN 1 ELSE 0 END) as causate_da_allievo,
            SUM(CASE WHEN tipo = 'docente' THEN 1 ELSE 0 END) as causate_da_docente
        FROM assenze
        WHERE allievo_id = ?
    ", [$allievo_id]);
    
    // Conta recuperi totali
    $recuperi = $db->queryOne("
        SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN annullato = 0 AND data_recupero >= DATE('now') THEN 1 ELSE 0 END) as programmati,
            SUM(CASE WHEN annullato = 0 AND data_recupero < DATE('now') THEN 1 ELSE 0 END) as completati,
            SUM(CASE WHEN annullato = 1 THEN 1 ELSE 0 END) as annullati
        FROM recuperi
        WHERE allievo_id = ?
    ", [$allievo_id]);
    
    // Lista corsi (lezioni ricorrenti) a cui è iscritto
    $corsi = $db->query("
        SELECT DISTINCT
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente,
            au.nome as aula
        FROM lezioni l
        JOIN docenti d ON l.docente_id = d.id
        LEFT JOIN materie m ON l.materia_id = m.id
        LEFT JOIN aule au ON l.aula_id = au.id
        WHERE l.allievo_id = ?
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
    ", [$allievo_id]);
    
    // Prossimi recuperi programmati
    $prossimi_recuperi = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente,
            au.nome as aula
        FROM recuperi r
        JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN aule au ON r.aula_id = au.id
        WHERE r.allievo_id = ?
        AND r.annullato = 0
        AND r.data_recupero >= DATE('now')
        ORDER BY r.data_recupero, r.ora_inizio
        LIMIT 5
    ", [$allievo_id]);
    
    // Prepara risposta
    $response = [
        'allievo' => [
            'id' => $allievo['id'],
            'nome_completo' => $allievo['cognome'] . ' ' . $allievo['nome'],
            'cognome' => $allievo['cognome'],
            'nome' => $allievo['nome'],
            'email' => $allievo['email'],
            'telefono' => $allievo['telefono'],
            'data_nascita' => $allievo['data_nascita'],
            'indirizzo' => $allievo['indirizzo'],
            'note' => $allievo['note']
        ],
        'statistiche' => [
            'assenze' => [
                'totale' => (int)$assenze['totale'],
                'da_recuperare' => (int)$assenze['da_recuperare'],
                'causate_da_allievo' => (int)$assenze['causate_da_allievo'],
                'causate_da_docente' => (int)$assenze['causate_da_docente']
            ],
            'recuperi' => [
                'totale' => (int)$recuperi['totale'],
                'programmati' => (int)$recuperi['programmati'],
                'completati' => (int)$recuperi['completati'],
                'annullati' => (int)$recuperi['annullati']
            ]
        ],
        'corsi' => $corsi ?: [],
        'prossimi_recuperi' => $prossimi_recuperi ?: []
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}