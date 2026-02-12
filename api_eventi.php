<?php
/**
 * API Eventi Calendario
 * Gestione CRUD per eventi_calendario
 * 
 * Endpoints:
 * - GET    ?action=list&date=YYYY-MM-DD
 * - POST   ?action=create
 * - PUT    ?action=update&id=X
 * - DELETE ?action=delete&id=X
 */

require_once 'includes/bootstrap.php';

use MusicAll\Models\EventoCalendario;
use MusicAll\Models\TipologiaEvento;

header('Content-Type: application/json');

// Autenticazione richiesta
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            // GET eventi per data
            $date = $_GET['date'] ?? date('Y-m-d');
            $aula_id = $_GET['aula_id'] ?? null;
            $docente_id = $_GET['docente_id'] ?? null;
            $allievo_id = $_GET['allievo_id'] ?? null;
            
            // Query base
            $query = EventoCalendario::with(['tipologia', 'aula', 'docente', 'allievo', 'materia'])
                ->attivi()
                ->perData($date);
            
            // Filtri opzionali
            if ($aula_id) {
                $query->perAula($aula_id);
            }
            if ($docente_id) {
                $query->perDocente($docente_id);
            }
            if ($allievo_id) {
                $query->perAllievo($allievo_id);
            }
            
            $eventi = $query->orderBy('ora_inizio')->get();
            
            // Trasforma per compatibilità frontend
            $eventiArray = $eventi->map(function($evento) {
                return [
                    'id' => $evento->id,
                    'tipologia_id' => $evento->tipologia_id,
                    'tipologia_nome' => $evento->tipologia->nome ?? '',
                    'tipologia_categoria' => $evento->tipologia->categoria ?? '',
                    'colore_bg' => $evento->tipologia->colore_bg ?? '#ffffff',
                    'colore_border' => $evento->tipologia->colore_border ?? '#000000',
                    'icona' => $evento->tipologia->icona ?? '',
                    'ricorrente' => $evento->ricorrente,
                    'giorno_settimana' => $evento->giorno_settimana,
                    'data_evento' => $evento->data_evento ? $evento->data_evento->format('Y-m-d') : null,
                    'ora_inizio' => $evento->ora_inizio,
                    'ora_fine' => $evento->ora_fine,
                    'aula_id' => $evento->aula_id,
                    'aula_nome' => $evento->aula->nome ?? '',
                    'docente_id' => $evento->docente_id,
                    'docente_nome' => $evento->docente ? ($evento->docente->cognome . ' ' . $evento->docente->nome) : '',
                    'allievo_id' => $evento->allievo_id,
                    'partecipante_nome' => $evento->partecipante,
                    'materia_id' => $evento->materia_id,
                    'materia_nome' => $evento->materia->nome ?? '',
                    'titolo' => $evento->titolo,
                    'descrizione' => $evento->descrizione,
                    'note' => $evento->note,
                    'confermato' => $evento->confermato,
                ];
            });
            
            echo json_encode([
                'success' => true,
                'date' => $date,
                'count' => $eventi->count(),
                'data' => $eventiArray
            ]);
            break;
            
        case 'create':
            // POST nuovo evento
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione base
            $required = ['tipologia_id', 'aula_id', 'ora_inizio', 'ora_fine'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Campo obbligatorio mancante: $field");
                }
            }
            
            // Validazione ricorrente vs data_evento
            if ($data['ricorrente'] ?? 0) {
                if (!isset($data['giorno_settimana'])) {
                    throw new Exception('Evento ricorrente richiede giorno_settimana');
                }
            } else {
                if (!isset($data['data_evento'])) {
                    throw new Exception('Evento singolo richiede data_evento');
                }
            }
            
            // Crea evento
            $data['created_by'] = $_SESSION['user_id'];
            $data['attivo'] = 1;
            $data['confermato'] = 1;
            
            $evento = EventoCalendario::create($data);
            
            // Log attività
            logActivity('create_evento', 'evento_calendario', $evento->id, 
                       "Creato evento: {$data['ora_inizio']}-{$data['ora_fine']}");
            
            echo json_encode([
                'success' => true,
                'id' => $evento->id,
                'message' => 'Evento creato con successo'
            ]);
            break;
            
        case 'update':
            // PUT modifica evento
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            if (!$id) throw new Exception('ID evento mancante');
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) $data = $_POST; // Fallback per form-data
            
            $evento = EventoCalendario::find($id);
            if (!$evento) throw new Exception('Evento non trovato');
            
            // Verifica permessi (solo admin o creatore)
            if ($_SESSION['role'] !== 'admin' && $evento->created_by != $_SESSION['user_id']) {
                throw new Exception('Non autorizzato a modificare questo evento');
            }
            
            $data['updated_by'] = $_SESSION['user_id'];
            $evento->update($data);
            
            // Log attività
            logActivity('update_evento', 'evento_calendario', $id, 'Modificato evento');
            
            echo json_encode([
                'success' => true,
                'message' => 'Evento aggiornato con successo'
            ]);
            break;
            
        case 'delete':
            // DELETE evento (soft delete)
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            if (!$id) throw new Exception('ID evento mancante');
            
            $evento = EventoCalendario::find($id);
            if (!$evento) throw new Exception('Evento non trovato');
            
            // Verifica permessi
            if ($_SESSION['role'] !== 'admin' && $evento->created_by != $_SESSION['user_id']) {
                throw new Exception('Non autorizzato a eliminare questo evento');
            }
            
            // Soft delete
            $evento->update([
                'attivo' => false,
                'updated_by' => $_SESSION['user_id']
            ]);
            
            // Log attività
            logActivity('delete_evento', 'evento_calendario', $id, 'Eliminato evento');
            
            echo json_encode([
                'success' => true,
                'message' => 'Evento eliminato con successo'
            ]);
            break;
            
        case 'get':
            // GET singolo evento
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID evento mancante');
            
            $evento = EventoCalendario::with(['tipologia', 'aula', 'docente', 'allievo', 'materia'])
                ->find($id);
            
            if (!$evento) throw new Exception('Evento non trovato');
            
            // Trasforma in array per compatibilità
            $eventoArray = [
                'id' => $evento->id,
                'tipologia_id' => $evento->tipologia_id,
                'tipologia_nome' => $evento->tipologia->nome ?? '',
                'tipologia_categoria' => $evento->tipologia->categoria ?? '',
                'colore_bg' => $evento->tipologia->colore_bg ?? '#ffffff',
                'colore_border' => $evento->tipologia->colore_border ?? '#000000',
                'icona' => $evento->tipologia->icona ?? '',
                'ricorrente' => $evento->ricorrente,
                'giorno_settimana' => $evento->giorno_settimana,
                'data_evento' => $evento->data_evento ? $evento->data_evento->format('Y-m-d') : null,
                'data_inizio' => $evento->data_inizio ? $evento->data_inizio->format('Y-m-d') : null,
                'data_fine' => $evento->data_fine ? $evento->data_fine->format('Y-m-d') : null,
                'ora_inizio' => $evento->ora_inizio,
                'ora_fine' => $evento->ora_fine,
                'aula_id' => $evento->aula_id,
                'aula_nome' => $evento->aula->nome ?? '',
                'docente_id' => $evento->docente_id,
                'docente_nome' => $evento->docente ? ($evento->docente->cognome . ' ' . $evento->docente->nome) : '',
                'allievo_id' => $evento->allievo_id,
                'partecipante_nome' => $evento->partecipante,
                'materia_id' => $evento->materia_id,
                'materia_nome' => $evento->materia->nome ?? '',
                'titolo' => $evento->titolo,
                'descrizione' => $evento->descrizione,
                'note' => $evento->note,
                'confermato' => $evento->confermato,
                'attivo' => $evento->attivo,
                'created_by' => $evento->created_by,
                'created_at' => $evento->created_at ? $evento->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $evento->updated_at ? $evento->updated_at->format('Y-m-d H:i:s') : null,
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $eventoArray
            ]);
            break;
            
        default:
            throw new Exception('Azione non valida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Helper per logging attività
 */
function logActivity($action, $entityType, $entityId, $description) {
    try {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $entityType,
            $entityId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        // Log error silently
        error_log("Errore log attività: " . $e->getMessage());
    }
}