<?php
require_once 'includes/bootstrap.php';

$auth->requireLogin();

$page_title = 'Gestione Lezioni';
$current_page = 'lezioni';

$lezioniCtrl = new LezioniController();
$sociCtrl = new SociController();
$docentiCtrl = new DocentiController();

$stats = [
    'totale' => $lezioniCtrl->countLezioniSettimana(),
    'per_giorno' => []
];

// Conta lezioni per giorno
$giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];
foreach ($giorni as $giorno) {
    $lezioni_giorno = $lezioniCtrl->getLezioniPerGiorno($giorno, true);
    $stats['per_giorno'][$giorno] = is_countable($lezioni_giorno) ? count($lezioni_giorno) : 0;
}

$lezioni = $lezioniCtrl->getAllLezioni();
$soci = $sociCtrl->getSoci(true);
$docenti = $docentiCtrl->getAllDocenti(true);

$db = Database::getInstance();
$aule = $db->query("SELECT * FROM aule WHERE attiva = 1 ORDER BY ordine_visualizzazione");
$materie = $db->query("SELECT * FROM materie WHERE attiva = 1 ORDER BY nome");

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-week"></i> Gestione Lezioni
            </h1>
            <p class="text-muted mb-0">Gestisci le lezioni settimanali ricorrenti</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-purple" data-bs-toggle="modal" data-bs-target="#addLezioneModal" style="background-color: #9C27B0; color: white;">
                <i class="bi bi-star"></i> Nuova Lezione di Prova
            </button>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stat-card stat-primary">
                <div class="card-body text-center">
                    <h3 class="stat-value mb-1"><?= $stats['totale'] ?></h3>
                    <p class="stat-label mb-0">Totale Lezioni</p>
                </div>
            </div>
        </div>
        <?php 
        $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];
        foreach ($giorni as $giorno): 
            $count = $stats['per_giorno'][$giorno] ?? 0;
        ?>
        <div class="col-md">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h4 class="mb-1"><?= $count ?></h4>
                    <small><?= ucfirst(getGiornoItaliano($giorno)) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtri -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" id="filtroGiorno">
                        <option value="">Tutti i giorni</option>
                        <option value="lunedi">Lunedì</option>
                        <option value="martedi">Martedì</option>
                        <option value="mercoledi">Mercoledì</option>
                        <option value="giovedi">Giovedì</option>
                        <option value="venerdi">Venerdì</option>
                        <option value="sabato">Sabato</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroDocente">
                        <option value="">Tutti i docenti</option>
                        <?php foreach ($docenti as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['cognome'] . ' ' . $d['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroAula">
                        <option value="">Tutte le aule</option>
                        <?php foreach ($aule as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFiltri()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Lezioni -->
    <div class="card">
        <div class="card-header">
                <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Lezioni
                <span class="badge bg-primary ms-2" id="countLezioni"><?= is_countable($lezioni) ? count($lezioni) : 0 ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabellaLezioni">
                    <thead>
                        <tr>
                            <th>Giorno</th>
                            <th>Orario</th>
                            <th>Socio</th>
                            <th>Materia</th>
                            <th>Docente</th>
                            <th>Aula</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lezioni)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Nessuna lezione trovata</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lezioni as $l): ?>
                                <tr data-giorno="<?= e($l['giorno_settimana']) ?>" 
                                    data-docente="<?= $l['docente_id'] ?>" 
                                    data-aula="<?= $l['aula_id'] ?>">
                                    <td>
                                        <span class="badge bg-primary"><?= ucfirst(getGiornoItaliano($l['giorno_settimana'])) ?></span>
                                    </td>
                                    <td><?= formatTime($l['ora_inizio']) ?> - <?= formatTime($l['ora_fine']) ?></td>
                                    <td><?= e($l['socio']) ?></td>
                                    <td><?= e($l['materia']) ?></td>
                                    <td><?= e($l['docente']) ?></td>
                                    <td><?= e($l['aula']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" onclick="modificaLezione(<?= $l['id'] ?>)" title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confermaEliminazione(<?= $l['id'] ?>, '<?= addslashes($l['socio']) ?>')" title="Elimina">
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

<!-- Modal Nuova Lezione -->
<div class="modal fade" id="addLezioneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #9C27B0; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-star"></i> Nuova Lezione di Prova
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Le lezioni di prova sono lezioni singole non legate a iscrizioni
                </div>
                <form id="formNuovaLezione">
                    <h6 class="mb-3">Dati Socio</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" name="nome_socio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cognome *</label>
                            <input type="text" class="form-control" name="cognome_socio" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_socio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefono</label>
                            <input type="tel" class="form-control" name="telefono_socio">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Dettagli Lezione</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data Lezione *</label>
                            <input type="date" class="form-control" name="data_lezione" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Materia *</label>
                            <select class="form-select" name="materia_id" required>
                                <option value="">Seleziona materia</option>
                                <?php foreach ($materie as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= e($m['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Docente *</label>
                            <select class="form-select" name="docente_id" required>
                                <option value="">Seleziona docente</option>
                                <?php foreach ($docenti as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= e($d['cognome'] . ' ' . $d['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Aula *</label>
                            <select class="form-select" name="aula_id" required>
                                <option value="">Seleziona aula</option>
                                <?php foreach ($aule as $au): ?>
                                    <option value="<?= $au['id'] ?>"><?= e($au['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" name="ora_inizio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" name="ora_fine" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn" style="background-color: #9C27B0; color: white;" onclick="salvaLezione()">
                    <i class="bi bi-star"></i> Salva Lezione di Prova
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Lezione -->
<div class="modal fade" id="editLezioneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Modifica Lezione
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formModificaLezione">
                    <input type="hidden" name="id" id="editLezioneId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Socio *</label>
                            <select class="form-select" name="socio_id" id="editSocioId" required>
                                <option value="">Seleziona socio</option>
                                <?php foreach ($soci as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['cognome'] . ' ' . $s['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Materia *</label>
                            <select class="form-select" name="materia_id" id="editMateriaId" required>
                                <option value="">Seleziona materia</option>
                                <?php foreach ($materie as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= e($m['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Docente *</label>
                            <select class="form-select" name="docente_id" id="editDocenteId" required>
                                <option value="">Seleziona docente</option>
                                <?php foreach ($docenti as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= e($d['cognome'] . ' ' . $d['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Aula *</label>
                            <select class="form-select" name="aula_id" id="editAulaId" required>
                                <option value="">Seleziona aula</option>
                                <?php foreach ($aule as $au): ?>
                                    <option value="<?= $au['id'] ?>"><?= e($au['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Giorno *</label>
                            <select class="form-select" name="giorno_settimana" id="editGiorno" required>
                                <option value="">Seleziona giorno</option>
                                <option value="lunedi">Lunedì</option>
                                <option value="martedi">Martedì</option>
                                <option value="mercoledi">Mercoledì</option>
                                <option value="giovedi">Giovedì</option>
                                <option value="venerdi">Venerdì</option>
                                <option value="sabato">Sabato</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" name="ora_inizio" id="editOraInizio" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" name="ora_fine" id="editOraFine" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" id="editNote" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-warning" onclick="aggiornaLezione()">
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
                <p>Sei sicuro di voler eliminare la lezione di <strong id="nomeSocioDaEliminare"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    Questa azione non può essere annullata.
                </div>
                <input type="hidden" id="idLezioneDaEliminare">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminaLezione()">
                    <i class="bi bi-trash"></i> Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $_SESSION['csp_nonce'] ?>">
// Carica docenti quando cambia materia (modal nuova lezione)
document.querySelector('#addLezioneModal select[name="materia_id"]').addEventListener('change', function() {
    const materiaId = this.value;
    const selectDocente = document.querySelector('#addLezioneModal select[name="docente_id"]');
    
    if (!materiaId) {
        selectDocente.innerHTML = '<option value="">Seleziona docente</option>';
        return;
    }
    
    fetch(`<?= BASE_URL ?>/api/api_docenti_per_materia.php?materia_id=${materiaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectDocente.innerHTML = '<option value="">Seleziona docente</option>';
                data.data.forEach(d => {
                    selectDocente.innerHTML += `<option value="${d.id}">${d.cognome} ${d.nome}</option>`;
                });
            }
        })
        .catch(error => console.error('Errore caricamento docenti:', error));
});

// Carica docenti quando cambia materia (modal modifica lezione)
document.getElementById('editMateriaId').addEventListener('change', function() {
    const materiaId = this.value;
    const selectDocente = document.getElementById('editDocenteId');
    const docenteCorrente = selectDocente.value;
    
    if (!materiaId) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>/api/api_docenti_per_materia.php?materia_id=${materiaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectDocente.innerHTML = '<option value="">Seleziona docente</option>';
                data.data.forEach(d => {
                    const selected = d.id == docenteCorrente ? 'selected' : '';
                    selectDocente.innerHTML += `<option value="${d.id}" ${selected}>${d.cognome} ${d.nome}</option>`;
                });
            }
        })
        .catch(error => console.error('Errore caricamento docenti:', error));
});

// Filtri
document.getElementById('filtroGiorno').addEventListener('change', filtroTabella);
document.getElementById('filtroDocente').addEventListener('change', filtroTabella);
document.getElementById('filtroAula').addEventListener('change', filtroTabella);

function filtroTabella() {
    const giorno = document.getElementById('filtroGiorno').value;
    const docente = document.getElementById('filtroDocente').value;
    const aula = document.getElementById('filtroAula').value;
    
    let visibili = 0;
    document.querySelectorAll('#tabellaLezioni tbody tr[data-giorno]').forEach(row => {
        const matchGiorno = !giorno || row.dataset.giorno === giorno;
        const matchDocente = !docente || row.dataset.docente === docente;
        const matchAula = !aula || row.dataset.aula === aula;
        
        if (matchGiorno && matchDocente && matchAula) {
            row.style.display = '';
            visibili++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('countLezioni').textContent = visibili;
}

function resetFiltri() {
    document.getElementById('filtroGiorno').value = '';
    document.getElementById('filtroDocente').value = '';
    document.getElementById('filtroAula').value = '';
    filtroTabella();
}

function salvaLezione() {
    const form = document.getElementById('formNuovaLezione');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_lezioni_prova.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', data: data})
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Lezione di prova creata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function modificaLezione(id) {
    fetch(`<?= BASE_URL ?>/api/api_lezioni.php?action=get&id=${id}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const l = data.data;
                document.getElementById('editLezioneId').value = l.id;
                document.getElementById('editSocioId').value = l.socio_id;
                document.getElementById('editMateriaId').value = l.materia_id;
                document.getElementById('editDocenteId').value = l.docente_id;
                document.getElementById('editAulaId').value = l.aula_id;
                document.getElementById('editGiorno').value = l.giorno_settimana;
                document.getElementById('editOraInizio').value = l.ora_inizio.substr(0, 5);
                document.getElementById('editOraFine').value = l.ora_fine.substr(0, 5);
                document.getElementById('editNote').value = l.note || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editLezioneModal'));
                modal.show();
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function aggiornaLezione() {
    const form = document.getElementById('formModificaLezione');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_lezioni.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', ...data})
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Lezione aggiornata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante l\'aggiornamento');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function confermaEliminazione(id, socio) {
    document.getElementById('nomeSocioDaEliminare').textContent = socio;
    document.getElementById('idLezioneDaEliminare').value = id;
    
    const modal = new bootstrap.Modal(document.getElementById('confermaEliminazioneModal'));
    modal.show();
}

function eliminaLezione() {
    const id = document.getElementById('idLezioneDaEliminare').value;
    
    fetch('<?= BASE_URL ?>/api/api_lezioni.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id: id})
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Lezione eliminata correttamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante l\'eliminazione');
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
