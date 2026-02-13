<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT id, codice, nome, categoria 
    FROM tipologie_evento 
    WHERE codice LIKE 'PREN_%' 
    ORDER BY id
");

$tipologie = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== TIPOLOGIE PRENOTAZIONE ===\n\n";

foreach ($tipologie as $t) {
    echo "ID: {$t['id']}\n";
    echo "Codice: {$t['codice']}\n";
    echo "Nome: {$t['nome']}\n";
    echo "Categoria: {$t['categoria']}\n";
    echo "---\n";
}