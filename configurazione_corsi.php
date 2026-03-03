<?php
require_once 'includes/bootstrap.php';

$auth->requireLogin();

$controller = new ConfigurazioneCorsiController();
$tipiCorso = $controller->getTipiCorso();
$tipologieLaboratorio = $controller->getTipologieLaboratorio();

$pageTitle = 'Configurazione Corsi';
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear-fill"></i> Configurazione Corsi</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoCorso">
            <i class="bi bi-plus-circle"></i> Nuovo Tipo Corso
        </button>
    </div>

    <!-- Tabella Tipi Corso -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tipi di Corso</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome Corso</th>
                            <th>Durata</th>
                            <th>Costo Mensile</th>
                            <th>Laboratorio Incluso</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tipiCorso as $corso): ?>
                        <tr>
                            <td><strong><?= e($corso['nome']) ?></strong></td>
                            <td><?= $corso['durata_lezione'] ?> min</td>
                            <td><strong>€ <?= number_format($corso['costo_mensile'], 2, ',', '.') ?></strong></td>
                            <td>
                                <?php if ($corso['include_laboratorio']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> <?= e($corso['tipologia_nome'] ?? 'Sì') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($corso['attivo']): ?>
                                    <span class="badge bg-success">Attivo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disattivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="modificaCorso(<?= $corso['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminaCorso(<?= $corso['id'] ?>)">
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

<!-- Modal Nuovo/Modifica Corso -->
<div class="modal fade" id="modalNuovoCorso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Tipo Corso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCorso">
                    <input type="hidden" id="corso_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome Corso *</label>
                        <input type="text" class="form-control" name="nome" required 
                               placeholder="Es: Base 45min">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durata Lezione *</label>
                            <select class="form-select" name="durata_lezione" required>
                                <option value="45">45 minuti</option>
                                <option value="60">60 minuti</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo Mensile *</label>
                            <input type="number" class="form-control" name="costo_mensile" 
                                   step="0.01" min="0" required placeholder="80.00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_laboratorio" 
                                   name="include_laboratorio" value="1">
                            <label class="form-check-label" for="include_laboratorio">
                                Include Laboratorio
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="div_laboratorio" style="display:none;">
                        <label class="form-label">Tipologia Laboratorio</label>
                        <select class="form-select" name="tipologia_laboratorio_id">
                            <option value="">Seleziona tipologia</option>
                            <?php foreach ($tipologieLaboratorio as $tip): ?>
                                <option value="<?= $tip['id'] ?>"><?= e($tip['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" name="descrizione" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ordine Visualizzazione</label>
                            <input type="number" class="form-control" name="ordine_visualizzazione" value="0">
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
                <button type="button" class="btn btn-primary" onclick="salvaCorso()">Salva</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle laboratorio select
document.getElementById('include_laboratorio').addEventListener('change', function() {
    document.getElementById('div_laboratorio').style.display = this.checked ? 'block' : 'none';
});

function salvaCorso() {
    const form = document.getElementById('formCorso');
    const formData = new FormData(form);
    const id = document.getElementById('corso_id').value;
    
    fetch('api_configurazione_corsi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: id ? 'update' : 'create',
            tipo: 'corso',
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

function modificaCorso(id) {
    fetch(`api_configurazione_corsi.php?action=get&tipo=corso&id=${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const corso = data.data;
            document.getElementById('corso_id').value = corso.id;
            document.querySelector('[name="nome"]').value = corso.nome;
            document.querySelector('[name="durata_lezione"]').value = corso.durata_lezione;
            document.querySelector('[name="costo_mensile"]').value = corso.costo_mensile;
            document.querySelector('[name="include_laboratorio"]').checked = corso.include_laboratorio == 1;
            document.querySelector('[name="tipologia_laboratorio_id"]').value = corso.tipologia_laboratorio_id || '';
            document.querySelector('[name="descrizione"]').value = corso.descrizione || '';
            document.querySelector('[name="ordine_visualizzazione"]').value = corso.ordine_visualizzazione;
            document.querySelector('[name="attivo"]').value = corso.attivo;
            
            document.getElementById('div_laboratorio').style.display = corso.include_laboratorio ? 'block' : 'none';
            
            new bootstrap.Modal(document.getElementById('modalNuovoCorso')).show();
        }
    });
}

function eliminaCorso(id) {
    if (!confirm('Sei sicuro di voler eliminare questo tipo di corso?')) return;
    
    fetch('api_configurazione_corsi.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', tipo: 'corso', id: id})
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
</script>

<?php include 'includes/footer.php'; ?>
