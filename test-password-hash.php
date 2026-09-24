<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

// Includi config
require_once __DIR__ . '/config/config.php';

// Connessione PDO diretta
$pdo = new PDO('sqlite:' . DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$admin_user = null;
$test_password = '';

// Se form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_password = $_POST['test_password'] ?? '';
    
    // Query admin corrente
    $stmt = $pdo->prepare("SELECT id, username, email, password, role, active FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_user && $test_password) {
        $is_valid = password_verify($test_password, $admin_user['password']);
        ?>
        <div class="alert alert-<?= $is_valid ? 'success' : 'danger' ?>" style="margin: 1rem;">
            <h4><?= $is_valid ? '✅ PASSWORD VALIDA!' : '❌ PASSWORD ERRATA!' ?></h4>
            <p><strong>Hash nel database:</strong></p>
            <code style="word-break: break-all; display: block; background: #f5f5f5; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
                <?= htmlspecialchars($admin_user['password']) ?>
            </code>
            <p><strong>Password inserita:</strong> <code><?= htmlspecialchars($test_password) ?></code></p>
            <p><strong>password_verify() result:</strong> <code><?= $is_valid ? 'TRUE' : 'FALSE' ?></code></p>
        </div>
        <?php
    }
}

// Query admin
$stmt = $pdo->prepare("SELECT id, username, email, password, role, active, created_at, updated_at FROM users WHERE username = 'admin'");
$stmt->execute();
$admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Password Hash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">🔐 Test Password Hash Verification</h1>

            <?php if (!$admin_user): ?>
                <div class="alert alert-danger">
                    ❌ Admin user non trovato nel database!
                </div>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Admin User Info</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr><th>ID</th><td><?= $admin_user['id'] ?></td></tr>
                                <tr><th>Username</th><td><?= $admin_user['username'] ?></td></tr>
                                <tr><th>Email</th><td><?= $admin_user['email'] ?></td></tr>
                                <tr><th>Role</th><td><?= $admin_user['role'] ?></td></tr>
                                <tr><th>Active</th><td><?= $admin_user['active'] ? 'YES ✓' : 'NO ✗' ?></td></tr>
                                <tr><th>Password Hash</th><td>
                                    <code style="word-break: break-all; font-size: 0.85rem;">
                                        <?= htmlspecialchars($admin_user['password']) ?>
                                    </code>
                                </td></tr>
                                <tr><th>Hash Algorithm</th><td>
                                    <?php
                                    $hash = $admin_user['password'];
                                    if (strpos($hash, '$2y$') === 0) {
                                        echo '✓ BCrypt ($2y$)';
                                    } elseif (strpos($hash, '$2a$') === 0) {
                                        echo '✓ BCrypt ($2a$)';
                                    } elseif (strpos($hash, '$2b$') === 0) {
                                        echo '✓ BCrypt ($2b$)';
                                    } else {
                                        echo '❌ Non è un hash BCrypt valido!';
                                    }
                                    ?>
                                </td></tr>
                                <tr><th>Created</th><td><?= $admin_user['created_at'] ?></td></tr>
                                <tr><th>Updated</th><td><?= $admin_user['updated_at'] ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">Test Password Verification</div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label for="test_password" class="form-label">Inserisci la password che hai usato in setup-admin.php:</label>
                                <input type="password" id="test_password" name="test_password" class="form-control form-control-lg" placeholder="Password di test" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning btn-lg w-100">Test Verifica Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>ℹ️ Istruzioni:</strong>
                    <ol>
                        <li>Inserisci la password che hai usato in <code>setup-admin.php</code></li>
                        <li>Clicca "Test Verifica Password"</li>
                        <li>Vedrai se la password è valida o no</li>
                        <li>Se mostra ❌, il problema è nella generazione dell'hash</li>
                    </ol>
                </div>

                <div class="alert alert-secondary">
                    <strong>Debug Info:</strong>
                    <ul class="mb-0">
                        <li>PHP version: <?= phpversion() ?></li>
                        <li>DB_PATH: <?= DB_PATH ?></li>
                        <li>DATABASE SIZE: <?= number_format(filesize(DB_PATH)) ?> bytes</li>
                    </ul>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
