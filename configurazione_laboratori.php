<?php
require_once 'includes/bootstrap.php';

$auth->requireLogin();

$controller = new ConfigurazioneCorsiController();
$docentiCtrl = new DocentiController();
$auleCtrl = new AuleController();

$tipiLaboratorio = $controller->getTipiLaboratorio();
$docenti = $docentiCtrl->getAllDocenti();
$aule = $auleCtrl->getAule();

$giorni = ['', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

$pageTitle = 'Configurazione Laboratori';
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> Configurazione Laboratori</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoLaboratorio">
            <i class="bi bi-plus-circle"></i> Nuovo Laboratorio
        </button>
    </div>

    <!-- Tabella Laboratori -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Laboratori Attivi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome Laboratorio</th>
                            <th>Docente</th>
                            <th>Giorno</th>
                            <th>Orario</th>
                            <th>Aula</th>
                            <th>Partecipanti</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tipiLaboratorio as $lab): ?>
                        <tr>
                            <td><strong><?= e($lab['nome']) ?></strong></td>
                            <td>
                                <?php if ($lab['docente_nome']): ?>
                                    <i class="bi bi-person-fill"></i> <?= e($lab['docente_nome']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Non assegnato</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $giorni[$lab['giorno_settimana']] ?></td>
                            <td>
                                <?= date('H:i', strtotime($lab['ora_inizio'])) ?> - 
                                <?= date('H:i', strtotime($lab['ora_fine'])) ?>
                            </td>
                            <td><?= e($lab['aula_nome'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= $lab['num_partecipanti'] ?>
                                    <?php if ($lab['max_partecipanti']): ?>
                                        / <?= $lab['max_partecipanti'] ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($lab['attivo']): ?>
                                    <span class="badge bg-success">Attivo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disattivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="vediPartecipanti(<?= $lab['id'] ?>)" 
                                        title="Partecipanti">
                                    <i class="bi bi-people"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="modificaLaboratorio(<?= $lab['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminaLaboratorio(<?= $lab['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuovo/Modifica Laboratorio -->
<div class="modal fade" id="modalNuovoLaboratorio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Laboratorio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formLaboratorio">
                    <input type="hidden" id="laboratorio_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome Laboratorio *</label>
                        <input type="text" class="form-control" name="nome" required 
                               placeholder="Es: Musica d'insieme">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" name="descrizione" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Docente di Riferimento</label>
                            <select class="form-select" name="docente_id">
                                <option value="">Seleziona docente</option>
                                <?php foreach ($docenti as $doc): ?>
                                    <?php if ($doc['attivo']): ?>
                                    <option value="<?= $doc['id'] ?>">
                                        <?= e($doc['cognome'] . ' ' . $doc['nome']) ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Aula</label>
                            <select class="form-select" name="aula_id">
                                <option value="">Seleziona aula</option>
                                <?php foreach ($aule as $aula): ?>
                                    <?php if ($aula['attiva']): ?>
                                    <option value="<?= $aula['id'] ?>"><?= e($aula['nome']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giorno Settimana *</label>
                            <select class="form-select" name="giorno_settimana" required>
                                <option value="">Seleziona</option>
                                <option value="1">Lunedì</option>
                                <option value="2">Martedì</option>
                                <option value="3">Mercoledì</option>
                                <option value="4">Giovedì</option>
                                <option value="5">Venerdì</option>
                                <option value="6">Sabato</option>
                                <option value="7">Domenica</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ora Inizio *</label>
                            <input type="time" class="form-control" name="ora_inizio" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ora Fine *</label>
                            <input type="time" class="form-control" name="ora_fine" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Partecipanti</label>
                            <input type="number" class="form-control" name="max_partecipanti" 
                                   placeholder="Lascia vuoto per illimitato">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stato</label>
                            <select class="form-select" name="attivo">
                                <option value="1">Attivo</option>
                                <option value="0">Disattivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" onclick="salvaLaboratorio()">Salva</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Partecipanti -->
<div class="modal fade" id="modalPartecipanti" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Partecipanti Laboratorio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="listaPartecipanti">
                <p class="text-center"><i class="bi bi-hourglass-split"></i> Caricamento...</p>
            </div>
        </div>
    </div>
</div>

<script>
function salvaLaboratorio() {
    const form = document.getElementById('formLaboratorio');
    const formData = new FormData(form);
    const id = document.getElementById('laboratorio_id').value;
    
    fetch('api_configurazione_corsi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: id ? 'update' : 'create',
            tipo: 'laboratorio',
            id: id,
            data: Object.fromEntries(formData)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    });
}

function modificaLaboratorio(id) {
    fetch(`api_configurazione_corsi.php?action=get&tipo=laboratorio&id=${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const lab = data.data;
            document.getElementById('laboratorio_id').value = lab.id;
            document.querySelector('[name="nome"]').value = lab.nome;
            document.querySelector('[name="descrizione"]').value = lab.descrizione || '';
            document.querySelector('[name="docente_id"]').value = lab.docente_id || '';
            document.querySelector('[name="aula_id"]').value = lab.aula_id || '';
            document.querySelector('[name="giorno_settimana"]').value = lab.giorno_settimana;
            document.querySelector('[name="ora_inizio"]').value = lab.ora_inizio;
            document.querySelector('[name="ora_fine"]').value = lab.ora_fine;
            document.querySelector('[name="max_partecipanti"]').value = lab.max_partecipanti || '';
            document.querySelector('[name="attivo"]').value = lab.attivo;
            
            new bootstrap.Modal(document.getElementById('modalNuovoLaboratorio')).show();
        }
    });
}

function eliminaLaboratorio(id) {
    if (!confirm('Sei sicuro di voler eliminare questo laboratorio?')) return;
    
    fetch('api_configurazione_corsi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', tipo: 'laboratorio', id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    });
}

function vediPartecipanti(id) {
    fetch(`api_configurazione_corsi.php?action=partecipanti&id=${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const lista = document.getElementById('listaPartecipanti');
            if (data.data.length === 0) {
                lista.innerHTML = '<p class="text-muted">Nessun partecipante iscritto</p>';
            } else {
                lista.innerHTML = '<ul class="list-group">' + 
                    data.data.map(p => `<li class="list-group-item">${p.allievo_nome}</li>`).join('') +
                    '</ul>';
            }
            new bootstrap.Modal(document.getElementById('modalPartecipanti')).show();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
