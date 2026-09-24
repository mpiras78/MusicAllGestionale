<?php
/**
 * Ultra-Simple Database Debug
 * No bootstrap.php, just raw PDO
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Database - MusicAll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card-header { background: #F29400; color: white; font-weight: bold; }
        .ok { color: #28a745; }
        .error { color: #dc3545; }
        code { background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4">🔍 Database Debug</h1>

<?php

// === 1. CONTROLLA FILE ===
echo '<div class="card">';
echo '<div class="card-header">1️⃣ File Status</div>';
echo '<div class="card-body">';

$files_to_check = [
    'config/config.php' => __DIR__ . '/config/config.php',
    'config/database.php' => __DIR__ . '/config/database.php',
    'includes/bootstrap.php' => __DIR__ . '/includes/bootstrap.php',
    'database/musicall.sqlite' => __DIR__ . '/database/musicall.sqlite',
];

$db_path = null;

foreach ($files_to_check as $label => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $status = $exists ? '<span class="ok">✓</span>' : '<span class="error">✗</span>';
    
    if ($label === 'database/musicall.sqlite') {
        $db_path = $path;
    }
    
    echo "<div style='margin-bottom: 0.5rem;'>";
    echo "$status <strong>$label</strong> ";
    if ($size > 0) {
        echo "(" . number_format($size) . " bytes)";
    }
    echo "</div>";
}

echo '</div></div>';

// === 2. CONTROLLA CONFIG ===
echo '<div class="card">';
echo '<div class="card-header">2️⃣ Config Variables</div>';
echo '<div class="card-body">';

$config_ok = false;
if (file_exists(__DIR__ . '/config/config.php')) {
    ob_start();
    include(__DIR__ . '/config/config.php');
    ob_end_clean();
    $config_ok = true;
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>DB_PATH</th><td><code>" . (defined('DB_PATH') ? DB_PATH : 'NOT DEFINED') . "</code></td></tr>";
    echo "<tr><th>BASE_URL</th><td><code>" . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</code></td></tr>";
    echo "<tr><th>DEBUG_MODE</th><td><code>" . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'NOT DEFINED') . "</code></td></tr>";
    echo "</table>";
} else {
    echo "<div class='alert alert-danger'>config.php non trovato!</div>";
}

echo '</div></div>';

// === 3. PROVA CONNESSIONE DATABASE ===
echo '<div class="card">';
echo '<div class="card-header">3️⃣ Database Connection</div>';
echo '<div class="card-body">';

$pdo = null;
$admin_user = null;

try {
    // Determina il percorso del database
    if (defined('DB_PATH')) {
        $db_path = DB_PATH;
    } else {
        $db_path = __DIR__ . '/database/musicall.sqlite';
    }
    
    echo "<p>Connessione a: <code>$db_path</code></p>";
    
    if (!file_exists($db_path)) {
        echo "<div class='alert alert-warning'>⚠ Database file non esiste, sarà creato</div>";
    }
    
    // Connetti con PDO
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-success'><span class='ok'>✓</span> Connessione riuscita!</div>";
    
    // Controlla tabella users
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
    
    if ($tables) {
        echo "<p><span class='ok'>✓</span> Tabella <strong>users</strong> trovata</p>";
        
        // Query admin
        $stmt = $pdo->prepare("SELECT id, username, email, password, role, active, created_at FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin_user) {
            echo "<div class='alert alert-info'><strong>Admin User Found:</strong></div>";
            echo "<table class='table table-sm'>";
            echo "<tr><th>ID</th><td>" . $admin_user['id'] . "</td></tr>";
            echo "<tr><th>Username</th><td>" . $admin_user['username'] . "</td></tr>";
            echo "<tr><th>Email</th><td>" . $admin_user['email'] . "</td></tr>";
            echo "<tr><th>Role</th><td>" . $admin_user['role'] . "</td></tr>";
            echo "<tr><th>Active</th><td>" . ($admin_user['active'] ? 'YES' : 'NO') . "</td></tr>";
            echo "<tr><th>Password Hash</th><td><code>" . substr($admin_user['password'], 0, 40) . "...</code></td></tr>";
            echo "<tr><th>Created</th><td>" . $admin_user['created_at'] . "</td></tr>";
            echo "</table>";
        } else {
            echo "<div class='alert alert-warning'>⚠ Admin user NON trovato</div>";
            
            // Mostra tutti gli utenti
            $all = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll();
            if (count($all) > 0) {
                echo "<p>Utenti nel database:</p>";
                echo "<table class='table table-sm'>";
                foreach ($all as $user) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . $user['username'] . "</td>";
                    echo "<td>" . $user['email'] . "</td>";
                    echo "<td>" . $user['role'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert alert-danger'>❌ Database VUOTO - nessun utente</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Tabella 'users' NON trovata</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Errore Connessione:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo '</div></div>';

// === 4. AZIONI ===
echo '<div class="card">';
echo '<div class="card-header">4️⃣ Azioni</div>';
echo '<div class="card-body">';

if ($admin_user) {
    echo "<p>Admin user esiste. Puoi:</p>";
    echo "<ul>";
    echo "<li><a href='login.php' class='btn btn-sm btn-primary'>→ Vai a Login</a></li>";
    echo "<li><a href='setup-admin.php' class='btn btn-sm btn-warning'>→ Cambia Password Admin</a></li>";
    echo "</ul>";
} else {
    echo "<p><strong>Admin non trovato!</strong> Imposta la password:</p>";
    echo "<a href='setup-admin.php' class='btn btn-warning'><i class='bi bi-key'></i> Vai a Setup Admin</a>";
}

echo '</div></div>';

?>

        </div>
    </div>
</div>

</body>
</html>
