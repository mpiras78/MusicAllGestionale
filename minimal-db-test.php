<?php
/**
 * [TEMPORARY] Minimal database test
 * DELETE AFTER: minimal-db-test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";

try {
    require_once 'config/config.php';
    echo "✓ Config loaded\n";
    echo "DB_PATH: " . DB_PATH . "\n";
    
    $pdo = new PDO('sqlite:' . DB_PATH);
    echo "✓ PDO connected\n\n";
    
    // TEST UPDATE
    if ($_POST['test'] ?? false) {
        $pwd = $_POST['pwd'] ?? 'Test123!@#';
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        
        echo "TEST UPDATE:\n";
        echo "Password: $pwd\n";
        echo "Hash: " . substr($hash, 0, 40) . "...\n\n";
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hash]);
        echo "Rows affected: " . $stmt->rowCount() . "\n\n";
    }
    
    // QUERY ADMIN
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    echo "Admin password hash: " . substr($admin['password'], 0, 40) . "...\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";

?>
<form method="POST">
    <input type="text" name="pwd" value="TestPassword123!@#">
    <button name="test" value="1">Test UPDATE</button>
</form>
