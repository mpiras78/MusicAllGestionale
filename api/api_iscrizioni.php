<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? $_GET['action'] ?? '';

$controller = new IscrizioniController();
$lezioniCtrl = new LezioniController();

try {
    switch ($action) {
        case 'get':
            $id = $_GET['id'] ?? 0;
            $db = Database::getInstance()->getConnection();
            
            // Recupera iscrizione con info socio, materia, docente, tipo corso
            $stmt = $db->prepare("
                SELECT i.*,
                    (a.cognome || ' ' || a.nome) as socio,
                    m.nome as materia,
                    (d.cognome || ' ' || d.nome) as docente,
                    tcc.nome as tipo_corso
                FROM iscrizioni i
                LEFT JOIN soci a ON i.socio_id = a.id
                LEFT JOIN materie m ON i.materia_id = m.id
                LEFT JOIN docenti d ON i.docente_id = d.id
                LEFT JOIN tipi_corso_config tcc ON i.tipo_corso_config_id = tcc.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $iscrizione = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$iscrizione) {
                echo json_encode(['success' => false, 'message' => 'Iscrizione non trovata']);
                break;
            }
            
            if ($iscrizione) {
                // Recupera slot settimanale e aula
                $stmt = $db->prepare("
                    SELECT l.giorno_settimana, l.ora_inizio, l.aula_id, au.nome as aula
                    FROM lezioni l
                    LEFT JOIN aule au ON l.aula_id = au.id
                    WHERE l.iscrizione_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$id]);
                $slot = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($slot) {
                    $iscrizione = array_merge($iscrizione, $slot);
                }
            }
            
            echo json_encode(['success' => true, 'data' => $iscrizione]);
            break;
            
        case 'create':
            $postData = $data['data'];
            
            // 1. Crea iscrizione
            $iscrizioneId = $controller->creaIscrizione([
                'socio_id' => $postData['socio_id'],
                'tipo_corso_config_id' => $postData['tipo_corso_config_id'],
                'materia_id' => $postData['materia_id'],
                'docente_id' => $postData['docente_id'],
                'anno_scolastico' => $postData['anno_scolastico'],
                'data_inizio' => $postData['data_inizio'],
                'data_fine' => $postData['data_fine'] ?? null,
                'quota_iscrizione' => $postData['quota_iscrizione'] ?? 30,
                'sconto_fratelli' => $postData['sconto_fratelli'] ?? 0,
                'stato' => $postData['stato'] ?? 'attiva',
                'note' => $postData['note'] ?? null
            ]);
            
            if (!$iscrizioneId) {
                throw new Exception('Errore nella creazione iscrizione');
            }
            
            // 2. Crea slot settimanale (template lezione)
            $db = Database::getInstance()->getConnection();
            
            // Calcola ora fine in base alla durata del corso
            $stmt = $db->prepare("SELECT durata_lezione FROM tipi_corso_config WHERE id = ?");
            $stmt->execute([$postData['tipo_corso_config_id']]);
            $durata = $stmt->fetchColumn();
            
            $ora_inizio = $postData['ora_inizio'];
            $ora_fine = date('H:i', strtotime($ora_inizio) + ($durata * 60));
            
            // Converti numero giorno in stringa
            $giorni = ['', 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
            $giorno_str = $giorni[$postData['giorno_settimana']] ?? 'lunedi';
            
            // Controlla sovrapposizioni
            $checkStmt = $db->prepare("
                SELECT l.id, 
                       (a.cognome || ' ' || a.nome) as socio, 
                       m.nome as materia,
                       (d.cognome || ' ' || d.nome) as docente,
                       l.ora_inizio,
                       l.ora_fine,
                       au.nome as aula
                FROM lezioni l
                JOIN soci a ON l.socio_id = a.id
                JOIN materie m ON l.materia_id = m.id
                JOIN docenti d ON l.docente_id = d.id
                LEFT JOIN aule au ON l.aula_id = au.id
                WHERE l.giorno_settimana = ?
                AND l.aula_id = ?
                AND l.attiva = 1
                AND (
                    (l.ora_inizio < ? AND l.ora_fine > ?) OR
                    (l.ora_inizio >= ? AND l.ora_inizio < ?)
                )
            ");
            $checkStmt->execute([$giorno_str, $postData['aula_id'], $ora_fine, $ora_inizio, $ora_inizio, $ora_fine]);
            $conflitto = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($conflitto) {
                // Trova sale alternative
                $altStmt = $db->prepare("
                    SELECT au2.id, au2.nome
                    FROM aule au2
                    WHERE au2.id NOT IN (
                        SELECT DISTINCT l2.aula_id
                        FROM lezioni l2
                        WHERE l2.giorno_settimana = ?
                        AND l2.attiva = 1
                        AND l2.aula_id IS NOT NULL
                        AND (
                            (l2.ora_inizio < ? AND l2.ora_fine > ?) OR
                            (l2.ora_inizio >= ? AND l2.ora_inizio < ?)
                        )
                    )
                    ORDER BY a.ordine_visualizzazione
                ");
                $altStmt->execute([$giorno_str, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine]);
                $alternative = $altStmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => false,
                    'conflict' => $conflitto,
                    'alternatives' => $alternative
                ]);
                exit;
            }
            
            $stmt = $db->prepare("INSERT INTO lezioni 
                (iscrizione_id, socio_id, docente_id, materia_id, aula_id, 
                 giorno_settimana, ora_inizio, ora_fine, attiva) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            
            $stmt->execute([
                $iscrizioneId,
                $postData['socio_id'],
                $postData['docente_id'],
                $postData['materia_id'],
                $postData['aula_id'] ?? null,
                $giorno_str,
                $ora_inizio,
                $ora_fine
            ]);
            
            echo json_encode(['success' => true, 'id' => $iscrizioneId]);
            break;
            
        case 'update':
            $id = $data['id'];
            $postData = $data['data'];
            
            $result = $controller->aggiornaIscrizione($id, [
                'tipo_corso_config_id' => $postData['tipo_corso_config_id'],
                'materia_id' => $postData['materia_id'],
                'docente_id' => $postData['docente_id'],
                'anno_scolastico' => $postData['anno_scolastico'],
                'data_inizio' => $postData['data_inizio'],
                'data_fine' => $postData['data_fine'] ?? null,
                'quota_iscrizione' => $postData['quota_iscrizione'],
                'sconto_fratelli' => $postData['sconto_fratelli'],
                'stato' => $postData['stato'],
                'note' => $postData['note'] ?? null
            ]);
            
            // Aggiorna anche slot settimanale
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT durata_lezione FROM tipi_corso_config WHERE id = ?");
            $stmt->execute([$postData['tipo_corso_config_id']]);
            $durata = $stmt->fetchColumn();
            
            $ora_inizio = $postData['ora_inizio'];
            $ora_fine = date('H:i', strtotime($ora_inizio) + ($durata * 60));
            
            // Converti numero giorno in stringa
            $giorni = ['', 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
            $giorno_str = $giorni[$postData['giorno_settimana']] ?? 'lunedi';
            
            $stmt = $db->prepare("UPDATE lezioni SET 
                docente_id = ?, materia_id = ?, aula_id = ?,
                giorno_settimana = ?, ora_inizio = ?, ora_fine = ?
                WHERE iscrizione_id = ?");
            
            $stmt->execute([
                $postData['docente_id'],
                $postData['materia_id'],
                $postData['aula_id'] ?? null,
                $giorno_str,
                $ora_inizio,
                $ora_fine,
                $id
            ]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
