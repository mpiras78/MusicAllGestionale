<?php
/**
 * API: Controlla conflitti di sovrapposizione per recuperi
 * Verifica se ci sono lezioni, recuperi o altri eventi nella stessa sala
 * con sovrapposizione di orario (anche parziale)
 * 
 * Parametri:
 * - data_recupero (obbligatorio)
 * - ora_inizio (obbligatorio)
 * - ora_fine (obbligatorio)
 * - aula_id (obbligatorio)
 * - recupero_id (opzionale) - ID del recupero in modifica, da escludere dal check
 * - evento_id (opzionale) - ID evento_calendario in modifica, da escludere dal check
 */

require_once '../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isPost()) {
        throw new Exception('Solo POST consentito');
    }
    
    // Parametri richiesti
    $data_recupero = post('data_recupero');
    $ora_inizio = post('ora_inizio');
    $ora_fine = post('ora_fine');
    $aula_id = post('aula_id');
    $recupero_id = post('recupero_id'); // Opzionale - per escludere nella modifica
    $evento_id = post('evento_id'); // Opzionale - ID evento_calendario per cercare recupero_id
    
    if (!$data_recupero || !$ora_inizio || !$ora_fine || !$aula_id) {
        throw new Exception('Parametri mancanti');
    }
    
    // Se evento_id fornito, cerca il recupero_id corrispondente
    if ($evento_id && !$recupero_id) {
        $db_conn = Database::getInstance()->getConnection();
        $stmt = $db_conn->prepare("
            SELECT r.id 
            FROM recuperi r 
            WHERE r.data_recupero = ? 
            AND r.ora_inizio = ? 
            LIMIT 1
        ");
        $stmt->execute([$data_recupero, $ora_inizio]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $recupero_id = $result['id'];
        }
    }
    
    $db = Database::getInstance();
    $conflitti = [];
    
    // 1. Controlla lezioni regolari nella stessa aula
    // Prima, calcola il giorno della settimana della data del recupero
    $data_recupero_obj = new DateTime($data_recupero);
    $giorno_settimana_recupero = strtolower($data_recupero_obj->format('l')); // lunedi, martedi, etc.
    
    // Mappa i giorni inglesi a italiani per il confronto
    $giorni_map = [
        'monday' => 'lunedì',
        'tuesday' => 'martedì',
        'wednesday' => 'mercoledì',
        'thursday' => 'giovedì',
        'friday' => 'venerdì',
        'saturday' => 'sabato',
        'sunday' => 'domenica'
    ];
    $giorno_italiano = $giorni_map[$giorno_settimana_recupero] ?? $giorno_settimana_recupero;
    
    $lezioni = $db->query("
        SELECT 
            'lezione' as tipo,
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            al.cognome || ' ' || al.nome as socio,
            d.cognome || ' ' || d.nome as docente,
            m.nome as materia
        FROM lezioni l
        LEFT JOIN soci al ON l.socio_id = al.id
        LEFT JOIN docenti d ON l.docente_id = d.id
        LEFT JOIN materie m ON l.materia_id = m.id
        WHERE l.aula_id = ?
        AND l.attiva = 1
        AND LOWER(l.giorno_settimana) = ?
        AND (
            (l.ora_inizio < ? AND l.ora_fine > ?) OR
            (l.ora_inizio >= ? AND l.ora_inizio < ?) OR
            (l.ora_fine > ? AND l.ora_fine <= ?)
        )
    ", [$aula_id, $giorno_italiano, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine]);
    
    if ($lezioni) {
        foreach ($lezioni as $lezione) {
            $conflitti[] = [
                'tipo' => 'Lezione regolare',
                'orario' => "{$lezione['ora_inizio']} - {$lezione['ora_fine']}",
                'dettagli' => "{$lezione['docente']} ({$lezione['materia']})",
                'socio' => $lezione['socio'] ?? 'N/A'
            ];
        }
    }
    
    // 2. Controlla recuperi esistenti nella stessa aula
    // Se recupero_id è fornito, lo esclude (per modifica)
    $where_escludi = $recupero_id ? "AND r.id != ?" : "";
    $params_recuperi = $recupero_id 
        ? [$aula_id, $data_recupero, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine, $recupero_id]
        : [$aula_id, $data_recupero, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine];
    
    $recuperi = $db->query("
        SELECT 
            'recupero' as tipo,
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            al.cognome || ' ' || al.nome as socio,
            d.cognome || ' ' || d.nome as docente,
            m.nome as materia
        FROM recuperi r
        LEFT JOIN soci al ON r.socio_id = al.id
        LEFT JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        WHERE r.aula_id = ?
        AND r.data_recupero = ?
        AND r.annullato = 0
        AND (
            (r.ora_inizio < ? AND r.ora_fine > ?) OR
            (r.ora_inizio >= ? AND r.ora_inizio < ?) OR
            (r.ora_fine > ? AND r.ora_fine <= ?)
        )
        $where_escludi
    ", $params_recuperi);
    
    if ($recuperi) {
        foreach ($recuperi as $recupero) {
            $conflitti[] = [
                'tipo' => 'Recupero esistente',
                'orario' => "{$recupero['ora_inizio']} - {$recupero['ora_fine']}",
                'dettagli' => "{$recupero['docente']} ({$recupero['materia']})",
                'socio' => $recupero['socio'] ?? 'N/A'
            ];
        }
    }
    
    // 3. Controlla eventi calendario nella stessa aula
    // Se recupero_id è fornito, lo esclude (per modifica)
    $params_eventi = $recupero_id
        ? [$aula_id, $data_recupero, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine]
        : [$aula_id, $data_recupero, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine];
    
    $eventi = $db->query("
        SELECT 
            'evento' as tipo,
            e.id,
            e.data_evento,
            e.ora_inizio,
            e.ora_fine,
            e.titolo,
            al.cognome || ' ' || al.nome as socio,
            d.cognome || ' ' || d.nome as docente,
            t.nome as tipo_evento
        FROM eventi_calendario e
        LEFT JOIN soci al ON e.socio_id = al.id
        LEFT JOIN docenti d ON e.docente_id = d.id
        LEFT JOIN tipologie_evento t ON e.tipologia_id = t.id
        WHERE e.aula_id = ?
        AND e.data_evento = ?
        AND e.attivo = 1
        AND (
            (e.ora_inizio < ? AND e.ora_fine > ?) OR
            (e.ora_inizio >= ? AND e.ora_inizio < ?) OR
            (e.ora_fine > ? AND e.ora_fine <= ?)
        )
    ", $params_eventi);
    
    if ($eventi) {
        foreach ($eventi as $evento) {
            $conflitti[] = [
                'tipo' => 'Evento calendario',
                'orario' => "{$evento['ora_inizio']} - {$evento['ora_fine']}",
                'dettagli' => "{$evento['titolo']} ({$evento['tipo_evento']})",
                'socio' => $evento['socio'] ?? 'N/A'
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'ha_conflitti' => count($conflitti) > 0,
        'conflitti' => $conflitti
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

