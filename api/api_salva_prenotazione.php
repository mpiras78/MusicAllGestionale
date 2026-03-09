<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

try {
    $db = Database::getInstance()->getConnection();
    
    $tipologia_id = $_POST['tipologia_id'] ?? '';
    $aula_id = $_POST['aula_id'] ?? '';
    $ora_inizio = $_POST['ora_inizio'] ?? '';
    $data = $_POST['data'] ?? '';
    $durata = (int)($_POST['durata'] ?? 60);
    $note = $_POST['note'] ?? '';
    
    if (!$tipologia_id || !$aula_id || !$ora_inizio || !$data) {
        throw new Exception('Parametri obbligatori mancanti');
    }
    
    // Calcola ora fine
    $ora_fine = date('H:i:s', strtotime($ora_inizio) + ($durata * 60));
    
    // Determina titolo e descrizione in base al tipo
    $titolo = 'Prenotazione';
    $descrizione = '';
    $socio_id = null;
    $docente_id = null;
    
    if ($tipologia_id == 6) { // PREN_SALA_SOCI
        $socio_id = $_POST['socio_id_pren'] ?? $_POST['socio_id_pren'] ?? null;
        if (!$socio_id) throw new Exception('Socio richiesto per prenotazione sala');
        
        $stmt = $db->prepare("SELECT cognome, nome FROM soci WHERE id = ?");
        $stmt->execute([$socio_id]);
        $socio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $titolo = 'Prenotazione Sala - ' . $socio['cognome'] . ' ' . $socio['nome'];
        
    } elseif ($tipologia_id == 7) { // PREN_DOCENTE
        $docente_id = $_POST['docente_id_pren'] ?? null;
        if (!$docente_id) throw new Exception('Docente richiesto per prenotazione docente');
        
        $stmt = $db->prepare("SELECT cognome, nome FROM docenti WHERE id = ?");
        $stmt->execute([$docente_id]);
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $titolo = $_POST['titolo'] ?? ('Prenotazione Docente - ' . $docente['cognome'] . ' ' . $docente['nome']);
        
    } elseif ($tipologia_id == 8) { // PREN_ESTERNO
        $nome = $_POST['nome_esterno'] ?? '';
        $cognome = $_POST['cognome_esterno'] ?? '';
        
        if (!$nome || !$cognome) throw new Exception('Nome e cognome richiesti per prenotazione esterno');
        
        $titolo = 'Prenotazione Esterno - ' . $cognome . ' ' . $nome;
        $descrizione = 'Email: ' . ($_POST['email_esterno'] ?? 'N/D') . ' | Tel: ' . ($_POST['telefono_esterno'] ?? 'N/D');
    }
    
    // Inserisci evento
    $stmt = $db->prepare("
        INSERT INTO eventi_calendario (
            tipologia_id, data_evento, ora_inizio, ora_fine,
            aula_id, socio_id, docente_id,
            titolo, descrizione, note, confermato, attivo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    
    $stmt->execute([
        $tipologia_id,
        $data,
        $ora_inizio,
        $ora_fine,
        $aula_id,
        $socio_id,
        $docente_id,
        $titolo,
        $descrizione,
        $note
    ]);
    
    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}