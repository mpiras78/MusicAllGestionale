<?php
/**
 * Web Debug Page
 * Accedi a: http://localhost:8000/debug-web.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug_info = [
    'php_version' => PHP_VERSION,
    'files' => []
];

// Controlla file critici
$critical_files = [
    'config.php' => __DIR__ . '/config.php',
    'includes/bootstrap.php' => __DIR__ . '/includes/bootstrap.php',
    'database/musicall.sqlite' => __DIR__ . '/database/musicall.sqlite',
];

foreach ($critical_files as $name => $path) {
    $debug_info['files'][$name] = [
        'exists' => file_exists($path),
        'path' => $path,
        'size' => file_exists($path) ? filesize($path) : 0
    ];
}

// Tenta connessione a database
$db_status = 'UNKNOWN';
$admin_user = null;
$error_msg = '';

try {
    // Prova il percorso corretto: config/config.php
    $configPath = __DIR__ . '/config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception("config.php non trovato in: $configPath");
    }
    require_once $configPath;
    require_once 'includes/bootstrap.php';
    
    // Query admin
    $users = \App\Models\User::where('username', 'admin')->first();
    
    if ($users) {
        $admin_user = [
            'id' => $users->id,
            'username' => $users->username,
            'email' => $users->email,
            'role' => $users->role,
            'active' => $users->active,
            'password_hash' => substr($users->password, 0, 50) . '...'
        ];
        $db_status = 'OK - ADMIN FOUND';
    } else {
        $db_status = 'OK - NO ADMIN';
    }
} catch (Exception $e) {
    $db_status = 'ERROR';
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - MusicAll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .debug-card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card-header { background: #F29400; color: white; font-weight: bold; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4">🔍 MusicAll Debug Info</h1>
            
            <!-- Database Status -->
            <div class="debug-card">
                <div class="card-header">Database Status</div>
                <div class="card-body">
                    <p>Status: <span class="<?= strpos($db_status, 'ERROR') !== false ? 'status-error' : 'status-ok' ?>">
                        <?= htmlspecialchars($db_status) ?>
                    </span></p>
                    
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?= htmlspecialchars($error_msg) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($admin_user): ?>
                        <h5>Admin User Found:</h5>
                        <table class="table table-sm">
                            <tbody>
                                <tr><th>ID</th><td><?= $admin_user['id'] ?></td></tr>
                                <tr><th>Username</th><td><?= $admin_user['username'] ?></td></tr>
                                <tr><th>Email</th><td><?= $admin_user['email'] ?></td></tr>
                                <tr><th>Role</th><td><?= $admin_user['role'] ?></td></tr>
                                <tr><th>Active</th><td><?= $admin_user['active'] ? 'YES' : 'NO' ?></td></tr>
                                <tr><th>Password Hash</th><td><code><?= $admin_user['password_hash'] ?></code></td></tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">No admin user found in database</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- File Status -->
            <div class="debug-card">
                <div class="card-header">File Status</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>File</th><th>Exists</th><th>Size</th><th>Path</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_info['files'] as $name => $info): ?>
                                <tr>
                                    <td><strong><?= $name ?></strong></td>
                                    <td>
                                        <span class="<?= $info['exists'] ? 'status-ok' : 'status-error' ?>">
                                            <?= $info['exists'] ? '✓' : '✗' ?>
                                        </span>
                                    </td>
                                    <td><?= $info['size'] > 0 ? number_format($info['size']) . ' bytes' : '-' ?></td>
                                    <td><small><?= $info['path'] ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- PHP Info -->
            <div class="debug-card">
                <div class="card-header">PHP Info</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr><th>Version</th><td><?= PHP_VERSION ?></td></tr>
                            <tr><th>SAPI</th><td><?= php_sapi_name() ?></td></tr>
                            <tr><th>Extensions</th><td><?= implode(', ', array_slice(get_loaded_extensions(), 0, 10)) ?>...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Links -->
            <div class="debug-card">
                <div class="card-header">Quick Links</div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="btn btn-sm btn-primary">→ Login</a></li>
                        <li><a href="setup-admin.php" class="btn btn-sm btn-info">→ Setup Admin</a></li>
                        <li><a href="index.php" class="btn btn-sm btn-success">→ Home</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
