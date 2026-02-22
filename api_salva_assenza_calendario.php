<?php
// Abilita error reporting per debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Non mostrare errori direttamente
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_api_assenza.log');

// Cattura qualsiasi output indesiderato
ob_start();

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

// Pulisci buffer e imposta header JSON
ob_clean();
header('Content-Type: application/json');

// Log richiesta
file_put_contents(__DIR__ . '/debug_assenza.log', date('Y-m-d H:i:s') . " - Richiesta ricevuta\n", FILE_APPEND);

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
    
    // Verifica CSRF token
    if (!CSRFHelper::verifyToken($data['csrf_token'] ?? '')) {
        throw new Exception('Token CSRF non valido');
    }
    
    // Rate limiting
    $rateLimiter = new RateLimiter();
    $rateLimiter->enforce($_SERVER['REMOTE_ADDR'], 'api');
    
    // Valida parametri richiesti
    $lezione_id = $data['lezione_id'] ?? null;
    $data_lezione = $data['data_lezione'] ?? null;
    $causale = $data['causale'] ?? 'allievo';
    $note = $data['note'] ?? '';
    
    if (!$lezione_id || !$data_lezione) {
        throw new Exception('Parametri mancanti');
    }
    
    // Log parametri
    file_put_contents(__DIR__ . '/debug_assenza.log', "Parametri: " . json_encode($data) . "\n", FILE_APPEND);
    
    // Inizializza controller
    file_put_contents(__DIR__ . '/debug_assenza.log', "Inizializzazione controllers...\n", FILE_APPEND);
    
    $assenzeCtrl = new AssenzeController();
    file_put_contents(__DIR__ . '/debug_assenza.log', "AssenzeController OK\n", FILE_APPEND);
    
    $lezioniCtrl = new LezioniController();
    file_put_contents(__DIR__ . '/debug_assenza.log', "LezioniController OK\n", FILE_APPEND);
    
    // Ottieni info lezione
    file_put_contents(__DIR__ . '/debug_assenza.log', "Recupero lezione ID: $lezione_id\n", FILE_APPEND);
    $lezione = $lezioniCtrl->getLezioneById($lezione_id);
    
    if (!$lezione) {
        file_put_contents(__DIR__ . '/debug_assenza.log', "ERRORE: Lezione non trovata\n", FILE_APPEND);
        throw new Exception('Lezione non trovata');
    }
    
    file_put_contents(__DIR__ . '/debug_assenza.log', "Lezione trovata: " . json_encode($lezione) . "\n", FILE_APPEND);
    
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
    
    // Determina se necessita recupero secondo la policy:
    // - Assenze docente: sempre da recuperare
    // - Assenze allievo: prime 3 assenze da recuperare, dalla 4a in poi a discrezione
    $necessita_recupero = 1; // Default: sempre da recuperare
    
    if ($causale === 'allievo') {
        // Conta assenze precedenti dell'allievo per questa lezione nell'anno scolastico corrente
        $contatori = $assenzeCtrl->getContatoriAnnoScolastico($lezione['allievo_id'], $lezione_id);
        $num_assenze_precedenti = $contatori['assenze'];
        
        // Se ha già 3+ assenze, dalla quarta in poi è a discrezione
        if ($num_assenze_precedenti >= 3) {
            $necessita_recupero = 0;
        }
    }
    
    // Crea assenza - usa nomi parametri corretti per AssenzeController
    $assenza_data = [
        'lezione_id' => $lezione_id,
        'data' => $data_lezione,  // AssenzeController si aspetta 'data', non 'data_assenza'
        'causata_da' => $causale,  // AssenzeController si aspetta 'causata_da', non 'causale'
        'necessita_recupero' => $necessita_recupero,
        'note_annullamento' => $note
    ];
    
    file_put_contents(__DIR__ . '/debug_assenza.log', "Dati assenza: " . json_encode($assenza_data) . "\n", FILE_APPEND);
    file_put_contents(__DIR__ . '/debug_assenza.log', "Chiamata creaAssenza...\n", FILE_APPEND);
    
    $assenza_id = $assenzeCtrl->creaAssenza($assenza_data);
    
    file_put_contents(__DIR__ . '/debug_assenza.log', "Assenza creata ID: $assenza_id\n", FILE_APPEND);
    
    if (!$assenza_id) {
        throw new Exception('Errore durante la creazione dell\'assenza');
    }
    
    file_put_contents(__DIR__ . '/debug_assenza.log', "✅ Assenza salvata con successo!\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Assenza registrata con successo',
        'assenza_id' => $assenza_id
    ]);
    
} catch (Exception $e) {
    // Log errore
    file_put_contents(__DIR__ . '/debug_assenza.log', "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Pulisci buffer per rimuovere HTML errori
    ob_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ]);
} catch (Error $e) {
    // Log errore fatale
    file_put_contents(__DIR__ . '/debug_assenza.log', "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Cattura anche errori PHP fatali
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore interno del server',
        'details' => DEBUG_MODE ? $e->getMessage() : null,
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ]);
}

// Invia output e termina
ob_end_flush();
