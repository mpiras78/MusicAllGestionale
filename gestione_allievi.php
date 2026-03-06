<?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Gestione Allievi';
$current_page = 'allievi';

// Inizializza Controller
$allieviCtrl = new AllieviController();

// Ottieni statistiche
$stats = $allieviCtrl->getStatisticheLezioni();

// Ottieni tutti gli allievi
$allievi = $allieviCtrl->getAllievi(true);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-people"></i> Gestione Allievi
            </h1>
            <p class="text-muted mb-0">Visualizza e gestisci gli allievi della scuola</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAllieveModal">
                <i class="bi bi-plus-circle"></i> Nuovo Allievo
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
                            <p class="stat-label mb-1">Totale Allievi</p>
                            <h3 class="stat-value mb-0"><?= $stats['totale'] ?></h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
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
                        <input type="text" id="searchAllievi" class="form-control" placeholder="Cerca per nome, cognome, email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroLezioni">
                        <option value="tutti">Tutti gli allievi</option>
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

    <!-- Tabella Allievi -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Allievi
                <span class="badge bg-primary ms-2" id="countAllievi"><?= count($allievi) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabellaAllievi">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Data Nascita</th>
                            <th class="text-center">Lezioni</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allievi)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Nessun allievo trovato</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allievi as $allievo): ?>
                                <tr data-allievo-id="<?= $allievo['id'] ?>" data-has-lezioni="0">
                                    <td><?= e($allievo['nome']) ?></td>
                                    <td><?= e($allievo['cognome']) ?></td>
                                    <td><?= e($allievo['email'] ?: '-') ?></td>
                                    <td><?= e($allievo['telefono'] ?: '-') ?></td>
                                    <td><?= $allievo['data_nascita'] ? date('d/m/Y', strtotime($allievo['data_nascita'])) : '-' ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary lezioni-badge">-</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" onclick="visualizzaAllievo(<?= $allievo['id'] ?>)" title="Visualizza">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="modificaAllievo(<?= $allievo['id'] ?>)" title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confermaDisattivazione(<?= $allievo['id'] ?>, '<?= addslashes($allievo['cognome'] . ' ' . $allievo['nome']) ?>')" title="Disattiva">
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

<!-- Modal Nuovo Allievo -->
<div class="modal fade" id="addAllieveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Nuovo Allievo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuovoAllievo">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data di Nascita</label>
                            <input type="date" class="form-control" name="data_nascita">
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
                <button type="button" class="btn btn-success" onclick="salvaAllievo()">
                    <i class="bi bi-check-circle"></i> Salva Allievo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Allievo -->
<div class="modal fade" id="editAllieveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Modifica Allievo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formModificaAllievo">
                    <input type="hidden" name="id" id="editAllieveId">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data di Nascita</label>
                            <input type="date" class="form-control" name="data_nascita" id="editDataNascita">
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
                <button type="button" class="btn btn-warning" onclick="aggiornaAllievo()">
                    <i class="bi bi-check-circle"></i> Aggiorna
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizza Allievo -->
<div class="modal fade" id="viewAllieveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle"></i> <span id="viewAllieveNome"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAllieveBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Caricamento...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Chiudi
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
                <p>Sei sicuro di voler disattivare l'allievo <strong id="nomeAllievoDaDisattivare"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    L'allievo non verrà eliminato ma solo disattivato. Potrà essere riattivato in seguito.
                </div>
                <input type="hidden" id="idAllievoDaDisattivare">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-danger" onclick="disattivaAllievo()">
                    <i class="bi bi-trash"></i> Disattiva
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Carica info lezioni per ogni allievo all'avvio
document.addEventListener('DOMContentLoaded', function() {
    caricaLezioniAllievi();
});

