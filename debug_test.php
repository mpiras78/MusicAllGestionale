<?php
/**
 * Debug Minimalista - Testa step by step
 */

echo "Step 1: Start\n";

try {
    require_once 'includes/bootstrap.php';
    echo "✓ Bootstrap loaded\n";
} catch (Exception $e) {
    echo "✗ Bootstrap error: " . $e->getMessage() . "\n";
    die();
}

try {
    if (!isset($_SESSION['user_id'])) {
        echo "✗ Not logged in\n";
        die();
    }
    echo "✓ User logged in\n";
} catch (Exception $e) {
    echo "✗ Session error: " . $e->getMessage() . "\n";
    die();
}

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connected\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    die();
}

$data = $_GET['data'] ?? date('Y-m-d');
echo "Data: $data\n";

try {
    // Test query
    $result = $db->query("SELECT COUNT(*) as count FROM recuperi WHERE annullato = 0");
    $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Recuperi totali: $count\n";
} catch (Exception $e) {
    echo "✗ Query error: " . $e->getMessage() . "\n";
    die();
}

echo "\nAll systems operational!\n";
