<?php
/**
 * Profilo Utente - Cambio Password
 * Pagina per visualizzare e gestire il profilo personale
 * Feature: Fase 9 (Polish UI) - per v3.0
 */

require_once 'includes/bootstrap.php';

// Verifica autenticazione
$auth->requireLogin();

// Inizializza variabili
$user = $auth->getUser();
$success = '';
$error = '';
$validator = new InputValidator();

// Gestione cambio password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    // Verifica CSRF token
    if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido. Ricarica la pagina e riprova.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validazione input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Tutti i campi sono obbligatori';
        } else if ($newPassword !== $confirmPassword) {
            $error = 'Le nuove password non coincidono';
        } else if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $error = "La password deve essere almeno " . PASSWORD_MIN_LENGTH . " caratteri";
        } else {
            // Validazione complessità password (sec-005)
            $validator->password($newPassword, 'new_password', true);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $error = $errors['new_password'] ?? 'Password non valida';
            } else {
                // Tenta cambio password
                $userId = $auth->getUserId();
                if ($auth->changePassword($userId, $currentPassword, $newPassword)) {
                    $success = '✅ Password modificata con successo! Ti consigliamo di rieffettuare il login.';
                    
                    // Log attività (sec-009)
                    try {
                        $sql = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                        $db = Database::getInstance();
                        $db->execute($sql, [
                            $userId,
                            'password_change',
                            'users',
                            $userId,
                            'Password modificata da profilo personale',
                            $_SERVER['REMOTE_ADDR'] ?? null
                        ]);
                    } catch (Exception $e) {
                        error_log("Errore logging password change: " . $e->getMessage());
                    }
                } else {
                    $error = '❌ Password attuale non corretta oppure errore nel cambio password';
                }
            }
        }
    }
}

