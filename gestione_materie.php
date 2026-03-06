<?php
require_once 'includes/bootstrap.php';

$auth->requireLogin();

$page_title = 'Gestione Materie';
$current_page = 'materie';

// Inizializza Database
$db = Database::getInstance();

// Ottieni tutte le materie
$materie = $db->query("
    SELECT m.*, COUNT(DISTINCT i.id) as num_iscrizioni_attive
    FROM materie m
    LEFT JOIN iscrizioni i ON m.id = i.materia_id AND i.stato = 'attiva'
    GROUP BY m.id
    ORDER BY m.nome
");

// Se non è array, converti
if (!is_array($materie)) {
    $materie = [];
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-journal-text"></i> Gestione Materie
            </h1>
            <p class="text-muted mb-0">Visualizza e gestisci le materie disponibili</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMateriaModal">
                <i class="bi bi-plus-circle"></i> Nuova Materia
            </button>
        </div>
    </div>

    <!-- Tabella Materie -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Materie
                <span class="badge bg-primary ms-2" id="countMaterie"><?= count($materie) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabellaMaterie">
                    <thead>
                        <tr>
                            <th>Nome Materia</th>
                            <th class="text-center">Iscrizioni Attive</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materie)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Nessuna materia trovata</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materie as $materia): ?>
                                <tr data-materia-id="<?= $materia['id'] ?>">
                                    <td><?= e($materia['nome']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $materia['num_iscrizioni_attive'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" onclick="modificaMateria(<?= $materia['id'] ?>)" title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confermaEliminazione(<?= $materia['id'] ?>, '<?= addslashes($materia['nome']) ?>', <?= $materia['num_iscrizioni_attive'] ?>)" title="Elimina" <?= $materia['num_iscrizioni_attive'] > 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuova Materia -->
<div class="modal fade" id="addMateriaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Nuova Materia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuovaMateria">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome Materia *</label>
                        <input type="text" class="form-control" name="nome" required placeholder="Es. Pianoforte, Chitarra...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-success" onclick="salvaMateria()">
                    <i class="bi bi-check-circle"></i> Salva Materia
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Materia -->
<div class="modal fade" id="editMateriaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Modifica Materia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formModificaMateria">
                    <input type="hidden" name="id" id="editMateriaId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome Materia *</label>
                        <input type="text" class="form-control" name="nome" id="editNome" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-warning" onclick="aggiornaMateria()">
                    <i class="bi bi-check-circle"></i> Aggiorna
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Conferma Eliminazione -->
<div class="modal fade" id="confermaEliminazioneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Conferma Eliminazione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler eliminare la materia <strong id="nomeMateriaEliminare"></strong>?</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Questa azione non può essere annullata.
                </div>
                <input type="hidden" id="idMateriaEliminare">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminaMateria()">
                    <i class="bi bi-trash"></i> Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function salvaMateria() {
    const form = document.getElementById('formNuovaMateria');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_materie.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Materia creata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Errore durante il salvataggio');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function modificaMateria(id) {
    fetch(`<?= BASE_URL ?>/api/api_materie.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const m = data.data;
                document.getElementById('editMateriaId').value = m.id;
                document.getElementById('editNome').value = m.nome;
                
                const modal = new bootstrap.Modal(document.getElementById('editMateriaModal'));
                modal.show();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function aggiornaMateria() {
    const form = document.getElementById('formModificaMateria');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_materie.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Materia aggiornata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Errore durante l\'aggiornamento');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function confermaEliminazione(id, nome, numIscrizioni) {
    if (numIscrizioni > 0) {
        mostraToast('Attenzione', 'Questa materia è utilizzata da ' + numIscrizioni + ' iscrizione/i attiva/e e non può essere eliminata', 'warning');
        return;
    }
    
    document.getElementById('nomeMateriaEliminare').textContent = nome;
    document.getElementById('idMateriaEliminare').value = id;
    
    const modal = new bootstrap.Modal(document.getElementById('confermaEliminazioneModal'));
    modal.show();
}

function eliminaMateria() {
    const id = document.getElementById('idMateriaEliminare').value;
    
    fetch('<?= BASE_URL ?>/api/api_materie.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id: id})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Materia eliminata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Errore durante l\'eliminazione');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function mostraToast(titolo, messaggio, tipo = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const bgColors = {
        'success': 'bg-success',
        'danger': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    
    const icons = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    
    const toastId = 'toast_' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header ${bgColors[tipo]} text-white">
                <i class="bi ${icons[tipo]} me-2"></i>
                <strong class="me-auto">${titolo}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${messaggio}</div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {autohide: false});
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}
</script>

<?php include 'includes/footer.php'; ?>
