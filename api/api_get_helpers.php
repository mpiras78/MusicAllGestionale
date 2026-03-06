<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($type) {
        case 'allievi':
            $stmt = $db->query("
                SELECT id, cognome, nome
                FROM allievi
                WHERE attivo = 1
                ORDER BY cognome, nome
            ");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'docenti':
            $stmt = $db->query("
                SELECT id, cognome, nome
                FROM docenti
                WHERE attivo = 1
                ORDER BY cognome, nome
            ");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'allievi_con_lezioni':
            $stmt = $db->query("
                SELECT 
                    a.id,
                    COUNT(DISTINCT l.id) as num_lezioni,
                    GROUP_CONCAT(DISTINCT m.nome) as materie,
                    GROUP_CONCAT(DISTINCT l.giorno_settimana) as giorni
                FROM allievi a
                INNER JOIN lezioni l ON a.id = l.allievo_id AND l.attiva = 1 AND l.iscrizione_id IS NOT NULL
                INNER JOIN iscrizioni i ON l.iscrizione_id = i.id AND i.stato = 'attiva'
                LEFT JOIN materie m ON l.materia_id = m.id
                WHERE a.attivo = 1
                GROUP BY a.id
            ");
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Tipo non valido']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
