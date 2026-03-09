<?php
/**
 * Pagina Recuperi - Per Docenti
 * Visualizzazione recuperi programmati ed effettuati
 * Flusso semplificato: nessuna conferma/rifiuto, solo visualizzazione
 */

require_once 'includes/bootstrap.php';

// Solo per docenti
$auth->requireRole('docente');

$recuperiCtrl = new RecuperiController();

// Ottieni ID docente dal user
$user = $auth->getUser();
$db = Database::getInstance();
$docente = $db->queryOne("SELECT * FROM docenti WHERE user_id = ?", [$user['id']]);

if (!$docente) {
    die('Errore: docente non trovato');
}

$docente_id = $docente['id'];

// Recupera dati - Programmati e Effettuati (no conferma/rifiuta)
$recuperi_programmati = $recuperiCtrl->getRecuperiDocente($docente_id, 'confermata') ?: [];
$recuperi_effettuati = $recuperiCtrl->getRecuperiDocente($docente_id, 'completato') ?: [];

// Conta per badge
$conta = [
    'programmati' => count($recuperi_programmati),
    'effettuati' => count($recuperi_effettuati)
];

$page_title = 'Recuperi';
$current_page = 'recuperi';

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-plus"></i> I Miei Recuperi
            </h1>
            <p class="text-muted mb-0">Visualizza i recuperi programmati e completati</p>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card stat-card stat-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Recuperi Programmati</p>
                            <h3 class="stat-value text-success"><?= $conta['programmati'] ?></h3>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card stat-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Recuperi Effettuati</p>
                            <h3 class="stat-value text-secondary"><?= $conta['effettuati'] ?></h3>
                        </div>
                        <div class="stat-icon text-secondary">
                            <i class="bi bi-check-all"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#programmati">
                <i class="bi bi-calendar-check"></i> Programmati
                <?php if ($conta['programmati'] > 0): ?>
                    <span class="badge bg-success ms-1"><?= $conta['programmati'] ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#effettuati">
                <i class="bi bi-check-all"></i> Effettuati
                <?php if ($conta['effettuati'] > 0): ?>
                    <span class="badge bg-secondary ms-1"><?= $conta['effettuati'] ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB: Programmati -->
        <div class="tab-pane fade show active" id="programmati">
            <?php if (empty($recuperi_programmati)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Nessun recupero programmato</h5>
                        <p class="text-muted">Non hai recuperi confermati in programma.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($recuperi_programmati as $rec): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0">
                                        <i class="bi bi-check-circle text-success"></i>
                                        Programmato
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= e($rec['socio']) ?></h5>
                                    <p class="card-text">
                                        <i class="bi bi-music-note"></i> <?= e($rec['materia']) ?><br>
                                        <i class="bi bi-calendar3"></i> <strong><?= formatDate($rec['data_recupero']) ?></strong><br>
                                        <i class="bi bi-clock"></i> <strong><?= formatTime($rec['ora_inizio']) ?> - <?= formatTime($rec['ora_fine']) ?></strong><br>
                                        <i class="bi bi-door-open"></i> <?= e($rec['aula'] ?? 'Da definire') ?>
                                    </p>
                                    
                                    <?php if ($rec['note_segreteria']): ?>
                                        <div class="alert alert-info alert-sm">
                                            <small><strong>Note:</strong> <?= e($rec['note_segreteria']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="badge bg-success">
                                        <i class="bi bi-check-all"></i> Confermato
                                    </div>
                                </div>
                                <div class="card-footer text-muted small">
                                    Recupero di: <?= formatDate($rec['data_assenza_originale']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB: Effettuati -->
        <div class="tab-pane fade" id="effettuati">
            <?php if (empty($recuperi_effettuati)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Nessun recupero effettuato</h5>
                        <p class="text-muted">Lo storico dei recuperi completati apparirà qui.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data Recupero</th>
                                <th>Orario</th>
                                <th>Socio</th>
                                <th>Materia</th>
                                <th>Aula</th>
                                <th>Lezione Originale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recuperi_effettuati as $rec): ?>
                                <tr>
                                    <td><?= formatDate($rec['data_recupero']) ?></td>
                                    <td><?= formatTime($rec['ora_inizio']) ?> - <?= formatTime($rec['ora_fine']) ?></td>
                                    <td><?= e($rec['socio']) ?></td>
                                    <td><?= e($rec['materia']) ?></td>
                                    <td><?= e($rec['aula'] ?? '-') ?></td>
                                    <td class="text-muted small"><?= formatDate($rec['data_assenza_originale']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Box -->
    <div class="card bg-light mt-4">
        <div class="card-body">
            <h6 class="card-title">
                <i class="bi bi-info-circle"></i> Informazioni
            </h6>
            <ul class="mb-0">
                <li>I recuperi vengono programmati dalla segreteria dopo accordi telefonici/whatsapp</li>
                <li>Riceverai notifica dei nuovi recuperi via email (se configurato)</li>
                <li>Le assenze dei tuoi soci sono visualizzabili nel menu <strong>Assenze</strong></li>
                <li>Per modifiche ai recuperi programmati, contatta la segreteria</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>