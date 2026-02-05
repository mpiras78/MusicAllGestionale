<?php
/**
 * Pagina Attivazione Account
 * L'utente arriva tramite link, inserisce codice attivazione E imposta password
 */

require_once 'includes/bootstrap.php';

// Ottieni istanza database
$db = Database::getInstance();

$success = '';
$error = '';
$urlToken = $_GET['token'] ?? '';
$username = '';
$email = '';
$step = 1; // Step 1: Verifica codice, Step 2: Imposta password

// Verifica url_token valido
if ($urlToken) {
    $sql = "
        SELECT u.id, u.username, u.email, at.activation_code, at.expires_at, at.used
        FROM activation_tokens at
        JOIN users u ON at.user_id = u.id
        WHERE at.url_token = ?
    ";
    
    $tokenData = $db->queryOne($sql, [$urlToken]);
    
    if (!$tokenData) {
        $error = 'Link di attivazione non valido o scaduto.';
        $urlToken = '';
    } elseif ($tokenData['used']) {
        $error = 'Questo link è già stato utilizzato. L\'account è già attivo.';
        $urlToken = '';
    } elseif (strtotime($tokenData['expires_at']) < time()) {
        $error = 'Link di attivazione scaduto. Contatta l\'amministratore per un nuovo link.';
        $urlToken = '';
    } else {
        // Token valido - mostra info utente
        $username = $tokenData['username'];
        $email = $tokenData['email'];
    }
}

// Gestione submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url_token'])) {
    $urlToken = $_POST['url_token'];
    $activationCode = strtoupper(trim($_POST['activation_code'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validazione codice
    if (empty($activationCode)) {
        $error = 'Inserisci il codice di attivazione';
    } 
    // Validazione password
    elseif (empty($password)) {
        $error = 'Inserisci una password';
    }
    elseif ($password !== $confirmPassword) {
        $error = 'Le password non corrispondono';
    }
    else {
        // Validazione password forte
        $passwordValidation = SecurityHelper::validateStrongPassword($password);
        if (!$passwordValidation['valid']) {
            $error = $passwordValidation['error'];
        } else {
            // Verifica codice attivazione
            $sql = "
                SELECT at.*, u.id as user_id, u.username, u.email 
                FROM activation_tokens at
                JOIN users u ON at.user_id = u.id
                WHERE at.url_token = ? 
                AND at.activation_code = ?
                AND at.used = 0 
                AND at.expires_at > datetime('now','localtime')
            ";
            
            $tokenData = $db->queryOne($sql, [$urlToken, $activationCode]);
            
            if (!$tokenData) {
                $error = 'Codice di attivazione non corretto o scaduto.';
                // Mantieni dati per form
                $recheckSql = "
                    SELECT u.username, u.email
                    FROM activation_tokens at
                    JOIN users u ON at.user_id = u.id
                    WHERE at.url_token = ?
                ";
                $userInfo = $db->queryOne($recheckSql, [$urlToken]);
                if ($userInfo) {
                    $username = $userInfo['username'];
                    $email = $userInfo['email'];
                }
            } else {
                // Codice corretto - Hash password e attiva
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $db->execute(
                    "UPDATE users SET password = ?, active = 1 WHERE id = ?", 
                    [$passwordHash, $tokenData['user_id']]
                );
                
                // Marca token come usato
                $db->execute(
                    "UPDATE activation_tokens SET used = 1, used_at = datetime('now','localtime') WHERE id = ?",
                    [$tokenData['id']]
                );
                
                $success = 'Account attivato con successo! Ora puoi effettuare il login.';
                $urlToken = '';
                $username = $tokenData['username'];
            }
        }
    }
}

$pageTitle = 'Attivazione Account';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-5">
            
            <!-- Logo -->
            <div class="text-center mb-4">
                <img src="assets/img/logo_musicall.png" alt="<?= APP_NAME ?>" style="max-width: 200px;">
                <h2 class="mt-3">🎵 Attivazione Account</h2>
            </div>

            <!-- Card -->
            <div class="card shadow">
                <div class="card-body p-4">
                    
                    <?php if ($success): ?>
                        <!-- Successo -->
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                        </div>
                        <div class="text-center">
                            <p class="text-muted mb-3">Account: <strong><?= htmlspecialchars($username) ?></strong></p>
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Vai al Login
                            </a>
                        </div>
                        
                    <?php elseif ($urlToken && $username): ?>
                        <!-- Form Attivazione completo -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Attivazione per: <strong><?= htmlspecialchars($username) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($email) ?></small>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="activationForm">
                            <input type="hidden" name="url_token" value="<?= htmlspecialchars($urlToken) ?>">
                            
                            <!-- Step 1: Codice Attivazione -->
                            <div class="mb-3">
                                <label class="form-label"><strong>1.</strong> Codice Attivazione</label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg text-center" 
                                    name="activation_code" 
                                    placeholder="ES: A3B9C5"
                                    maxlength="6"
                                    required
                                    autofocus
                                    style="letter-spacing: 0.5em; font-weight: bold; text-transform: uppercase;">
                                <div class="form-text">
                                    <i class="bi bi-envelope"></i> 
                                    Inserisci il codice a 6 cifre ricevuto via email
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Step 2: Imposta Password -->
                            <div class="mb-3">
                                <label class="form-label"><strong>2.</strong> Imposta Password</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    name="password" 
                                    id="password"
                                    required>
                                <div class="mt-2">
                                    <small><strong>Requisiti password:</strong></small>
                                    <ol class="small mb-0 ps-3">
                                        <li id="req-length" class="text-muted">
                                            <i class="bi bi-circle"></i> Minimo 6 caratteri
                                        </li>
                                        <li id="req-number" class="text-muted">
                                            <i class="bi bi-circle"></i> Almeno 1 numero
                                        </li>
                                        <li id="req-uppercase" class="text-muted">
                                            <i class="bi bi-circle"></i> Almeno 1 lettera maiuscola
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Conferma Password</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    name="confirm_password" 
                                    id="confirm_password"
                                    required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Attiva Account e Imposta Password
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted small mb-0">
                                Non hai ricevuto il codice? 
                                <a href="mailto:<?= EMAIL_FROM ?>">Contatta l'amministratore</a>
                            </p>
                        </div>
                        
                    <?php else: ?>
                        <!-- Nessun token o token non valido -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Link mancante</strong><br>
                                Devi utilizzare il link ricevuto via email per attivare il tuo account.
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Controlla la tua email o contatta l'amministratore.
                            </p>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    <a href="login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Torna al Login
                    </a>
                </p>
                <p class="text-muted small mb-0">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?> - v<?= APP_VERSION ?>
                </p>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-uppercase codice mentre digiti
const codeInput = document.querySelector('input[name="activation_code"]');
if (codeInput) {
    codeInput.addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
}

// Validazione password in tempo reale
const passwordInput = document.getElementById('password');
if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        validatePasswordRequirements(this.value);
    });
}

