<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'calcola_importo':
            // Calcola importo per iscrizione e mese
            $iscrizione_id = $_GET['iscrizione_id'] ?? 0;
            $mese_riferimento = $_GET['mese_riferimento'] ?? date('Y-m');
            
            $calcolo = PagamentiHelper::calcolaImportoIscrizione($iscrizione_id, $mese_riferimento);
            
            echo json_encode([
                'success' => true,
                'data' => $calcolo
            ]);
            break;
            
        case 'preview_anno':
            // Preview tutti i mesi dell'anno scolastico
            $iscrizione_id = $_GET['iscrizione_id'] ?? 0;
            $anno_scolastico = $_GET['anno_scolastico'] ?? '2024/2025';
            
            $preview = PagamentiHelper::previewAnnoScolastico($iscrizione_id, $anno_scolastico);
            
            echo json_encode([
                'success' => true,
                'data' => $preview
            ]);
            break;
            
        case 'conta_lezioni':
            // Conta lezioni per giorno settimana e mese
            $mese_riferimento = $_GET['mese_riferimento'] ?? date('Y-m');
            $giorno_settimana = $_GET['giorno_settimana'] ?? 1;
            
            $festivita = PagamentiHelper::getFestivitaMese($mese_riferimento);
            $info = PagamentiHelper::contaLezioniMese($mese_riferimento, $giorno_settimana, $festivita);
            
            echo json_encode([
                'success' => true,
                'data' => $info
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