$pageTitle = 'Profilo Personale';
$currentPage = 'profile';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Header Profilo -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle"></i> Profilo Personale
                    </h4>
                </div>
                <div class="card-body">
                    
                    <!-- Messaggi di Feedback -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= e($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= e($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informazioni Utente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">👤 Username</label>
                                <p class="h5"><?= e($user['username']) ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">📧 Email</label>
                                <p class="h5"><?= e($user['email']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">🎭 Ruolo</label>
                                <p class="h5">
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'docente' ? 'info' : 'success') ?>">
                                        <?= ucfirst(e($user['role'])) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">🕐 Ultimo Login</label>
                                <p class="h5">
                                    <?php
                                    if ($user['last_login']) {
                                        $lastLogin = new DateTime($user['last_login']);
                                        echo $lastLogin->format('d/m/Y H:i');
                                    } else {
                                        echo '<span class="text-muted">Mai</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Form Cambio Password -->
                    <h5 class="mt-4 mb-3">
                        <i class="bi bi-lock"></i> Cambia Password
                    </h5>
                    
                    <form method="POST" class="needs-validation">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="csrf_token" value="<?= CSRFHelper::generateToken() ?>">
                        
                        <!-- Password Attuale -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-shield-lock"></i> Password Attuale
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="form-control" 
                                    placeholder="Inserisci la tua password attuale"
                                    autocomplete="current-password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword" onclick="return false;">
                                    <i class="bi bi-eye" id="toggleCurrentIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Per motivi di sicurezza, devi inserire la password attuale per cambiarla
                            </small>
                        </div>
                        
                        <!-- Nuova Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-key"></i> Nuova Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    class="form-control" 
                                    placeholder="Inserisci la nuova password"
                                    autocomplete="new-password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword" onclick="return false;">
                                    <i class="bi bi-eye" id="toggleNewIcon"></i>
                                </button>
                            </div>
                            <div class="alert alert-light border mt-2 py-2 px-3" style="border-left: 3px solid #F29400;">
                                <small class="text-muted d-block">
                                    <strong>Requisiti:</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        <li>Almeno <strong><?= PASSWORD_MIN_LENGTH ?></strong> caratteri</li>
                                        <?php if (defined('PASSWORD_REQUIRE_UPPERCASE') && PASSWORD_REQUIRE_UPPERCASE): ?>
                                            <li>Almeno una <strong>lettera maiuscola</strong> (A-Z)</li>
                                        <?php endif; ?>
                                        <?php if (defined('PASSWORD_REQUIRE_LOWERCASE') && PASSWORD_REQUIRE_LOWERCASE): ?>
                                            <li>Almeno una <strong>lettera minuscola</strong> (a-z)</li>
                                        <?php endif; ?>
                                        <?php if (defined('PASSWORD_REQUIRE_NUMBERS') && PASSWORD_REQUIRE_NUMBERS): ?>
                                            <li>Almeno un <strong>numero</strong> (0-9)</li>
                                        <?php endif; ?>
                                        <?php if (defined('PASSWORD_REQUIRE_SYMBOLS') && PASSWORD_REQUIRE_SYMBOLS): ?>
                                            <li>Almeno un <strong>carattere speciale</strong> (!@#$%^&*)</li>
                                        <?php endif; ?>
                                    </ul>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Conferma Nuova Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-key-fill"></i> Conferma Nuova Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-control" 
                                    placeholder="Ripeti la nuova password"
                                    autocomplete="new-password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" onclick="return false;">
                                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Deve coincidere con la password inserita sopra
                            </small>
                        </div>
                        
                        <!-- Pulsanti -->
                        <div class="d-grid gap-2 d-sm-flex gap-2">
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                onclick="return confirm('Sei sicuro di voler cambiare la password?');"
                            >
                                <i class="bi bi-check-circle"></i> Salva Nuova Password
                            </button>
                            <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Annulla
                            </a>
                        </div>
                    </form>
                    
                </div>
            </div>
            
            <!-- Card Info Sicurezza -->
            <div class="card alert alert-info border-info">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-shield-check"></i> Consigli di Sicurezza
                    </h5>
                    <ul class="mb-0">
                        <li>Cambia la password regolarmente (consigliato ogni 3 mesi)</li>
                        <li>Non condividere la tua password con nessuno</li>
                        <li>Usa una password unica che non utilizzi in altri siti</li>
                        <li>Se sospetti un accesso non autorizzato, cambia password immediatamente</li>
                        <li>Dopo il cambio password, ti consigliamo di effettuare nuovamente il login</li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.card-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.alert-info.alert {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

/* Input Group Enhancement per profile */
.input-group .btn-outline-secondary {
    color: #6c757d;
    border-color: #dee2e6;
    background-color: white;
}

.input-group .btn-outline-secondary:hover {
    color: #F29400;
    border-color: #F29400;
    background-color: #fff9f0;
}

.input-group .btn-outline-secondary:focus {
    color: #F29400;
    border-color: #F29400;
    box-shadow: 0 0 0 0.2rem rgba(242, 148, 0, 0.25);
}
</style>


<script nonce="<?= $_SESSION['csp_nonce'] ?>">
(function() {
    // Toggle password visibility per current password
    const toggleCurrentBtn = document.getElementById('toggleCurrentPassword');
    if (toggleCurrentBtn) {
        toggleCurrentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const input = document.getElementById('current_password');
            const icon = document.getElementById('toggleCurrentIcon');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    }
    
    // Toggle password visibility per new password
    const toggleNewBtn = document.getElementById('toggleNewPassword');
    if (toggleNewBtn) {
        toggleNewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const input = document.getElementById('new_password');
            const icon = document.getElementById('toggleNewIcon');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    }
    
    // Toggle password visibility per confirm password
    const toggleConfirmBtn = document.getElementById('toggleConfirmPassword');
    if (toggleConfirmBtn) {
        toggleConfirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const input = document.getElementById('confirm_password');
            const icon = document.getElementById('toggleConfirmIcon');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    }
})();
</script>

<?php require_once 'includes/footer.php'; ?>
