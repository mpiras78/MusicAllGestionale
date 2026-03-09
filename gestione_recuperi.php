<?php
/**
 * Gestione Recuperi - Semplificato
 * Solo visualizzazione recuperi confermati
 */

require_once 'includes/bootstrap.php';

// Solo per admin e segreteria
$auth->requireRole(['admin', 'segreteria']);

$recuperiCtrl = new RecuperiController();
$auleCtrl = new AuleController();

// Gestione azioni
$success = '';
$error = '';

if (isPost()) {
    $action = post('action');
    
    try {
        switch ($action) {
            case 'crea_recupero':
                $data = [
                    'assenza_id' => post('assenza_id'),
                    'data_recupero' => post('data_recupero'),
                    'ora_inizio' => post('ora_inizio'),
                    'ora_fine' => post('ora_fine'),
                    'aula_id' => post('aula_id'),
                    'note_segreteria' => post('note_segreteria')
                ];
                $recuperiCtrl->creaRecupero($data);
                $success = 'Recupero creato con successo!';
                break;
                
            case 'annulla':
                $recupero_id = post('recupero_id');
                $motivo = post('motivo_annullamento');
                if (empty($motivo)) {
                    throw new Exception('Indica il motivo dell\'annullamento');
                }
                $recuperiCtrl->annullaRecupero($recupero_id, $motivo, $_SESSION['user_id']);
                $success = 'Recupero annullato';
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Ottieni solo recuperi confermati (tutti sono già confermati nel nuovo flusso)
$recuperi = $recuperiCtrl->getRecuperi(null) ?: [];
$tutte_aule = $auleCtrl->getAllAule() ?: [];

// Conta
$conta = $recuperiCtrl->contaRecuperiPerStato() ?: ['proposta' => 0, 'confermata' => 0, 'completato' => 0, 'annullato' => 0];

$page_title = 'Gestione Recuperi';
$current_page = 'gestione_recuperi';

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-check"></i> Gestione Recuperi
            </h1>
            <p class="text-muted mb-0">Visualizza e gestisci i recuperi programmati</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= e($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card stat-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Recuperi Programmati</p>
                            <h3 class="stat-value text-success"><?= count($recuperi) ?></h3>
                        </div>
                        <i class="bi bi-calendar-check stat-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card stat-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Completati</p>
                            <h3 class="stat-value text-secondary"><?= $conta['completato'] ?></h3>
                        </div>
                        <i class="bi bi-check-all stat-icon text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card stat-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Annullati</p>
                            <h3 class="stat-value text-danger"><?= $conta['annullato'] ?></h3>
                        </div>
                        <i class="bi bi-x-circle stat-icon text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Recuperi -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list"></i> Recuperi Programmati
                <span class="badge bg-secondary ms-2"><?= count($recuperi) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($recuperi)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Nessun recupero programmato</h5>
                    <p class="text-muted">Crea recuperi dalla pagina Gestione Assenze</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data Recupero</th>
                                <th>Orario</th>
                                <th>Socio</th>
                                <th>Docente</th>
                                <th>Materia</th>
                                <th>Aula</th>
                                <th>Durata</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recuperi as $rec): ?>
                                <?php
                                // Calcola durata in minuti
                                $ora_inizio = new DateTime($rec['ora_inizio']);
                                $ora_fine = new DateTime($rec['ora_fine']);
                                $durata_minuti = ($ora_fine->getTimestamp() - $ora_inizio->getTimestamp()) / 60;
                                
                                // Determina stato
                                $data_recupero = new DateTime($rec['data_recupero']);
                                $oggi = new DateTime();
                                $is_passato = $data_recupero < $oggi;
                                $is_oggi = $data_recupero->format('Y-m-d') == $oggi->format('Y-m-d');
                                ?>
                                <tr class="<?= $is_oggi ? 'table-warning' : '' ?>">
                                    <td>
                                        <strong><?= formatDate($rec['data_recupero']) ?></strong>
                                        <?php if ($is_oggi): ?>
                                            <span class="badge bg-warning text-dark ms-1">OGGI</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatTime($rec['ora_inizio']) ?> - <?= formatTime($rec['ora_fine']) ?></td>
                                    <td><strong><?= e($rec['socio']) ?></strong></td>
                                    <td><?= e($rec['docente']) ?></td>
                                    <td><?= e($rec['materia']) ?></td>
                                    <td><?= e($rec['aula'] ?? 'Da definire') ?></td>
                                    <td><span class="badge bg-info"><?= $durata_minuti ?>'</span></td>
                                    <td>
                                        <?php if ($rec['annullato']): ?>
                                            <span class="badge bg-danger">Annullato</span>
                                        <?php elseif ($is_passato): ?>
                                            <span class="badge bg-secondary">Completato</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Confermato</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$rec['annullato'] && !$is_passato): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#annullaModal<?= $rec['id'] ?>">
                                                <i class="bi bi-x-lg"></i> Annulla
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal Annulla -->
                                <?php if (!$rec['annullato'] && !$is_passato): ?>
                                <div class="modal fade" id="annullaModal<?= $rec['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Annulla Recupero</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="annulla">
                                                    <input type="hidden" name="recupero_id" value="<?= $rec['id'] ?>">
                                                    
                                                    <p>Stai annullando il recupero per <strong><?= e($rec['socio']) ?></strong></p>
                                                    <p class="text-muted">
                                                        <?= formatDate($rec['data_recupero']) ?> - 
                                                        <?= formatTime($rec['ora_inizio']) ?>-<?= formatTime($rec['ora_fine']) ?>
                                                    </p>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Motivo annullamento *</label>
                                                        <textarea name="motivo_annullamento" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                                                    <button type="submit" class="btn btn-danger">Conferma Annullamento</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>