<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'create':
            $postData = $data['data'];
            
            // Ottieni ID tipologia LEZ_PROVA
            $stmt = $db->prepare("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_PROVA'");
            $stmt->execute();
            $tipologiaId = $stmt->fetchColumn();
            
            if (!$tipologiaId) {
                throw new Exception('Tipologia LEZ_PROVA non trovata');
            }
            
            // Calcola giorno settimana dalla data
            $dataObj = new DateTime($postData['data_lezione']);
            $giornoNumero = $dataObj->format('N'); // 1=lunedì, 7=domenica
            $mappaGiorni = [
                1 => 'lunedi', 2 => 'martedi', 3 => 'mercoledi',
                4 => 'giovedi', 5 => 'venerdi', 6 => 'sabato', 7 => 'domenica'
            ];
            $giornoSettimana = $mappaGiorni[$giornoNumero];
            
            // 1. Controlla sovrapposizioni con EVENTI in eventi_calendario
            $checkEventi = $db->prepare("
                SELECT e.id, e.titolo, e.ora_inizio, e.ora_fine
                FROM eventi_calendario e
                WHERE e.data_evento = ?
                AND e.aula_id = ?
                AND e.attivo = 1
                AND (
                    (e.ora_inizio < ? AND e.ora_fine > ?) OR
                    (e.ora_inizio >= ? AND e.ora_inizio < ?)
                )
            ");
            $checkEventi->execute([
                $postData['data_lezione'],
                $postData['aula_id'],
                $postData['ora_fine'],
                $postData['ora_inizio'],
                $postData['ora_inizio'],
                $postData['ora_fine']
            ]);
            $conflittoEvento = $checkEventi->fetch(PDO::FETCH_ASSOC);
            
            if ($conflittoEvento) {
                throw new Exception("Conflitto orario: {$conflittoEvento['titolo']} già presente dalle {$conflittoEvento['ora_inizio']} alle {$conflittoEvento['ora_fine']}");
            }
            
            // 2. Controlla sovrapposizioni con LEZIONI RICORRENTI in lezioni
            // Verifica che la lezione non sia annullata per quella data specifica
            $checkLezioni = $db->prepare("
                SELECT 
                    l.id,
                    CONCAT(a.cognome, ' ', a.nome) as socio,
                    m.nome as materia,
                    l.ora_inizio,
                    l.ora_fine,
                    CASE 
                        WHEN ass.id IS NOT NULL THEN 1
                        ELSE 0
                    END as ha_assenza
                FROM lezioni l
                INNER JOIN soci a ON l.socio_id = a.id
                INNER JOIN materie m ON l.materia_id = m.id
                LEFT JOIN assenze ass ON ass.lezione_id = l.id 
                    AND ass.data_assenza = ?
                WHERE l.giorno_settimana = ?
                AND l.aula_id = ?
                AND l.attiva = 1
                AND (
                    (l.ora_inizio < ? AND l.ora_fine > ?) OR
                    (l.ora_inizio >= ? AND l.ora_inizio < ?)
                )
            ");
            $checkLezioni->execute([
                $postData['data_lezione'],
                $giornoSettimana,
                $postData['aula_id'],
                $postData['ora_fine'],
                $postData['ora_inizio'],
                $postData['ora_inizio'],
                $postData['ora_fine']
            ]);
            $conflittoLezione = $checkLezioni->fetch(PDO::FETCH_ASSOC);
            
            // Se c'è una lezione ricorrente E NON ha assenza, è un conflitto
            if ($conflittoLezione && $conflittoLezione['ha_assenza'] == 0) {
                throw new Exception("Conflitto orario: lezione di {$conflittoLezione['socio']} ({$conflittoLezione['materia']}) già presente dalle {$conflittoLezione['ora_inizio']} alle {$conflittoLezione['ora_fine']}");
            }
            
            // Inserisci evento lezione di prova
            $titolo = 'Lezione di Prova - ' . $postData['cognome_socio'] . ' ' . $postData['nome_socio'];
            $descrizione = 'Email: ' . ($postData['email_socio'] ?? 'N/D') . ' | Tel: ' . ($postData['telefono_socio'] ?? 'N/D');
            
            $stmt = $db->prepare("
                INSERT INTO eventi_calendario (
                    tipologia_id, data_evento, ora_inizio, ora_fine,
                    materia_id, docente_id, aula_id, 
                    titolo, descrizione, confermato, attivo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
            ");
            
            $stmt->execute([
                $tipologiaId,
                $postData['data_lezione'],
                $postData['ora_inizio'],
                $postData['ora_fine'],
                $postData['materia_id'],
                $postData['docente_id'],
                $postData['aula_id'],
                $titolo,
                $descrizione
            ]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
