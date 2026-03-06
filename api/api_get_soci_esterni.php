<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    // Per ora ritorna array vuoto con opzione "Nuovo"
    echo json_encode(['success' => true, 'data' => []]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}