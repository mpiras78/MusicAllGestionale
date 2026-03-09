<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

try {
    $db = Database::getInstance()->getConnection();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $anno = $_GET['anno'] ?? date('Y') . '-' . (date('Y') + 1);
            $page = (int)($_GET['page'] ?? 1);
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            $stmt = $db->prepare("
                SELECT 
                    i.id,
                    CONCAT(a.cognome, ' ', a.nome) as socio,
                    tcc.nome as tipo_corso,
                    tcc.nome as materia,
                    CONCAT(d.cognome, ' ', d.nome) as docente,
                    i.data_inizio,
                    i.stato,
                    tcc.costo_mensile as importo_totale,
                    0 as importo_pagato
                FROM iscrizioni i
                INNER JOIN soci a ON i.socio_id = a.id
                INNER JOIN tipi_corso_config tcc ON i.tipo_corso_config_id = tcc.id
                INNER JOIN docenti d ON i.docente_id = d.id
                WHERE i.anno_accademico = ?
                ORDER BY a.cognome, a.nome
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$anno, $limit, $offset]);
            $iscrizioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Conta totale
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM iscrizioni WHERE anno_accademico = ?");
            $countStmt->execute([$anno]);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true, 
                'data' => $iscrizioni,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
