<?php
/**
 * API Helpers - Singolo endpoint multiplo
 * GET: ?type=allievi|docenti|materie|aule
 */

require_once 'includes/bootstrap.php';

use MusicAll\Models\Allievo;
use MusicAll\Models\Docente;
use MusicAll\Models\Materia;
use MusicAll\Models\Aula;

header('Content-Type: application/json');

// Autenticazione richiesta
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'allievi':
            $data = Allievo::where('attivo', true)
                ->orderBy('cognome')
                ->orderBy('nome')
                ->get()
                ->map(function($a) {
                    return [
                        'id' => $a->id,
                        'nome' => $a->nome,
                        'cognome' => $a->cognome,
                        'nome_completo' => $a->cognome . ' ' . $a->nome
                    ];
                });
            break;
            
        case 'docenti':
            $data = Docente::where('attivo', true)
                ->orderBy('cognome')
                ->orderBy('nome')
                ->get()
                ->map(function($d) {
                    return [
                        'id' => $d->id,
                        'nome' => $d->nome,
                        'cognome' => $d->cognome,
                        'nome_completo' => $d->cognome . ' ' . $d->nome
                    ];
                });
            break;
            
        case 'materie':
            $data = Materia::where('attiva', true)
                ->orderBy('nome')
                ->get()
                ->map(function($m) {
                    return [
                        'id' => $m->id,
                        'nome' => $m->nome
                    ];
                });
            break;
            
        case 'aule':
            $data = Aula::where('attiva', true)
                ->orderBy('nome')
                ->get()
                ->map(function($a) {
                    return [
                        'id' => $a->id,
                        'nome' => $a->nome
                    ];
                });
            break;
            
        case 'allievi_con_lezioni':
            // Usa AllieviController per ottenere allievi con statistiche lezioni
            $allieviCtrl = new AllieviController();
            $data = $allieviCtrl->getAllieviConLezioni();
            break;
            
        default:
            throw new Exception('Tipo non valido');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}