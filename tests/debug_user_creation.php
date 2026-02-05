<?php
/**
 * Debug creazione utente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/controllers/UsersController.php';

echo "=== DEBUG CREAZIONE UTENTE ===\n\n";

$db = Database::getInstance();
$controller = new UsersController($db);

// Verifica struttura tabella users
echo "1. Struttura tabella users:\n";
$columns = $db->query("PRAGMA table_info(users)");
foreach ($columns as $col) {
    echo "   - {$col['name']} ({$col['type']})\n";
}
echo "\n";

// Verifica tabella activation_tokens
echo "2. Tabella activation_tokens esiste?\n";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='activation_tokens'");
if (count($tables) > 0) {
    echo "   ✓ Sì\n";
} else {
    echo "   ✗ NO - Esegui migration!\n";
}
echo "\n";

// Test creazione utente
echo "3. Test creazione utente...\n";
$testData = [
    'username' => 'test_debug_' . time(),
    'password' => 'Test123',
    'email' => 'test@test.it',
    'role' => 'segreteria'
];

echo "   Dati: " . json_encode($testData) . "\n";

try {
    $result = $controller->create($testData);
    echo "   Risultato: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    if ($result['success']) {
        echo "\n✓ UTENTE CREATO CON SUCCESSO!\n";
        
        // Verifica token
        $token = $db->queryOne("SELECT * FROM activation_tokens WHERE user_id = ?", [$result['id']]);
        if ($token) {
            echo "\n✓ TOKEN GENERATO: " . $token['token'] . "\n";
            echo "  Scade il: " . $token['expires_at'] . "\n";
        }
        
        // Cleanup
        $db->execute("DELETE FROM users WHERE id = ?", [$result['id']]);
        echo "\n(Utente test eliminato)\n";
    } else {
        echo "\n✗ ERRORE: " . $result['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}