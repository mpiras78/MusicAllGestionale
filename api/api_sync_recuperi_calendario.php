<?php
/**
 * Endpoint: Sincronizza recuperi con calendario
 * Accesso: GET|POST /api/api_sync_recuperi_calendario.php
 * 
 * Sincronizza tutti i recuperi che non hanno evento_calendario.
 * Utile quando ci sono recuperi "orfani" nel database.
 */

require_once '../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Verificazione autenticazione (opzionale - commentare se serve accesso pubblico per admin)
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non autorizzato');
    }
    
    if (!in_array($_SESSION['role'], ['admin', 'segreteria'])) {
        throw new Exception('Accesso limitato a admin/segreteria');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // 1. Conta recuperi senza evento_calendario
    $count_query = $db->query("
        SELECT COUNT(*) as count FROM recuperi r
        WHERE r.annullato = 0 
        AND NOT EXISTS (
            SELECT 1 FROM eventi_calendario e
            WHERE e.data_evento = r.data_recupero
            AND e.ora_inizio = r.ora_inizio
            AND e.socio_id = r.socio_id
        )
    ");
    $count_mancanti = $count_query->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count_mancanti == 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Nessun recupero da sincronizzare',
            'recuperi_sincronizzati' => 0,
            'recuperi_mancanti' => 0
        ]);
        exit;
    }
    
    // 2. Ottieni ID tipologia recupero
    $tipologia_query = $db->query("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO' LIMIT 1");
    $tipologia = $tipologia_query->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipologia) {
        throw new Exception('Tipologia evento "LEZ_RECUPERO" non trovata');
    }
    
    $tipologia_id = $tipologia['id'];
    
    // 3. Ottieni tutti i recuperi senza evento_calendario
    $recuperi_query = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            r.aula_id,
            r.docente_id,
            r.socio_id,
            r.materia_id,
            a.data_assenza
        FROM recuperi r
        LEFT JOIN assenze a ON r.assenza_id = a.id
        WHERE r.annullato = 0 
        AND NOT EXISTS (
            SELECT 1 FROM eventi_calendario e
            WHERE e.data_evento = r.data_recupero
            AND e.ora_inizio = r.ora_inizio
            AND e.socio_id = r.socio_id
        )
    ");
    
    $recuperi = $recuperi_query->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Inserisci eventi_calendario per ogni recupero
    $stmt = $db->prepare("
        INSERT INTO eventi_calendario (
            tipologia_id, ricorrente, giorno_settimana, data_evento,
            ora_inizio, ora_fine, aula_id, docente_id, socio_id, materia_id,
            titolo, note, confermato, attivo, created_at
        ) VALUES (?, 0, NULL, ?, ?, ?, ?, ?, ?, ?, 'Recupero', ?, 1, 1, datetime('now', 'localtime'))
    ");
    
    $inserted = 0;
    foreach ($recuperi as $recupero) {
        $data_assenza_formattata = '';
        if (!empty($recupero['data_assenza'])) {
            $data_assenza_formattata = date('d/m/Y', strtotime($recupero['data_assenza']));
        }
        $note = $data_assenza_formattata ? "Recupero lezione del {$data_assenza_formattata}" : "Recupero lezione";
        
        try {
            $stmt->execute([
                $tipologia_id,
                $recupero['data_recupero'],
                $recupero['ora_inizio'],
                $recupero['ora_fine'],
                $recupero['aula_id'],
                $recupero['docente_id'],
                $recupero['socio_id'],
                $recupero['materia_id'],
                $note
            ]);
            $inserted++;
        } catch (Exception $e) {
            // Potrebbe fallire se l'evento esiste già (duplicate key)
            // Continua comunque
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Sincronizzazione completata: {$inserted} recuperi aggiunti al calendario",
        'recuperi_sincronizzati' => $inserted,
        'recuperi_mancanti' => count($recuperi),
        'inseriti' => $inserted
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
