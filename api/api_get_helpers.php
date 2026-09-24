<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($type) {
        case 'soci':
            $stmt = $db->query("
                SELECT id, cognome, nome
                FROM soci
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
            
        case 'soci_con_lezioni':
            $stmt = $db->query("
                SELECT 
                    s.id,
                    COUNT(DISTINCT l.id) as num_lezioni,
                    GROUP_CONCAT(DISTINCT m.nome) as materie,
                    GROUP_CONCAT(DISTINCT l.giorno_settimana) as giorni
                FROM soci s
                INNER JOIN lezioni l ON s.id = l.socio_id AND l.attiva = 1 AND l.iscrizione_id IS NOT NULL
                INNER JOIN iscrizioni_annuali i ON l.iscrizione_id = i.id AND i.stato = 'attiva'
                LEFT JOIN materie m ON l.materia_id = m.id
                WHERE s.attivo = 1
                GROUP BY s.id
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
