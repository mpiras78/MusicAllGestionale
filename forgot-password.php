<?php
/**
 * Forgot Password - Reset Password Page
 * Utenti possono resetare la password tramite email
 * Feature: Aggiunta in Fase 9 (Polish UI) - v3.0
 * Design: Coerente con login.php
 */

require_once 'includes/bootstrap.php';

// Se già loggato, redirect a dashboard
if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$success = '';
$step = 'request';  // 'request' o 'reset'
$token = '';

// STEP 1: Richiesta reset (email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'request') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Inserisci la tua email';
    } else {
        try {
            $db = Database::getInstance();
            
            // Verifica se email esiste
            $user = $db->queryOne(
                "SELECT id, username, email FROM users WHERE email = ? LIMIT 1",
                [$email]
            );
            
            if (!$user) {
                // Per sicurezza, non dire se email esiste o no
                $success = "Se l'email è registrata, riceverai un link per resettare la password";
            } else {
                // Genera token reset
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);  // Valido 1 ora
                
                // Salva token nel database
                $db->execute(
                    "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?",
                    [$token, $expiresAt, $user['id']]
                );
                
                // Log attività
                $db->execute(
                    "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$user['id'], 'password_reset_request', 'users', $user['id'], 'Password reset richiesto', $_SERVER['REMOTE_ADDR'] ?? null]
                );
                
                // URL reset (in produzione usare HTTPS)
                $resetUrl = BASE_URL . '/forgot-password.php?token=' . $token;
                
                // TODO: Inviare email con link reset
                // Per ora, mostra link (solo in dev)
                if (DEBUG_MODE) {
                    $success = "Email trovata! Link reset (dev only):<br><br>" .
                              "<a href='$resetUrl' class='btn btn-sm btn-primary mt-2'>Clicca qui per resettare</a><br><br>" .
                              "<small class='text-muted'>Link: $resetUrl<br>Scade tra 1 ora</small>";
                } else {
                    $success = "Se l'email è registrata, riceverai un link per resettare la password";
                }
            }
            
        } catch (Exception $e) {
            $error = "Errore nel processamento: " . $e->getMessage();
        }
    }
}

// STEP 2: Reset password con token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'reset') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($token) || empty($password)) {
        $error = 'Token o password mancante';
    } else if ($password !== $confirmPassword) {
        $error = 'Le password non coincidono';
    } else if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = "Password deve essere almeno " . PASSWORD_MIN_LENGTH . " caratteri";
    } else {
        try {
            $db = Database::getInstance();
            
            // Verifica token
            $user = $db->queryOne(
                "SELECT id, username FROM users 
                 WHERE password_reset_token = ? 
                 AND password_reset_expires > datetime('now') 
                 LIMIT 1",
                [$token]
            );
            
            if (!$user) {
                $error = "Token non valido o scaduto";
            } else {
                // Validazione complessità password
                $validator = new InputValidator();
                $validator->password($password, 'password', true);
                
                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $error = $errors['password'] ?? 'Password non valida';
                } else {
                    // Hash password
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Aggiorna password e cancella token (rimosso updated_at - colonna non esiste)
                    $db->execute(
                        "UPDATE users 
                         SET password = ?, password_reset_token = NULL, password_reset_expires = NULL
                         WHERE id = ?",
                        [$hash, $user['id']]
                    );
                    
                    // Log attività
                    $db->execute(
                        "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address) 
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [$user['id'], 'password_reset_complete', 'users', $user['id'], 'Password resetata', $_SERVER['REMOTE_ADDR'] ?? null]
                    );
                    
                    $success = "Password cambiata con successo! Redirezione al login...";
                    
                    // Redirect dopo 2 secondi
                    echo "<script nonce=\"" . $_SESSION['csp_nonce'] . "\">\n";
                    echo "setTimeout(function() {\n";
                    echo "    window.location.href = '" . BASE_URL . "/login.php';\n";
                    echo "}, 2000);\n";
                    echo "</script>";
                }
            }
            
        } catch (Exception $e) {
            $error = "Errore nel reset: " . $e->getMessage();
        }
    }
}

