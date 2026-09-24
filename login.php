    <?php
require_once 'includes/bootstrap.php';

// Se già loggato, redirect a dashboard
if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$info = '';

// Messaggio se sessione scaduta
if (isset($_GET['timeout'])) {
    $info = 'La tua sessione è scaduta per inattività. Effettua nuovamente il login.';
}

if (isPost()) {
    // Verifica CSRF token
    if (!CSRFHelper::verifyToken(post('csrf_token', ''))) {
        $error = 'Token di sicurezza non valido. Ricarica la pagina e riprova.';
    } else {
        $username = post('username');
        $password = post('password');
        
        if (empty($username) || empty($password)) {
            $error = 'Inserisci username e password';
        } else {
            // Rate limiting - max 5 tentativi in 15 minuti per sec-001
            $rateLimiter = new RateLimiter();
            $identifier = $_SERVER['REMOTE_ADDR'];
            
            if (!$rateLimiter->check($identifier, 'login')) {
                $remaining = $rateLimiter->getTimeRemaining($identifier, 'login');
                $minutes = ceil($remaining / 60);
                $error = "Troppi tentativi di login. Riprova tra {$minutes} minuti.";
            } else {
                // Registra tentativo
                $rateLimiter->hit($identifier, 'login', ['username' => $username]);
                
                if ($auth->login($username, $password)) {
                    // RESET rate limiting counter al login riuscito (sec-001)
                    // Pulisci tutti i tentativi precedenti per questo IP
                    $sql = "DELETE FROM rate_limit_log WHERE identifier = ? AND action = 'login'";
                    $db = Database::getInstance();
                    $db->execute($sql, [$identifier]);
                    
                    // Redirect in base al ruolo
                    $role = $_SESSION['role'] ?? 'segreteria';
                    
                    if ($role === 'admin') {
                        redirect(BASE_URL . '/index.php'); // Dashboard
                    } else {
                        redirect(BASE_URL . '/calendario.php'); // Calendario per docente/segreteria
                    }
                } else {
                    $error = 'Credenziali non valide';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
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
                    <div class="card-header text-center py-4" style="background-color: white;">
                        <div class="login-logo mb-3">
                            <img src="<?= BASE_URL ?>/assets/img/logo_musicall.png" 
                                 alt="MusicAll Logo" 
                                 class="logo-image">
                        </div>
                        <p class="text-muted mb-0">Sistema di Gestione Scuola di Musica</p>
                    </div>
                    <div class="card-body">
                        <?php if ($info): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-clock-history"></i> <?= e($info) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?= CSRFHelper::field() ?>
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Username
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="username" 
                                       name="username" 
                                       required 
                                       autofocus
                                       value="<?= e(post('username')) ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Password
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password"
                                           autocomplete="current-password"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Accedi
                                </button>
                            </div>
                        </form>
                        
                        <!-- Link Forgot Password -->
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/forgot-password.php" class="text-decoration-none">
                                <i class="bi bi-key"></i> Hai dimenticato la password?
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-info-circle"></i> Versione <?= APP_VERSION ?>
                            </small>
                            <small class="text-muted">
                                Developed by Marco Piras & Cline
                            </small>
                        </div>
                        
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="bi bi-shield-lock"></i> Sistema protetto - Accesso riservato csp_nonce:<?= $_SESSION['csp_nonce'] ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (DEVE essere caricato PRIMA di app.js) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Custom JS (richiede jQuery e Bootstrap) -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