function validatePasswordRequirements(password) {
    // Requisito 1: Minimo 6 caratteri
    const lengthReq = document.getElementById('req-length');
    if (password.length >= 6) {
        lengthReq.className = 'text-success';
        lengthReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Minimo 6 caratteri';
    } else {
        lengthReq.className = 'text-muted';
        lengthReq.innerHTML = '<i class="bi bi-circle"></i> Minimo 6 caratteri';
    }
    
    // Requisito 2: Almeno 1 numero
    const numberReq = document.getElementById('req-number');
    if (/[0-9]/.test(password)) {
        numberReq.className = 'text-success';
        numberReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Almeno 1 numero';
    } else {
        numberReq.className = 'text-muted';
        numberReq.innerHTML = '<i class="bi bi-circle"></i> Almeno 1 numero';
    }
    
    // Requisito 3: Almeno 1 maiuscola
    const uppercaseReq = document.getElementById('req-uppercase');
    if (/[A-Z]/.test(password)) {
        uppercaseReq.className = 'text-success';
        uppercaseReq.innerHTML = '<i class="bi bi-check-circle-fill"></i> Almeno 1 lettera maiuscola';
    } else {
        uppercaseReq.className = 'text-muted';
        uppercaseReq.innerHTML = '<i class="bi bi-circle"></i> Almeno 1 lettera maiuscola';
    }
}

// Validazione match password
const confirmInput = document.getElementById('confirm_password');
if (confirmInput && passwordInput) {
    confirmInput.addEventListener('input', function() {
        if (this.value && this.value !== passwordInput.value) {
            this.setCustomValidity('Le password non corrispondono');
        } else {
            this.setCustomValidity('');
        }
    });
}
</script>

</body>
</html>