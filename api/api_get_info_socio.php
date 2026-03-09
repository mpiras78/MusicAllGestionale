<?php
/**
 * API: Ottiene informazioni complete socio
 * Per modal info da calendario - Versione aggiornata per soci
 */

header('Content-Type: application/json');
require_once '../includes/bootstrap.php';

// Verifica autenticazione
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

// Ottieni socio_id (accetta anche socio_id per retrocompatibilità)
$socio_id = $_GET['socio_id'] ?? $_GET['socio_id'] ?? null;

if (!$socio_id) {
    http_response_code(400);
    echo json_encode(['error' => 'socio_id richiesto']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Dati socio
    $socio = $db->queryOne("
        SELECT id, cognome, nome, email, telefono, data_nascita, indirizzo, note,
               cognome || ' ' || nome as nome_completo
        FROM soci
        WHERE id = ?
    ", [$socio_id]);
    
    if (!$socio) {
        http_response_code(404);
        echo json_encode(['error' => 'Socio non trovato']);
        exit;
    }
    
    // Conta assenze totali
    $assenze = $db->queryOne("
        SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN da_recuperare = 1 THEN 1 ELSE 0 END) as da_recuperare,
            SUM(CASE WHEN tipo = 'socio' THEN 1 ELSE 0 END) as causate_da_socio,
            SUM(CASE WHEN tipo = 'docente' THEN 1 ELSE 0 END) as causate_da_docente
        FROM assenze
        WHERE socio_id = ?
    ", [$socio_id]);
    
    // Recuperi - conta per stato usando colonne reali
    $recuperi = $db->queryOne("
        SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN annullato = 1 THEN 1 ELSE 0 END) as annullati,
            SUM(CASE WHEN annullato = 0 AND data_recupero < DATE('now') THEN 1 ELSE 0 END) as completati,
            SUM(CASE WHEN annullato = 0 AND data_recupero >= DATE('now') THEN 1 ELSE 0 END) as programmati
        FROM recuperi
        WHERE socio_id = ?
    ", [$socio_id]);

    // Prossimi recuperi (futuri, non annullati)
    $prossimi_recuperi = $db->query("
        SELECT
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente,
            au.nome as aula
        FROM recuperi r
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN aule au ON r.aula_id = au.id
        WHERE r.socio_id = ?
        AND r.annullato = 0
        AND r.data_recupero >= DATE('now')
        ORDER BY r.data_recupero, r.ora_inizio
        LIMIT 5
    ", [$socio_id]);
    
    // Corsi frequentati
    $corsi = $db->query("
        SELECT DISTINCT
            l.id,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            a.nome as aula
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
        JOIN aule a on a.id=l.aula_id 
        WHERE l.socio_id = ?
        AND l.attiva = 1
        ORDER BY l.giorno_settimana, l.ora_inizio
    ", [$socio_id]);
    
    // Iscrizioni annuali
    $iscrizioni = $db->query("
        SELECT 
            ia.id,
            ia.anno_accademico,
            ia.numero_tessera,
            ia.data_iscrizione,
            ia.stato_pagamento as stato
        FROM iscrizioni_annuali ia
        WHERE ia.socio_id = ?
        ORDER BY ia.anno_accademico DESC
        LIMIT 3
    ", [$socio_id]);
    
    echo json_encode([
        'success' => true,
        'socio' => $socio,
        'statistiche' => [
            'assenze' => $assenze ?: ['totale' => 0, 'da_recuperare' => 0, 'causate_da_socio' => 0, 'causate_da_docente' => 0],
            'recuperi' => $recuperi ?: ['totale' => 0, 'completati' => 0, 'programmati' => 0, 'annullati' => 0]
        ],
        'corsi' => $corsi ?: [],
        'iscrizioni' => $iscrizioni ?: [],
        'prossimi_recuperi' => $prossimi_recuperi ?: []
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
