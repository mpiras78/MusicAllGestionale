<?php
/**
 * Componente: Statistiche Assenze
 * Da includere in gestione_assenze.php
 * Richiede: $conta (array con statistiche)
 */
if (!isset($conta)) {
    die('Errore: variabile $conta non definita');
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Totale Assenze</p>
                        <h3 class="stat-value"><?= $conta['totale'] ?></h3>
                    </div>
                    <i class="bi bi-calendar-x stat-icon text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Da Allievo</p>
                        <h3 class="stat-value text-warning"><?= $conta['da_allievo'] ?></h3>
                    </div>
                    <i class="bi bi-person-x stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Da Docente</p>
                        <h3 class="stat-value text-info"><?= $conta['da_docente'] ?></h3>
                    </div>
                    <i class="bi bi-person-badge stat-icon text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Con Recupero</p>
                        <h3 class="stat-value text-success"><?= $conta['con_recupero'] ?></h3>
                    </div>
                    <i class="bi bi-check-circle stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>