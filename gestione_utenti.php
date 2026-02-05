<?php
/**
 * Gestione Utenti - CRUD Utenti e Ruoli
 */

require_once 'includes/bootstrap.php';

// Verifica autenticazione
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Solo admin può gestire utenti
if (!$auth->isAdmin()) {
    die('Accesso negato. Solo gli amministratori possono gestire gli utenti.');
}

require_once 'includes/controllers/UsersController.php';

// Ottieni connessione database
if (!isset($db)) {
    $db = Database::getInstance();
}
$usersController = new UsersController($db);

// Gestione richieste AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $result = ['success' => false, 'error' => 'Azione non valida'];
    
    switch ($action) {
        case 'create':
            $result = $usersController->create($_POST);
            break;
            
        case 'update':
            $result = $usersController->update($_POST['id'], $_POST);
            break;
            
        case 'delete':
            $result = $usersController->delete($_POST['id']);
            break;
            
        case 'toggle_active':
            $result = $usersController->toggleActive($_POST['id']);
            break;
    }
    
    echo json_encode($result);
    exit;
}

$success = '';
$error = '';

// Gestione messaggio da redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Gestione azioni standard (per toggle e delete che usano form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $result = $usersController->delete($_POST['id']);
            if ($result['success']) {
                $success = 'Utente eliminato con successo!';
            } else {
                $error = $result['error'];
            }
            break;
            
        case 'toggle_active':
            $result = $usersController->toggleActive($_POST['id']);
            if ($result['success']) {
                $success = 'Stato utente aggiornato!';
            } else {
                $error = $result['error'];
            }
            break;
    }
}

// Carica lista utenti
$users = $usersController->index();
$docenti = $usersController->getAllDocenti();

$pageTitle = 'Gestione Utenti';
require_once 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> Gestione Utenti</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateModal()">
            <i class="bi bi-plus-circle"></i> Nuovo Utente
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiche Ruoli -->
    <div class="row mb-4">
        <?php
        $roleStats = ['admin' => 0, 'segreteria' => 0, 'docente' => 0];
        foreach ($users as $user) {
            $roleStats[$user['role']]++;
        }
        ?>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">👑 <?= $roleStats['admin'] ?></h3>
                    <p class="mb-0">Amministratori</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary">📋 <?= $roleStats['segreteria'] ?></h3>
                    <p class="mb-0">Segreteria</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">🎵 <?= $roleStats['docente'] ?></h3>
                    <p class="mb-0">Docenti</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Utenti -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-table"></i> Elenco Utenti</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Docente Collegato</th>
                            <th>Stato</th>
                            <th>Ultimo Accesso</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <?php if ($user['username'] === 'admin'): ?>
                                <span class="badge bg-warning">Protetto</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                            <td>
                                <?php
                                $badgeClass = match($user['role']) {
                                    'admin' => 'danger',
                                    'segreteria' => 'primary',
                                    'docente' => 'success',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $user['role_label'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['docente_cognome']): ?>
                                    <i class="bi bi-person-check-fill text-success"></i>
                                    <?= htmlspecialchars($user['docente_cognome'] . ' ' . $user['docente_nome']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['active']): ?>
                                    <span class="badge bg-success">Attivo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disattivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                    <small><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Mai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" 
                                            onclick='editUser(<?= json_encode($user) ?>)'
                                            title="Modifica">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Cambiare stato utente?')">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-outline-warning" title="Attiva/Disattiva">
                                            <i class="bi bi-toggle-<?= $user['active'] ? 'on' : 'off' ?>"></i>
                                        </button>
                                    </form>
                                    
                                    <?php if ($user['username'] !== 'admin'): ?>
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Eliminare questo utente?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Elimina">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crea/Modifica Utente -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="userForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuovo Utente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="userId">
                    
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    
                    
                    <!-- Alert errori nella modale -->
                    <div id="modalAlert" class="alert alert-danger" style="display:none;" role="alert"></div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ruolo *</label>
                        <select class="form-select" name="role" id="role" required onchange="toggleDocenteSelect()">
                            <option value="">-- Seleziona Ruolo --</option>
                            <option value="admin">👑 Amministratore</option>
                            <option value="segreteria">📋 Segreteria</option>
                            <option value="docente">🎵 Docente</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="docenteSelectDiv" style="display:none;">
                        <label class="form-label">Collega a Docente</label>
                        <select class="form-select" name="docente_id" id="docente_id">
                            <option value="">-- Nessun collegamento --</option>
                            <?php foreach ($docenti as $doc): ?>
                            <option value="<?= $doc['id'] ?>" 
                                    <?= $doc['user_id'] ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($doc['cognome'] . ' ' . $doc['nome']) ?>
                                <?= $doc['user_id'] ? '(già collegato)' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="docenteSelectHelp">Opzionale: collega questo account a un docente esistente</div>
                    </div>
                    
                    <!-- Mostra collegamento esistente (solo in modifica) -->
                    <div class="mb-3" id="docenteLinkedDiv" style="display:none;">
                        <label class="form-label">Docente Collegato</label>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-person-check-fill"></i> 
                            <strong id="docenteLinkedName"></strong>
                            <div class="form-text mt-1">
                                Il collegamento non può essere modificato dopo la creazione
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nuovo Utente';
    document.getElementById('formAction').value = 'create';
    document.getElementById('userId').value = '';
    document.getElementById('userForm').reset();
    document.getElementById('username').readOnly = false;
    document.getElementById('modalAlert').style.display = 'none';
    toggleDocenteSelect();
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Modifica Utente';
    document.getElementById('formAction').value = 'update';
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('username').readOnly = true;
    document.getElementById('email').value = user.email || '';
    document.getElementById('role').value = user.role;
    document.getElementById('modalAlert').style.display = 'none';
    
    // Gestione collegamento docente
    if (user.role === 'docente' && user.docente_id) {
        document.getElementById('docenteSelectDiv').style.display = 'none';
        document.getElementById('docenteLinkedDiv').style.display = 'block';
        document.getElementById('docenteLinkedName').textContent = 
            (user.docente_cognome || '') + ' ' + (user.docente_nome || '');
    } else {
        document.getElementById('docenteLinkedDiv').style.display = 'none';
        if (user.docente_id) {
            document.getElementById('docente_id').value = user.docente_id;
        }
        toggleDocenteSelect();
    }
    
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

function toggleDocenteSelect() {
    const role = document.getElementById('role').value;
    const div = document.getElementById('docenteSelectDiv');
    div.style.display = role === 'docente' ? 'block' : 'none';
}

// Gestione submit AJAX
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const modalAlert = document.getElementById('modalAlert');
    
    // Prepara dati form
    const formData = new FormData(this);
    formData.append('ajax', '1');
    
    // Disabilita bottone submit
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvataggio...';
    
    // Invia richiesta AJAX
    fetch('gestione_utenti.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Chiudi modale
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            
            // Se c'è un messaggio, passalo tramite URL
            if (data.message) {
                window.location.href = 'gestione_utenti.php?success=' + encodeURIComponent(data.message);
            } else {
                window.location.reload();
            }
        } else {
            // Mostra errore nella modale
            modalAlert.textContent = data.error;
            modalAlert.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-save"></i> Salva';
        }
    })
    .catch(error => {
        modalAlert.textContent = 'Errore di connessione: ' + error.message;
        modalAlert.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-save"></i> Salva';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>