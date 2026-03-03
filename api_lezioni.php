<?php
require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

// Leggi input JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($input['action'] ?? null);

try {
    $db = Database::getInstance();
    
    switch ($action) {
        case 'get':
            // Ottieni singola lezione
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID lezione mancante');
            
            $lezione = $db->queryOne("
                SELECT l.*, 
                       al.cognome || ' ' || al.nome as allievo_nome,
                       d.cognome || ' ' || d.nome as docente_nome,
                       m.nome as materia_nome,
                       a.nome as aula_nome
                FROM lezioni l
                JOIN allievi al ON l.allievo_id = al.id
                JOIN docenti d ON l.docente_id = d.id
                JOIN materie m ON l.materia_id = m.id
                JOIN aule a ON l.aula_id = a.id
                WHERE l.id = ?
            ", [$id]);
            
            if (!$lezione) throw new Exception('Lezione non trovata');
            
            echo json_encode(['success' => true, 'data' => $lezione]);
            break;
            
        case 'create':
            // Crea nuova lezione
            if (!$input) {
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            // Validazione
            $required = ['allievo_id', 'materia_id', 'docente_id', 'aula_id', 'giorno_settimana', 'ora_inizio', 'ora_fine'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Campo obbligatorio mancante: $field");
                }
            }
            
            // Verifica conflitti aula
            $conflitto = verificaConflittoAula(
                $input['aula_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                null
            );
            
            if ($conflitto) {
                throw new Exception("Conflitto di orario: l'aula è già occupata da {$conflitto['allievo']} ({$conflitto['ora_inizio']}-{$conflitto['ora_fine']})");
            }
            
            // Verifica conflitti allievo
            $conflittoAllievo = verificaConflittoAllievo(
                $input['allievo_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                null
            );
            
            if ($conflittoAllievo) {
                throw new Exception("Conflitto: l'allievo ha già una lezione in questo orario");
            }
            
            // Verifica conflitti docente
            $conflittoDocente = verificaConflittoDocente(
                $input['docente_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                null
            );
            
            if ($conflittoDocente) {
                throw new Exception("Conflitto: il docente ha già una lezione in questo orario");
            }
            
            // Inserisci lezione
            $id = $db->insert("
                INSERT INTO lezioni (
                    allievo_id, materia_id, docente_id, aula_id,
                    giorno_settimana, ora_inizio, ora_fine, note, attiva
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ", [
                $input['allievo_id'],
                $input['materia_id'],
                $input['docente_id'],
                $input['aula_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                $input['note'] ?? null
            ]);
            
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Lezione creata con successo']);
            break;
            
        case 'update':
            // Aggiorna lezione
            if (!$input) {
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            if (empty($input['id'])) throw new Exception('ID lezione mancante');
            
            // Validazione
            $required = ['allievo_id', 'materia_id', 'docente_id', 'aula_id', 'giorno_settimana', 'ora_inizio', 'ora_fine'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Campo obbligatorio mancante: $field");
                }
            }
            
            // Verifica conflitti (escludi lezione corrente)
            $conflitto = verificaConflittoAula(
                $input['aula_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                $input['id']
            );
            
            if ($conflitto) {
                throw new Exception("Conflitto di orario: l'aula è già occupata da {$conflitto['allievo']} ({$conflitto['ora_inizio']}-{$conflitto['ora_fine']})");
            }
            
            $conflittoAllievo = verificaConflittoAllievo(
                $input['allievo_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                $input['id']
            );
            
            if ($conflittoAllievo) {
                throw new Exception("Conflitto: l'allievo ha già una lezione in questo orario");
            }
            
            $conflittoDocente = verificaConflittoDocente(
                $input['docente_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                $input['id']
            );
            
            if ($conflittoDocente) {
                throw new Exception("Conflitto: il docente ha già una lezione in questo orario");
            }
            
            // Aggiorna lezione
            $db->execute("
                UPDATE lezioni SET
                    allievo_id = ?,
                    materia_id = ?,
                    docente_id = ?,
                    aula_id = ?,
                    giorno_settimana = ?,
                    ora_inizio = ?,
                    ora_fine = ?,
                    note = ?
                WHERE id = ?
            ", [
                $input['allievo_id'],
                $input['materia_id'],
                $input['docente_id'],
                $input['aula_id'],
                $input['giorno_settimana'],
                $input['ora_inizio'],
                $input['ora_fine'],
                $input['note'] ?? null,
                $input['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Lezione aggiornata con successo']);
            break;
            
        case 'delete':
            // Elimina lezione
            if (!$input) {
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            if (empty($input['id'])) throw new Exception('ID lezione mancante');
            
            // Disattiva invece di eliminare
            $db->execute("UPDATE lezioni SET attiva = 0 WHERE id = ?", [$input['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Lezione eliminata con successo']);
            break;
            
        default:
            throw new Exception('Azione non valida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Verifica conflitto di orario per aula
 */
function verificaConflittoAula($aula_id, $giorno, $ora_inizio, $ora_fine, $escludi_id = null) {
    $db = Database::getInstance();
    
    $where_escludi = $escludi_id ? "AND l.id != ?" : "";
    $params = $escludi_id ? [$aula_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine, $escludi_id] 
                          : [$aula_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine];
    
    $conflitto = $db->queryOne("
        SELECT l.*, al.cognome || ' ' || al.nome as allievo
        FROM lezioni l
        JOIN allievi al ON l.allievo_id = al.id
        WHERE l.aula_id = ?
        AND l.giorno_settimana = ?
        AND l.attiva = 1
        AND (
            (l.ora_inizio < ? AND l.ora_fine > ?)
            OR (l.ora_inizio >= ? AND l.ora_inizio < ?)
        )
        $where_escludi
        LIMIT 1
    ", $params);
    
    return $conflitto;
}

/**
 * Verifica conflitto di orario per allievo
 */
function verificaConflittoAllievo($allievo_id, $giorno, $ora_inizio, $ora_fine, $escludi_id = null) {
    $db = Database::getInstance();
    
    $where_escludi = $escludi_id ? "AND id != ?" : "";
    $params = $escludi_id ? [$allievo_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine, $escludi_id] 
                          : [$allievo_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine];
    
    $conflitto = $db->queryOne("
        SELECT id
        FROM lezioni
        WHERE allievo_id = ?
        AND giorno_settimana = ?
        AND attiva = 1
        AND (
            (ora_inizio < ? AND ora_fine > ?)
            OR (ora_inizio >= ? AND ora_inizio < ?)
        )
        $where_escludi
        LIMIT 1
    ", $params);
    
    return $conflitto;
}

/**
 * Verifica conflitto di orario per docente
 */
function verificaConflittoDocente($docente_id, $giorno, $ora_inizio, $ora_fine, $escludi_id = null) {
    $db = Database::getInstance();
    
    $where_escludi = $escludi_id ? "AND id != ?" : "";
    $params = $escludi_id ? [$docente_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine, $escludi_id] 
                          : [$docente_id, $giorno, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine];
    
    $conflitto = $db->queryOne("
        SELECT id
        FROM lezioni
        WHERE docente_id = ?
        AND giorno_settimana = ?
        AND attiva = 1
        AND (
            (ora_inizio < ? AND ora_fine > ?)
            OR (ora_inizio >= ? AND ora_inizio < ?)
        )
        $where_escludi
        LIMIT 1
    ", $params);
    
    return $conflitto;
}
