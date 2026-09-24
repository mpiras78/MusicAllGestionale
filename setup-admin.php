<?php
/**
 * Setup Admin Password - Web Page
 * Accesso: http://localhost:8000/setup-admin.php
 */

// Debug: Log all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

require_once 'includes/bootstrap.php';

$message = '';
$error = '';
$success = false;
$debug = '';

// Se il form viene inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validazioni
    if (empty($password)) {
        $error = 'La password è obbligatoria';
    } else if ($password !== $confirmPassword) {
        $error = 'Le password non coincidono';
    } else if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = "La password deve avere almeno " . PASSWORD_MIN_LENGTH . " caratteri";
    } else {
        try {
            $db = Database::getInstance();
            
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Controlla se admin esiste
            $admin = $db->queryOne(
                "SELECT id FROM users WHERE username = 'admin' LIMIT 1"
            );
            
            if ($admin) {
                // Update (rimosso updated_at - colonna non esiste nel database)
                $db->execute(
                    "UPDATE users SET password = ? WHERE username = 'admin'",
                    [$hash]
                );
                $message = '✅ Password di admin aggiornata con successo!';
            } else {
                // Insert (rimosso created_at e updated_at - colonne non esistono)
                $db->execute(
                    "INSERT INTO users (username, password, email, role, active) 
                     VALUES (?, ?, ?, ?, ?)",
                    ['admin', $hash, 'admin@musicall.it', 'admin', 1]
                );
                $message = '✅ Utente admin creato con successo!';
            }
            
            $success = true;
        } catch (Exception $e) {
            $error = "Errore: " . $e->getMessage();
            $debug = $e->getTraceAsString();
            error_log("Setup Admin Error: " . $error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - MusicAll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #F29400 0%, #ff8533 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .setup-card .card-header {
            background: white;
            border-radius: 20px 20px 0 0;
            border-bottom: 2px solid #F29400;
            padding: 2.5rem 2rem;
        }
        .setup-card .card-body {
            padding: 2.5rem;
        }
        .logo-img {
            max-width: 100px;
            height: auto;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: #F29400;
            border-color: #F29400;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #d68200;
            border-color: #d68200;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .form-control:focus {
            border-color: #F29400;
            box-shadow: 0 0 0 0.2rem rgba(242, 148, 0, 0.25);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card setup-card">
                <div class="card-header text-center">
                    <img src="<?= BASE_URL ?>/assets/img/logo_musicall.png" alt="Logo" class="logo-img">
                    <h4 class="mb-0">Setup Admin Account</h4>
                    <small class="text-muted">MusicAll v3.0</small>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <div class="success-message">
                            <i class="bi bi-check-circle"></i> <?= e($message) ?>
                        </div>
                        <p class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Vai al Login
                            </a>
                        </p>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
                            </div>
                            <?php if ($debug): ?>
                                <div class="alert alert-light small" style="border-left: 3px solid #dc3545; margin-top: 1rem; font-family: monospace;">
                                    <strong>Debug Info:</strong>
                                    <pre style="margin: 0.5rem 0; font-size: 0.8rem;"><?= e(substr($debug, 0, 500)) ?></pre>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <p class="text-muted small mb-4">
                            Imposta una password sicura per l'account admin. La password deve avere almeno <?= PASSWORD_MIN_LENGTH ?> caratteri con maiuscole, minuscole, numeri e caratteri speciali.
                        </p>
                        
                        <form method="POST" class="needs-validation">
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-key"></i> Password
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control form-control-lg"
                                    placeholder="Password sicura"
                                    required
                                    autofocus
                                >
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-key-fill"></i> Conferma Password
                                </label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-control form-control-lg"
                                    placeholder="Ripeti password"
                                    required
                                >
                            </div>
                            
                            <div class="alert alert-light border" style="border-left: 3px solid #F29400;">
                                <strong>Requisiti Password:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li>Almeno <?= PASSWORD_MIN_LENGTH ?> caratteri</li>
                                    <li>Almeno una <strong>maiuscola</strong> (A-Z)</li>
                                    <li>Almeno una <strong>minuscola</strong> (a-z)</li>
                                    <li>Almeno un <strong>numero</strong> (0-9)</li>
                                    <li>Almeno un <strong>carattere speciale</strong> (!@#$%^&*)</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Imposta Password Admin
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                </div>
            </div>
            
            <p class="text-center text-white small mt-3">
                <i class="bi bi-shield-lock"></i> Setup Account Protetto
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