// Determina quale step mostrare
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $step = 'reset';
    $token = $_GET['token'];
    
    // Verifica che il token sia valido
    try {
        $db = Database::getInstance();
        $tokenCheck = $db->queryOne(
            "SELECT id FROM users 
             WHERE password_reset_token = ? 
             AND password_reset_expires > datetime('now') 
             LIMIT 1",
            [$token]
        );
        
        if (!$tokenCheck) {
            $error = "Token non valido o scaduto. Richiedi un nuovo reset della password.";
            $step = 'request';
        }
    } catch (Exception $e) {
        $error = "Errore: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card shadow-lg">
                    
                    <!-- Card Header con Logo -->
                    <div class="card-header text-center py-4" style="background-color: white;">
                        <div class="login-logo mb-3">
                            <img src="<?= BASE_URL ?>/assets/img/logo_musicall.png" 
                                 alt="MusicAll Logo" 
                                 class="logo-image">
                        </div>
                        <p class="text-muted mb-0">Reset Password</p>
                    </div>
                    
                    <div class="card-body">
                        
                        <!-- Messaggi di Errore -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Messaggi di Successo -->
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- STEP 1: Richiesta Reset (Email) -->
                        <?php if ($step === 'request'): ?>
                        
                            <p class="text-muted small mb-4">
                                Inserisci la tua email registrata e riceverai un link per resettare la password.
                            </p>
                            
                            <form method="POST">
                                <input type="hidden" name="step" value="request">
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        class="form-control form-control-lg" 
                                        placeholder="tua.email@example.com"
                                        required
                                        autofocus
                                    >
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send"></i> Invia Link Reset
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <p class="text-center text-muted small mb-0">
                                Ricordi la password? 
                                <a href="<?= BASE_URL ?>/login.php" class="text-decoration-none">
                                    <strong>Torna al login</strong>
                                </a>
                            </p>
                        
                        <!-- STEP 2: Reset Password (con Token) -->
                        <?php else: ?>
                        
                            <p class="text-muted small mb-4">
                                Inserisci la tua nuova password. Deve contenere maiuscole, minuscole, numeri e caratteri speciali.
                            </p>
                            
                            <form method="POST">
                                <input type="hidden" name="step" value="reset">
                                <input type="hidden" name="token" value="<?= e($token) ?>">
                                
                                <!-- Nuova Password -->
                                <div class="mb-4">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-key"></i> Nuova Password
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input 
                                            type="password" 
                                            id="password" 
                                            name="password" 
                                            class="form-control" 
                                            placeholder="Nuova password"
                                            autocomplete="new-password"
                                            required
                                        >
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword1" onclick="return false;">
                                            <i class="bi bi-eye" id="toggleIcon1"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Conferma Password -->
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-key-fill"></i> Conferma Password
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input 
                                            type="password" 
                                            id="confirm_password" 
                                            name="confirm_password" 
                                            class="form-control" 
                                            placeholder="Ripeti password"
                                            autocomplete="new-password"
                                            required
                                        >
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword2" onclick="return false;">
                                            <i class="bi bi-eye" id="toggleIcon2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Requisiti Password -->
                                <div class="mb-4 form-requirements">
                                    <small class="text-muted d-block">
                                        <strong>Requisiti:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Almeno <?= PASSWORD_MIN_LENGTH ?> caratteri</li>
                                            <li>Maiuscole (A-Z)</li>
                                            <li>Minuscole (a-z)</li>
                                            <li>Numeri (0-9)</li>
                                            <li>Caratteri speciali (!@#$%^&*)</li>
                                        </ul>
                                    </small>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle"></i> Resetta Password
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <p class="text-center text-muted small mb-0">
                                Token scaduto? 
                                <a href="<?= BASE_URL ?>/forgot-password.php" class="text-decoration-none">
                                    <strong>Richiedi un nuovo link</strong>
                                </a>
                            </p>
                        
                        <?php endif; ?>
                        
                    </div>
                </div>
                
                <!-- Footer Sicurezza -->
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="bi bi-shield-lock"></i> Sistema protetto - Link valido per 1 ora
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script nonce="<?= $_SESSION['csp_nonce'] ?>">
(function() {
    // Toggle password visibility per campo 1
    const toggleBtn1 = document.getElementById('togglePassword1');
    if (toggleBtn1) {
        toggleBtn1.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon1');
            
            if (passwordInput && toggleIcon) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            }
        });
    }
    
    // Toggle password visibility per campo 2
    const toggleBtn2 = document.getElementById('togglePassword2');
    if (toggleBtn2) {
        toggleBtn2.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const confirmInput = document.getElementById('confirm_password');
            const toggleIcon = document.getElementById('toggleIcon2');
            
            if (confirmInput && toggleIcon) {
                if (confirmInput.type === 'password') {
                    confirmInput.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    confirmInput.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            }
        });
    }
})();
</script>
</body>
</html>
