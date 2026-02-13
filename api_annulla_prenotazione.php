<?php
/**
 * API: Annulla Prenotazione
 * 
 * Annulla una prenotazione (evento) senza generare assenze
 * Diversamente dall'annullamento di una lezione che genera assenza,
 * l'annullamento di una prenotazione semplicemente la disattiva.
 */

require_once 'includes/bootstrap.php';

header('Content-Type: application/json');

// Richiede autenticazione
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autenticato']);
    exit;
}

// Verifica permessi (solo admin, segreteria o il creatore)
if (!$auth->hasRole('admin') && !$auth->hasRole('segreteria')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permessi insufficienti']);
    exit;
}

try {
    // Leggi input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['evento_id'])) {
        throw new Exception('ID evento mancante');
    }
    
    $evento_id = (int)$input['evento_id'];
    $motivo = $input['motivo'] ?? '';
    
    $db = Database::getInstance()->getConnection();
    
    // Verifica che l'evento esista e sia una prenotazione
    $stmt = $db->prepare("
        SELECT e.*, t.codice, t.categoria
        FROM eventi_calendario e
        INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
        WHERE e.id = ?
    ");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        throw new Exception('Evento non trovato');
    }
    
    // Verifica che sia una prenotazione o recupero (non lezione ricorrente)
    if ($evento['categoria'] !== 'prenotazione' && $evento['categoria'] !== 'recupero') {
        throw new Exception('Questo evento non può essere annullato da qui. Usa la funzione "Segna Assenza" per le lezioni ricorrenti.');
    }
    
    // Annulla prenotazione (disattiva)
    $stmt = $db->prepare("
        UPDATE eventi_calendario 
        SET attivo = 0,
            note = CASE 
                WHEN note IS NULL OR note = '' THEN ?
                ELSE note || '\n---\nANNULLATA: ' || ?
            END,
            updated_at = datetime('now', 'localtime')
        WHERE id = ?
    ");
    
    $nota_annullamento = "ANNULLATA il " . date('d/m/Y H:i');
    if ($motivo) {
        $nota_annullamento .= " - Motivo: " . $motivo;
    }
    
    $stmt->execute([
        $nota_annullamento,
        $nota_annullamento,
        $evento_id
    ]);
    
    // Log operazione
    error_log("Prenotazione annullata: ID={$evento_id}, User=" . $auth->getUserId() . ", Motivo={$motivo}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Prenotazione annullata con successo',
        'data' => [
            'evento_id' => $evento_id,
            'tipo' => $evento['codice']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}