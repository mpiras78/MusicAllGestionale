<?php
// Cattura qualsiasi output indesiderato
ob_start();

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

// Pulisci buffer e imposta header JSON
ob_clean();
header('Content-Type: application/json');

try {
    // Verifica metodo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non permesso');
    }
    
    // Leggi JSON body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Dati non validi');
    }
    
    // Valida parametri richiesti
    $lezione_id = $data['lezione_id'] ?? null;
    $data_lezione = $data['data_lezione'] ?? null;
    $causale = $data['causale'] ?? 'allievo';
    $note = $data['note'] ?? '';
    
    if (!$lezione_id || !$data_lezione) {
        throw new Exception('Parametri mancanti');
    }
    
    // Inizializza controller
    $assenzeCtrl = new AssenzeController();
    $lezioniCtrl = new LezioniController();
    
    // Ottieni info lezione
    $lezione = $lezioniCtrl->getLezioneById($lezione_id);
    
    if (!$lezione) {
        throw new Exception('Lezione non trovata');
    }
    
    // Verifica se assenza già esiste
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM assenze 
        WHERE lezione_id = ? AND data_assenza = ?
    ");
    $stmt->execute([$lezione_id, $data_lezione]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        throw new Exception('Assenza già registrata per questa lezione');
    }
    
    // Crea assenza
    $assenza_data = [
        'allievo_id' => $lezione['allievo_id'],
        'lezione_id' => $lezione_id,
        'data_assenza' => $data_lezione,
        'causale' => $causale,
        'richiede_recupero' => ($causale === 'docente') ? 1 : 0,
        'note' => $note
    ];
    
    $assenza_id = $assenzeCtrl->creaAssenza($assenza_data);
    
    if (!$assenza_id) {
        throw new Exception('Errore durante la creazione dell\'assenza');
    }
    
    // Se causata da docente, crea automaticamente recupero
    if ($causale === 'docente') {
        $recuperiCtrl = new RecuperiController();
        $recuperiCtrl->creaRecuperoDaAssenza($assenza_id);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Assenza registrata con successo',
        'assenza_id' => $assenza_id,
        'recupero_creato' => ($causale === 'docente')
    ]);
    
} catch (Exception $e) {
    // Pulisci buffer per rimuovere HTML errori
    ob_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ]);
} catch (Error $e) {
    // Cattura anche errori PHP fatali
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore interno del server',
        'details' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}

// Invia output e termina
ob_end_flush();
