<?php
/**
 * Ultra Debug - Mostra ogni step
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<div class='alert alert-danger' style='margin: 1rem;'>";
    echo "<strong>PHP ERROR [$errno]:</strong> $errstr<br>";
    echo "<small>$errfile : $errline</small>";
    echo "</div>";
    return false;
});

header('Content-Type: text/html; charset=utf-8');

echo '<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 2rem; }
.step { background: #252526; border-left: 3px solid #F29400; padding: 1rem; margin: 1rem 0; }
.ok { color: #4ec9b0; }
.error { color: #f48771; }
code { background: #1e1e1e; padding: 0.25rem 0.5rem; border-radius: 3px; }
</style>';

echo '<h1>🔍 Ultra Debug Mode</h1>';

// STEP 1: Config
echo '<div class="step"><strong>STEP 1: Load Config</strong>';
try {
    $config_path = __DIR__ . '/config/config.php';
    echo "<br>Config path: <code>$config_path</code>";
    echo "<br>Exists: " . (file_exists($config_path) ? '<span class="ok">✓</span>' : '<span class="error">✗</span>');
    
    require_once $config_path;
    echo "<br>Loaded: <span class='ok'>✓</span>";
    
    if (defined('DB_PATH')) {
        echo "<br>DB_PATH defined: <span class='ok'>✓</span>";
        echo "<br>DB_PATH value: <code>" . DB_PATH . "</code>";
    } else {
        echo "<br>DB_PATH: <span class='error'>NOT DEFINED</span>";
    }
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

// STEP 2: Check database file
echo '<div class="step"><strong>STEP 2: Check Database File</strong>';
try {
    if (!defined('DB_PATH')) {
        throw new Exception('DB_PATH not defined');
    }
    
    $db_path = DB_PATH;
    echo "<br>DB_PATH: <code>$db_path</code>";
    echo "<br>File exists: " . (file_exists($db_path) ? '<span class="ok">✓</span>' : '<span class="error">✗</span>');
    echo "<br>File size: " . (file_exists($db_path) ? filesize($db_path) . ' bytes' : 'N/A');
    echo "<br>File writable: " . (is_writable($db_path) ? '<span class="ok">✓</span>' : '<span class="error">✗</span>');
    echo "<br>Dir writable: " . (is_writable(dirname($db_path)) ? '<span class="ok">✓</span>' : '<span class="error">✗</span>');
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

// STEP 3: PDO Connection
echo '<div class="step"><strong>STEP 3: PDO Connection</strong>';
$pdo = null;
try {
    if (!defined('DB_PATH')) {
        throw new Exception('DB_PATH not defined');
    }
    
    echo "<br>Connecting to: <code>sqlite:" . DB_PATH . "</code>";
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<br>Connected: <span class='ok'>✓</span>";
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

// STEP 4: Query admin user
echo '<div class="step"><strong>STEP 4: Query Admin User</strong>';
$admin = null;
try {
    if (!$pdo) {
        throw new Exception('PDO not connected');
    }
    
    echo "<br>Executing: SELECT * FROM users WHERE username = 'admin'";
    $stmt = $pdo->prepare("SELECT id, username, password, role, active FROM users WHERE username = 'admin'");
    $stmt->execute();
    echo "<br>Query executed: <span class='ok'>✓</span>";
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "<br>Admin found: <span class='ok'>✓</span>";
        echo "<br>  ID: " . $admin['id'];
        echo "<br>  Username: " . $admin['username'];
        echo "<br>  Role: " . $admin['role'];
        echo "<br>  Active: " . ($admin['active'] ? 'YES' : 'NO');
        echo "<br>  Hash: <code style='display:block; word-break: break-all;'>" . substr($admin['password'], 0, 50) . "...</code>";
    } else {
        echo "<br><span class='error'>Admin user NOT found</span>";
    }
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

// STEP 5: Update test
echo '<div class="step"><strong>STEP 5: Test UPDATE</strong>';
try {
    if (!$pdo) {
        throw new Exception('PDO not connected');
    }
    
    $test_password = 'TestPassword123!@#';
    echo "<br>Test password: <code>$test_password</code>";
    
    $hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "<br>Generated hash: <code style='display:block; word-break: break-all;'>" . substr($hash, 0, 50) . "...</code>";
    
    echo "<br>Executing UPDATE query...";
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = datetime('now') WHERE username = 'admin'");
    $result = $stmt->execute([$hash]);
    $rows = $stmt->rowCount();
    
    echo "<br>UPDATE executed: <span class='ok'>✓</span>";
    echo "<br>Result: " . ($result ? '<span class="ok">true</span>' : '<span class="error">false</span>');
    echo "<br>Rows affected: " . $rows;
    
    // Query di verifica SUBITO DOPO
    echo "<br><br><strong>Verifying immediately after UPDATE...</strong>";
    $stmt2 = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt2->execute();
    $check = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($check) {
        echo "<br>New hash in DB: <code style='display:block; word-break: break-all;'>" . substr($check['password'], 0, 50) . "...</code>";
        echo "<br>Hashes match: " . ($hash === $check['password'] ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>');
        echo "<br>Password verify: " . (password_verify($test_password, $check['password']) ? '<span class="ok">✓ OK</span>' : '<span class="error">✗ FAILED</span>');
    } else {
        echo "<br><span class='error'>Could not verify after UPDATE</span>";
    }
    
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

echo '<div class="step"><strong>STEP 6: All Database Info</strong>';
try {
    if (!$pdo) {
        throw new Exception('PDO not connected');
    }
    
    // Count users
    $count = $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "<br>Total users: $count";
    
    // List all users
    echo "<br><br>All users:";
    $all = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll();
    foreach ($all as $u) {
        echo "<br>  [{$u['id']}] {$u['username']} ({$u['email']}) - {$u['role']}";
    }
    
} catch (Exception $e) {
    echo "<br><span class='error'>ERROR: " . $e->getMessage() . "</span>";
}
echo '</div>';

?>
