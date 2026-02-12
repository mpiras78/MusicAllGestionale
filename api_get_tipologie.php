<?php
/**
 * API Get Tipologie Eventi
 * Restituisce lista tipologie per select forms
 */

require_once 'includes/bootstrap.php';

use MusicAll\Models\TipologiaEvento;

header('Content-Type: application/json');

// Autenticazione richiesta
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $tipologie = TipologiaEvento::where('attivo', true)
        ->orderBy('categoria')
        ->orderBy('nome')
        ->get();
    
    $result = $tipologie->map(function($tip) {
        return [
            'id' => $tip->id,
            'nome' => $tip->nome,
            'categoria' => $tip->categoria,
            'colore_bg' => $tip->colore_bg,
            'colore_border' => $tip->colore_border,
            'icona' => $tip->icona,
            'descrizione' => $tip->descrizione
        ];
    });
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}