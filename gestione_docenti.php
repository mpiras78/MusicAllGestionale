<?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Gestione Docenti';
$current_page = 'docenti';

// Inizializza Controller
$docentiCtrl = new DocentiController();

// Ottieni statistiche
$stats = $docentiCtrl->getStatisticheLezioni();

// Ottieni tutti i docenti
$docenti = $docentiCtrl->getDocenti(true);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-person-badge"></i> Gestione Docenti
            </h1>
            <p class="text-muted mb-0">Visualizza e gestisci i docenti della scuola</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDocenteModal">
                <i class="bi bi-plus-circle"></i> Nuovo Docente
            </button>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card stat-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Totale Docenti</p>
                            <h3 class="stat-value mb-0"><?= $stats['totale'] ?></h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Con Lezioni</p>
                            <h3 class="stat-value mb-0"><?= $stats['con_lezioni'] ?></h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Senza Lezioni</p>
                            <h3 class="stat-value mb-0"><?= $stats['senza_lezioni'] ?></h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">% Copertura</p>
                            <h3 class="stat-value mb-0"><?= $stats['percentuale'] ?>%</h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchDocenti" class="form-control" placeholder="Cerca per nome, cognome, email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroLezioni">
                        <option value="tutti">Tutti i docenti</option>
                        <option value="con_lezioni">Solo con lezioni</option>
                        <option value="senza_lezioni">Solo senza lezioni</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFiltri()">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filtri
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Docenti -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Docenti
                <span class="badge bg-primary ms-2" id="countDocenti"><?= count($docenti) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabellaDocenti">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th class="text-center">Lezioni</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($docenti)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Nessun docente trovato</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($docenti as $docente): ?>
                                <tr data-docente-id="<?= $docente['id'] ?>" data-has-lezioni="0">
                                    <td><?= e($docente['nome']) ?></td>
                                    <td><?= e($docente['cognome']) ?></td>
                                    <td><?= e($docente['email'] ?: '-') ?></td>
                                    <td><?= e($docente['telefono'] ?: '-') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary lezioni-badge">-</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" onclick="modificaDocente(<?= $docente['id'] ?>)" title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confermaDisattivazione(<?= $docente['id'] ?>, '<?= addslashes($docente['cognome'] . ' ' . $docente['nome']) ?>')" title="Disattiva">
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

<!-- Modal Nuovo Docente -->
<div class="modal fade" id="addDocenteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Nuovo Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuovoDocente">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cognome *</label>
                            <input type="text" class="form-control" name="cognome" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefono</label>
                            <input type="tel" class="form-control" name="telefono">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Indirizzo</label>
                        <input type="text" class="form-control" name="indirizzo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-success" onclick="salvaDocente()">
                    <i class="bi bi-check-circle"></i> Salva Docente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Docente -->
<div class="modal fade" id="editDocenteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Modifica Docente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formModificaDocente">
                    <input type="hidden" name="id" id="editDocenteId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" name="nome" id="editNome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cognome *</label>
                            <input type="text" class="form-control" name="cognome" id="editCognome" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefono</label>
                            <input type="tel" class="form-control" name="telefono" id="editTelefono">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Indirizzo</label>
                        <input type="text" class="form-control" name="indirizzo" id="editIndirizzo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" id="editNote" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-warning" onclick="aggiornaDocente()">
                    <i class="bi bi-check-circle"></i> Aggiorna
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Conferma Disattivazione -->
<div class="modal fade" id="confermaDisattivazioneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Conferma Disattivazione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler disattivare il docente <strong id="nomeDocenteDaDisattivare"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    Il docente non verrà eliminato ma solo disattivato. Potrà essere riattivato in seguito.
                </div>
                <input type="hidden" id="idDocenteDaDisattivare">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-danger" onclick="disattivaDocente()">
                    <i class="bi bi-trash"></i> Disattiva
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Carica info lezioni per ogni docente all'avvio
document.addEventListener('DOMContentLoaded', function() {
    caricaLezioniDocenti();
});

