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

// Ottieni socio_id (accetta anche allievo_id per retrocompatibilità)
$socio_id = $_GET['socio_id'] ?? $_GET['allievo_id'] ?? null;

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
    
    // Recuperi
    $recuperi = $db->queryOne("
        SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN completato = 1 THEN 1 ELSE 0 END) as completati,
            SUM(CASE WHEN completato = 0 THEN 1 ELSE 0 END) as da_completare,
            SUM(CASE WHEN programmato = 1 THEN 1 ELSE 0 END) as programmati
        FROM recuperi
        WHERE socio_id = ?
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
            l.aula
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
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
            ia.stato
        FROM iscrizioni_annuali ia
        WHERE ia.socio_id = ?
        ORDER BY ia.anno_accademico DESC
        LIMIT 3
    ", [$socio_id]);
    
    echo json_encode([
        'success' => true,
        'socio' => $socio,
        'statistiche' => [
            'assenze' => $assenze ?: ['totale' => 0, 'da_recuperare' => 0],
            'recuperi' => $recuperi ?: ['totale' => 0, 'completati' => 0, 'programmati' => 0]
        ],
        'corsi' => $corsi ?: [],
        'iscrizioni' => $iscrizioni ?: []
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
