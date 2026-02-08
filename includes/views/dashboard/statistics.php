<?php
/**
 * Dashboard Component: Statistics Cards
 * Richiede: $stats (array)
 */
if (!isset($stats)) {
    die('Errore: variabile $stats non definita');
}
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card stat-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Allievi Attivi</p>
                        <h3 class="stat-value text-primary"><?= $stats['allievi'] ?></h3>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/allievi/index.php" class="btn btn-sm btn-outline-primary mt-2">
                    Visualizza <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card stat-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Docenti</p>
                        <h3 class="stat-value text-success"><?= $stats['docenti'] ?></h3>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/docenti/index.php" class="btn btn-sm btn-outline-success mt-2">
                    Visualizza <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card stat-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Lezioni/Settimana</p>
                        <h3 class="stat-value text-warning"><?= $stats['lezioni_settimana'] ?></h3>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/lezioni/index.php" class="btn btn-sm btn-outline-warning mt-2">
                    Visualizza <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card stat-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Assenze (Mese)</p>
                        <h3 class="stat-value text-danger"><?= $stats['assenze_mese'] ?></h3>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/gestione_assenze.php" class="btn btn-sm btn-outline-danger mt-2">
                    Visualizza <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>