function caricaLezioniDocenti() {
    fetch('<?= BASE_URL ?>/api_get_helpers.php?type=docenti_con_lezioni')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const docentiConLezioni = new Map();
                data.data.forEach(d => {
                    docentiConLezioni.set(d.id, {
                        num_lezioni: d.num_lezioni,
                        materie: d.materie,
                        giorni: d.giorni
                    });
                });
                
                document.querySelectorAll('#tabellaDocenti tbody tr[data-docente-id]').forEach(row => {
                    const docenteId = parseInt(row.dataset.docenteId);
                    const badge = row.querySelector('.lezioni-badge');
                    
                    if (docentiConLezioni.has(docenteId)) {
                        const info = docentiConLezioni.get(docenteId);
                        badge.className = 'badge bg-success lezioni-badge';
                        badge.textContent = info.num_lezioni + ' lezioni';
                        badge.title = `Materie: ${info.materie}\nGiorni: ${info.giorni}`;
                        row.dataset.hasLezioni = '1';
                    } else {
                        badge.className = 'badge bg-secondary lezioni-badge';
                        badge.textContent = 'Nessuna';
                        row.dataset.hasLezioni = '0';
                    }
                });
            }
        })
        .catch(error => console.error('Errore caricamento lezioni:', error));
}

// Filtro ricerca
document.getElementById('searchDocenti').addEventListener('input', function(e) {
    filtroTabella();
});

document.getElementById('filtroLezioni').addEventListener('change', function() {
    filtroTabella();
});

function filtroTabella() {
    const query = document.getElementById('searchDocenti').value.toLowerCase();
    const filtroLezioni = document.getElementById('filtroLezioni').value;
    
    let visibili = 0;
    document.querySelectorAll('#tabellaDocenti tbody tr[data-docente-id]').forEach(row => {
        const nome = row.cells[0].textContent.toLowerCase();
        const cognome = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const hasLezioni = row.dataset.hasLezioni === '1';
        
        const matchQuery = nome.includes(query) || cognome.includes(query) || email.includes(query);
        const matchLezioni = filtroLezioni === 'tutti' || 
                            (filtroLezioni === 'con_lezioni' && hasLezioni) ||
                            (filtroLezioni === 'senza_lezioni' && !hasLezioni);
        
        if (matchQuery && matchLezioni) {
            row.style.display = '';
            visibili++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('countDocenti').textContent = visibili;
}

function resetFiltri() {
    document.getElementById('searchDocenti').value = '';
    document.getElementById('filtroLezioni').value = 'tutti';
    filtroTabella();
}

function salvaDocente() {
    const form = document.getElementById('formNuovoDocente');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api_docenti.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Docente creato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function modificaDocente(id) {
    fetch(`<?= BASE_URL ?>/api_docenti.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const d = data.data;
                document.getElementById('editDocenteId').value = d.id;
                document.getElementById('editNome').value = d.nome;
                document.getElementById('editCognome').value = d.cognome;
                document.getElementById('editEmail').value = d.email || '';
                document.getElementById('editTelefono').value = d.telefono || '';
                document.getElementById('editIndirizzo').value = d.indirizzo || '';
                document.getElementById('editNote').value = d.note || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editDocenteModal'));
                modal.show();
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function aggiornaDocente() {
    const form = document.getElementById('formModificaDocente');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api_docenti.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Docente aggiornato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante l\'aggiornamento');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function confermaDisattivazione(id, nome) {
    document.getElementById('nomeDocenteDaDisattivare').textContent = nome;
    document.getElementById('idDocenteDaDisattivare').value = id;
    
    const modal = new bootstrap.Modal(document.getElementById('confermaDisattivazioneModal'));
    modal.show();
}

function disattivaDocente() {
    const id = document.getElementById('idDocenteDaDisattivare').value;
    
    fetch('<?= BASE_URL ?>/api_docenti.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id: id})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Docente disattivato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante la disattivazione');
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