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
    $username = post('username');
    $password = post('password');
    
    if (empty($username) || empty($password)) {
        $error = 'Inserisci username e password';
    } else {
        if ($auth->login($username, $password)) {
            // Redirect in base al ruolo
            $role = $_SESSION['user_role'];
            
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
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Developed by Marco Piras & Cline
                            </small>
                        </div>
                        
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="bi bi-shield-lock"></i> Sistema protetto - Accesso riservato
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
});
</script>
</body>
</html>
