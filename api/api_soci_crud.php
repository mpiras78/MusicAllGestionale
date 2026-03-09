<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$auth->requireLogin();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$action = $data['action'] ?? '';

$controller = new SociController();

try {
    switch ($action) {
        case 'create':
            $postData = $data['data'];
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare(
                "INSERT INTO soci (cognome, nome, email, telefono, cellulare, data_nascita, luogo_nascita,
                    codice_fiscale, indirizzo, cap, citta, provincia, nazione,
                    note_anagrafiche, tipo_socio, data_inizio, stato, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))"
            );

            $stmt->execute([
                $postData['cognome'] ?? null,
                $postData['nome'] ?? null,
                $postData['email'] ?? null,
                $postData['telefono'] ?? null,
                $postData['cellulare'] ?? null,
                $postData['data_nascita'] ?? null,
                $postData['luogo_nascita'] ?? null,
                $postData['codice_fiscale'] ?? null,
                $postData['indirizzo'] ?? null,
                $postData['cap'] ?? null,
                $postData['citta'] ?? null,
                $postData['provincia'] ?? null,
                $postData['nazione'] ?? null,
                $postData['note_anagrafiche'] ?? null,
                $postData['tipo_socio'] ?? 'socio',
                $postData['data_inizio'] ?? date('Y-m-d'),
                $postData['stato'] ?? 'attivo'
            ]);

            $id = $db->lastInsertId();
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'delete':
            $id = $data['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID socio mancante');
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE soci SET attivo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
