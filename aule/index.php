<?php
require_once '../includes/bootstrap.php';

$auth->requireLogin();

$page_title = 'Aule';
$current_page = 'aule';

$auleCtrl = new AuleController();
$aule = $auleCtrl->getAule();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-door-open"></i> Aule
            </h1>
            <p class="text-muted mb-0">Visualizza le sale disponibili nella scuola</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary fs-6">
                <?= count($aule) ?> <?= count($aule) === 1 ? 'sala' : 'sale' ?> disponibili
            </span>
        </div>
    </div>

    <?php if (empty($aule)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nessuna sala disponibile.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($aule as $index => $aula): ?>
                <?php $color = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'][$index % 6]; ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-<?= $color ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-<?= $color ?> me-3" style="font-size: 2rem;">
                                    <i class="bi bi-door-open-fill"></i>
                                </div>
                                <div>
                                    <h2 class="h5 mb-1"><?= e($aula['nome']) ?></h2>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Disponibile
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($aula['descrizione'])): ?>
                                <p class="card-text text-muted"><?= e($aula['descrizione']) ?></p>
                            <?php else: ?>
                                <p class="card-text text-muted">Sala per lezioni e attivita didattiche.</p>
                            <?php endif; ?>

                            <?php if (!empty($aula['capienza']) || !empty($aula['attrezzature'])): ?>
                                <hr>
                                <?php if (!empty($aula['capienza'])): ?>
                                    <p class="mb-2">
                                        <i class="bi bi-people"></i>
                                        <strong>Capienza:</strong> <?= (int) $aula['capienza'] ?> persone
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($aula['attrezzature'])): ?>
                                    <p class="mb-0">
                                        <i class="bi bi-music-note-beamed"></i>
                                        <strong>Attrezzature:</strong> <?= e($aula['attrezzature']) ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
