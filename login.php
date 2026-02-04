<?php
require_once 'includes/bootstrap.php';

// Se già loggato, redirect a dashboard
if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

if (isPost()) {
    $username = post('username');
    $password = post('password');
    
    if (empty($username) || empty($password)) {
        $error = 'Inserisci username e password';
    } else {
        if ($auth->login($username, $password)) {
            redirect(BASE_URL . '/index.php');
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
                    <div class="card-header text-center py-4">
                        <div class="login-logo mb-3">
                            <div class="logo-circle">
                                <i class="bi bi-music-note-list"></i>
                            </div>
                        </div>
                        <h2 class="mb-2 fw-bold"><?= APP_NAME ?></h2>
                        <p class="text-muted mb-0">Sistema di Gestione Scuola di Musica</p>
                    </div>
                    <div class="card-body">
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
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Accedi
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center text-muted small">
                            <p class="mb-0">
                                <i class="bi bi-info-circle"></i> Credenziali di default:
                            </p>
                            <p class="mb-0">
                                <strong>Username:</strong> admin<br>
                                <strong>Password:</strong> admin123
                            </p>
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
</body>
</html>