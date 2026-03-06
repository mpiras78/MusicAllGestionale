<?php
/**
 * Dashboard Component: Quick Actions
 * No dependencies required
 */
?>

<?php
/**
 * Dashboard Component: Quick Actions
 * No dependencies required
 */
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-lightning"></i> Azioni Rapide
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <a href="<?= BASE_URL ?>/gestione_allievi.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-person-plus"></i> Aggiungi Nuovo Allievo
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= BASE_URL ?>/lezioni/add.php" class="btn btn-outline-success w-100">
                    <i class="bi bi-book"></i> Programma Nuova Lezione
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= BASE_URL ?>/gestione_assenze.php" class="btn btn-outline-warning w-100">
                    <i class="bi bi-calendar-x"></i> Registra Assenza
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= BASE_URL ?>/report/statistiche.php" class="btn btn-outline-info w-100">
                    <i class="bi bi-graph-up"></i> Visualizza Report
                </a>
            </div>
        </div>
    </div>
</div>