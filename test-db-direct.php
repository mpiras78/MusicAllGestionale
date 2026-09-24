<?php
/**
 * [TEMPORARY DEBUG FILE] Direct Database UPDATE + Verify
 * Questo script aggiorna E verifica nello stesso processo
 * DELETE AFTER DEBUGGING: test-db-direct.php
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

echo '<div style="background:#1e1e1e; color:#d4d4d4; padding:1rem; font-family:monospace; margin:1rem; border-left:3px solid red;">';
echo '<strong style="color:#f48771;">🔍 DEBUG MODE ACTIVE</strong>';
echo '</div>';

try {
    require_once __DIR__ . '/config/config.php';
    echo '<div style="background:#2d5016; color:#4ec9b0; padding:0.5rem 1rem; margin:1rem; border-radius:3px;">';
    echo '✓ Config loaded';
    echo '</div>';
} catch (Exception $e) {
    echo '<div style="background:#5c2e2e; color:#f48771; padding:1rem; margin:1rem; border-radius:3px;">';
    echo '<strong>ERROR loading config:</strong> ' . $e->getMessage();
    echo '</div>';
    exit(1);
}

$results = [];

try {
    // 1. Info DB_PATH
    echo '<div style="background:#2d5016; color:#4ec9b0; padding:0.5rem 1rem; margin:1rem; border-radius:3px;">';
    echo '✓ DB_PATH: ' . DB_PATH . '</div>';
    
    $results['db_path'] = DB_PATH;
    $results['db_exists'] = file_exists(DB_PATH);
    $results['db_size'] = file_exists(DB_PATH) ? filesize(DB_PATH) : 0;
    $results['db_writable'] = is_writable(DB_PATH);
    $results['db_dir_writable'] = is_writable(dirname(DB_PATH));
    
    // 2. Connessione
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $results['connection'] = 'OK';
    
    echo '<div style="background:#2d5016; color:#4ec9b0; padding:0.5rem 1rem; margin:1rem; border-radius:3px;">';
    echo '✓ PDO connected</div>';
    
    // 3. Se POST, fai UPDATE
    $new_hash = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $test_password = $_POST['test_password'] ?? 'TestPassword123!@#';
        
        // Genera hash
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        
        // UPDATE
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = datetime('now') WHERE username = 'admin'");
        $result = $stmt->execute([$new_hash]);
        
        $results['update_executed'] = $result ? 'YES' : 'NO';
        $results['rows_affected'] = $stmt->rowCount();
        $results['password_sent'] = $test_password;
        $results['hash_generated'] = $new_hash;
    }
    
    // 4. Query admin DOPO UPDATE (nello stesso script)
    $stmt = $pdo->prepare("SELECT id, username, password, role, active FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $results['admin_found'] = 'YES';
        $results['admin_id'] = $admin['id'];
        $results['admin_username'] = $admin['username'];
        $results['admin_role'] = $admin['role'];
        $results['admin_active'] = $admin['active'];
        $results['admin_password_hash'] = $admin['password'];
        
        // Se avevamo fatto UPDATE, verifica se è cambiato
        if ($new_hash) {
            $results['hash_changed'] = ($admin['password'] === $new_hash) ? 'YES ✓' : 'NO ✗';
            $results['verify_test'] = password_verify($test_password, $admin['password']) ? 'OK ✓' : 'FAILED ✗';
        }
    } else {
        $results['admin_found'] = 'NO';
    }
    
} catch (Exception $e) {
    echo '<div style="background:#5c2e2e; color:#f48771; padding:1rem; margin:1rem; border-radius:3px;">';
    echo '<strong>CRITICAL ERROR:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
    exit(1);
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct DB Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .card { margin-bottom: 2rem; }
        .card-header { background: #F29400; color: white; font-weight: bold; }
        code { background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 3px; }
        .ok { color: #28a745; }
        .error { color: #dc3545; }
        pre { background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4">🔧 Direct Database Test & Update</h1>

            <?php if (isset($results['error'])): ?>
                <div class="alert alert-danger">
                    <strong>❌ ERROR:</strong> <?= htmlspecialchars($results['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Database Info -->
            <div class="card">
                <div class="card-header">Database Configuration</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th>DB_PATH</th>
                                <td><code><?= e($results['db_path']) ?></code></td>
                            </tr>
                            <tr>
                                <th>File Exists</th>
                                <td><span class="<?= $results['db_exists'] ? 'ok' : 'error' ?>">
                                    <?= $results['db_exists'] ? '✓ YES' : '✗ NO' ?>
                                </span></td>
                            </tr>
                            <tr>
                                <th>File Size</th>
                                <td><?= number_format($results['db_size']) ?> bytes</td>
                            </tr>
                            <tr>
                                <th>File Writable</th>
                                <td><span class="<?= $results['db_writable'] ? 'ok' : 'error' ?>">
                                    <?= $results['db_writable'] ? '✓ YES' : '✗ NO' ?>
                                </span></td>
                            </tr>
                            <tr>
                                <th>Directory Writable</th>
                                <td><span class="<?= $results['db_dir_writable'] ? 'ok' : 'error' ?>">
                                    <?= $results['db_dir_writable'] ? '✓ YES' : '✗ NO' ?>
                                </span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admin Current State -->
            <div class="card">
                <div class="card-header">Admin User Current State</div>
                <div class="card-body">
                    <?php if ($results['admin_found'] === 'YES'): ?>
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr><th>ID</th><td><?= $results['admin_id'] ?></td></tr>
                                <tr><th>Username</th><td><?= $results['admin_username'] ?></td></tr>
                                <tr><th>Role</th><td><?= $results['admin_role'] ?></td></tr>
                                <tr><th>Active</th><td><?= $results['admin_active'] ? 'YES' : 'NO' ?></td></tr>
                                <tr>
                                    <th>Password Hash</th>
                                    <td>
                                        <code style="word-break: break-all; display: block; font-size: 0.8rem;">
                                            <?= e($results['admin_password_hash']) ?>
                                        </code>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-danger">Admin user NOT found!</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Test Update Form -->
            <div class="card">
                <div class="card-header">Test: Direct Database Update</div>
                <div class="card-body">
                    <p class="text-muted">Questo form aggiorna il database E verifica il cambio nello stesso script.</p>
                    <form method="POST" class="row g-3">
                        <div class="col-12">
                            <label for="test_password" class="form-label">Password di test:</label>
                            <input type="text" id="test_password" name="test_password" class="form-control" 
                                   value="TestPassword123!@#" placeholder="Es: TestPassword123!@#">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning btn-lg w-100">
                                Aggiorna Password e Verifica
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Results -->
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="card">
                    <div class="card-header">Update Results</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th>UPDATE Executed</th>
                                    <td><span class="ok"><?= $results['update_executed'] ?></span></td>
                                </tr>
                                <tr>
                                    <th>Rows Affected</th>
                                    <td><span class="ok"><?= $results['rows_affected'] ?></span></td>
                                </tr>
                                <tr>
                                    <th>Password Sent</th>
                                    <td><code><?= $results['password_sent'] ?></code></td>
                                </tr>
                                <tr>
                                    <th>Hash Generated</th>
                                    <td>
                                        <code style="word-break: break-all; display: block; font-size: 0.8rem;">
                                            <?= e($results['hash_generated']) ?>
                                        </code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Hash Changed?</th>
                                    <td>
                                        <span class="<?= strpos($results['hash_changed'], 'YES') !== false ? 'ok' : 'error' ?>">
                                            <?= $results['hash_changed'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Password Verify</th>
                                    <td>
                                        <span class="<?= strpos($results['verify_test'], 'OK') !== false ? 'ok' : 'error' ?>">
                                            <?= $results['verify_test'] ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