function caricaLezioniAllievi() {
    fetch('<?= BASE_URL ?>/api/api_get_helpers.php?type=allievi_con_lezioni')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mappa allievi con lezioni
                const allieviConLezioni = new Map();
                data.data.forEach(a => {
                    allieviConLezioni.set(a.id, {
                        num_lezioni: a.num_lezioni,
                        materie: a.materie,
                        giorni: a.giorni
                    });
                });
                
                // Aggiorna tabella
                document.querySelectorAll('#tabellaAllievi tbody tr[data-allievo-id]').forEach(row => {
                    const allieveId = parseInt(row.dataset.allieveId);
                    const badge = row.querySelector('.lezioni-badge');
                    
                    if (allieviConLezioni.has(allieveId)) {
                        const info = allieviConLezioni.get(allieveId);
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
document.getElementById('searchAllievi').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    filtroTabella();
});

document.getElementById('filtroLezioni').addEventListener('change', function() {
    filtroTabella();
});

function filtroTabella() {
    const query = document.getElementById('searchAllievi').value.toLowerCase();
    const filtroLezioni = document.getElementById('filtroLezioni').value;
    
    let visibili = 0;
    document.querySelectorAll('#tabellaAllievi tbody tr[data-allievo-id]').forEach(row => {
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
    
    document.getElementById('countAllievi').textContent = visibili;
}

function resetFiltri() {
    document.getElementById('searchAllievi').value = '';
    document.getElementById('filtroLezioni').value = 'tutti';
    filtroTabella();
}

function salvaAllievo() {
    const form = document.getElementById('formNuovoAllievo');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_allievi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Allievo creato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function modificaAllievo(id) {
    fetch(`<?= BASE_URL ?>/api/api_allievi.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const a = data.data;
                document.getElementById('editAllieveId').value = a.id;
                document.getElementById('editNome').value = a.nome;
                document.getElementById('editCognome').value = a.cognome;
                document.getElementById('editEmail').value = a.email || '';
                document.getElementById('editTelefono').value = a.telefono || '';
                document.getElementById('editDataNascita').value = a.data_nascita || '';
                document.getElementById('editIndirizzo').value = a.indirizzo || '';
                document.getElementById('editNote').value = a.note || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editAllieveModal'));
                modal.show();
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function aggiornaAllievo() {
    const form = document.getElementById('formModificaAllievo');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_allievi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', ...data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Allievo aggiornato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante l\'aggiornamento');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function visualizzaAllievo(id) {
    const modalBody = document.getElementById('viewAllieveBody');
    const modalNome = document.getElementById('viewAllieveNome');
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Caricamento...</span>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewAllieveModal'));
    modal.show();
    
    fetch(`<?= BASE_URL ?>/api/api_get_info_allievo.php?allievo_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            
            modalNome.textContent = data.allievo.nome_completo;
            
            let html = `
                <!-- Info Anagrafica -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-person"></i> Dati Anagrafici</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"><strong>Email:</strong> ${data.allievo.email || '-'}</div>
                            <div class="col-md-6"><strong>Telefono:</strong> ${data.allievo.telefono || '-'}</div>
                        </div>
                        ${data.allievo.data_nascita || data.allievo.indirizzo ? `
                        <div class="row mt-2">
                            ${data.allievo.data_nascita ? `<div class="col-md-6"><strong>Data Nascita:</strong> ${new Date(data.allievo.data_nascita).toLocaleDateString('it-IT')}</div>` : ''}
                            ${data.allievo.indirizzo ? `<div class="col-md-6"><strong>Indirizzo:</strong> ${data.allievo.indirizzo}</div>` : ''}
                        </div>
                        ` : ''}
                        ${data.allievo.note ? `<div class="row mt-2"><div class="col-12"><strong>Note:</strong> ${data.allievo.note}</div></div>` : ''}
                    </div>
                </div>
                
                <!-- Statistiche -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-calendar-x"></i> Assenze</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Totali:</span>
                                    <span class="badge bg-secondary">${data.statistiche.assenze.totale}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Da Recuperare:</span>
                                    <span class="badge bg-danger">${data.statistiche.assenze.da_recuperare}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Recuperi</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Totali:</span>
                                    <span class="badge bg-secondary">${data.statistiche.recuperi.totale}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Programmati:</span>
                                    <span class="badge bg-primary">${data.statistiche.recuperi.programmati}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Corsi -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-book"></i> Corsi Frequentati</h6>
                    </div>
                    <div class="card-body">
                        ${data.corsi.length > 0 ? `
                            <div class="list-group list-group-flush">
                                ${data.corsi.map(c => `
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><i class="bi bi-music-note"></i> ${c.materia || 'N/D'}</h6>
                                            <small>${c.giorno_settimana}</small>
                                        </div>
                                        <p class="mb-1">
                                            <i class="bi bi-person"></i> ${c.docente}<br>
                                            <i class="bi bi-clock"></i> ${c.ora_inizio.substr(0,5)} - ${c.ora_fine.substr(0,5)}
                                            ${c.aula ? `<br><i class="bi bi-door-open"></i> ${c.aula}` : ''}
                                        </p>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-muted">Nessun corso registrato</p>'}
                    </div>
                </div>
            `;
            
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>
            `;
        });
}

function confermaDisattivazione(id, nome) {
    document.getElementById('nomeAllievoDaDisattivare').textContent = nome;
    document.getElementById('idAllievoDaDisattivare').value = id;
    
    const modal = new bootstrap.Modal(document.getElementById('confermaDisattivazioneModal'));
    modal.show();
}

function disattivaAllievo() {
    const id = document.getElementById('idAllievoDaDisattivare').value;
    
    fetch('<?= BASE_URL ?>/api/api_allievi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id: id})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Allievo disattivato correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Errore durante la disattivazione');
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