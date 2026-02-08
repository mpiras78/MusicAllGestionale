<?php
/**
 * Dashboard Component: Quick Actions
 * No dependencies required
 */
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-lightning"></i> Azioni Rapide
            </h5>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/allievi/add.php" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus"></i> Aggiungi Nuovo Allievo
                </a>
                <a href="<?= BASE_URL ?>/lezioni/add.php" class="btn btn-outline-success">
                    <i class="bi bi-book"></i> Programma Nuova Lezione
                </a>
                <a href="<?= BASE_URL ?>/gestione_assenze.php" class="btn btn-outline-warning">
                    <i class="bi bi-calendar-x"></i> Registra Assenza
                </a>
                <a href="<?= BASE_URL ?>/report/statistiche.php" class="btn btn-outline-info">
                    <i class="bi bi-graph-up"></i> Visualizza Report
                </a>
            </div>
        </div>
    </div>
</div>