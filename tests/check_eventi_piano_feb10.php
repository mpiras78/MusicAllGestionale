<?php
// Define BASE_PATH first
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/includes/Database.php';

echo "=== VERIFICA EVENTI 10 FEBBRAIO 2026 - AULA PIANO ===\n\n";

$db = Database::getInstance()->getConnection();

// 1. Trova ID aula Piano
$stmt = $db->query("SELECT id, nome FROM aule WHERE nome LIKE '%PIANO%'");
$aula = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aula) {
    die("❌ Aula Piano non trovata!\n");
}

echo "✓ Aula trovata: {$aula['nome']} (ID: {$aula['id']})\n\n";

// 2. Eventi in quella aula il 10 febbraio
$stmt = $db->prepare("
    SELECT 
        ec.id,
        ec.tipologia_id,
        t.nome as tipologia_nome,
        ec.data_evento,
        ec.ora_inizio,
        ec.ora_fine,
        ec.aula_id,
        a.nome as aula_nome,
        ec.allievo_id,
        ec.confermato
    FROM eventi_calendario ec
    LEFT JOIN tipologie_evento t ON ec.tipologia_id = t.id
    LEFT JOIN aule a ON ec.aula_id = a.id
    WHERE ec.data_evento = '2026-02-10'
    AND ec.aula_id = ?
    ORDER BY ec.ora_inizio
");
$stmt->execute([$aula['id']]);
$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📋 Eventi trovati nel DB: " . count($eventi) . "\n\n";

if (count($eventi) > 0) {
    foreach ($eventi as $evt) {
        echo "ID: {$evt['id']}\n";
        echo "Tipo: {$evt['tipologia_nome']} (ID: {$evt['tipologia_id']})\n";
        echo "Data: {$evt['data_evento']}\n";
        echo "Orario: {$evt['ora_inizio']} - {$evt['ora_fine']}\n";
        echo "Aula: {$evt['aula_nome']} (ID: {$evt['aula_id']})\n";
        echo "Allievo ID: " . ($evt['allievo_id'] ?: 'NULL') . "\n";
        echo "Confermato: " . ($evt['confermato'] ? 'Sì' : 'No') . "\n";
        echo str_repeat('-', 50) . "\n\n";
    }
} else {
    echo "❌ Nessun evento trovato nel DB!\n";
}

// 3. Verifica cosa restituisce EventiController
echo "\n=== VERIFICA EventiController::getEventiPerData() ===\n\n";

require_once BASE_PATH . '/includes/controllers/EventiController.php';
$eventiCtrl = new EventiController();
$eventi_ctrl = $eventiCtrl->getEventiPerData('2026-02-10');

echo "Eventi restituiti da Controller: " . count($eventi_ctrl) . "\n\n";

if (count($eventi_ctrl) > 0) {
    foreach ($eventi_ctrl as $evt) {
        echo "ID: {$evt['id']}\n";
        echo "Tipo: {$evt['tipo']}\n";
        echo "Orario: {$evt['ora_inizio']} - {$evt['ora_fine']}\n";
        echo "Aula ID: {$evt['aula_id']}\n";
        echo "Source Type: {$evt['source_type']}\n";
        echo str_repeat('-', 50) . "\n\n";
    }
} else {
    echo "❌ Nessun evento restituito dal Controller!\n";
}