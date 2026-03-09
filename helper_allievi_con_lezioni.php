<?php
/**
 * Helper: Mostra soci con lezioni per facilitare test assenze
 * Pattern MVC - usa SociController
 */

require_once 'includes/bootstrap.php';

// Usa controller (MVC)
$sociCtrl = new SociController();

// Ottieni dati tramite controller
$soci_con_lezioni = $sociCtrl->getSociConLezioni();
$stats = $sociCtrl->getStatisticheLezioni();

$page_title = 'Helper - Soci con Lezioni';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-info-circle"></i> Helper - Soci con Lezioni
            </h1>
            <p class="text-muted mb-0">Lista soci che hanno lezioni programmate (utilizzabili per creare assenze)</p>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Totale Soci</p>
                            <h3 class="stat-value"><?= $stats['totale'] ?></h3>
                        </div>
                        <i class="bi bi-people stat-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card stat-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Con Lezioni</p>
                            <h3 class="stat-value text-success"><?= $stats['con_lezioni'] ?></h3>
                        </div>
                        <i class="bi bi-check-circle stat-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card stat-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Copertura</p>
                            <h3 class="stat-value text-info"><?= $stats['percentuale'] ?>%</h3>
                        </div>
                        <i class="bi bi-pie-chart stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($stats['con_lezioni'] == 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Attenzione!</strong> Nessun socio ha lezioni programmate nel calendario.
            È necessario aggiungere lezioni prima di poter registrare assenze.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Info:</strong> Solo gli soci in questa lista possono avere assenze registrate, 
            poiché le assenze sono legate alle lezioni programmate nel calendario settimanale.
        </div>
    <?php endif; ?>

    <!-- Tabella Soci con Lezioni -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list-check"></i> Soci con Lezioni Programmate
                <span class="badge bg-primary ms-2"><?= $stats['con_lezioni'] ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($soci_con_lezioni)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Nessun socio con lezioni</h5>
                    <p class="text-muted">Aggiungi lezioni al calendario per poter registrare assenze</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Nome Completo</th>
                                <th width="120" class="text-center">N. Lezioni</th>
                                <th>Materie</th>
                                <th width="150" class="text-center">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($soci_con_lezioni as $all): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= $all['id'] ?></span></td>
                                    <td><strong><?= e($all['nome_completo']) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= $all['num_lezioni'] ?></span>
                                    </td>
                                    <td class="small text-muted"><?= e($all['materie']) ?></td>
                                    <td class="text-center">
                                        <a href="gestione_assenze.php" class="btn btn-sm btn-outline-primary" 
                                           title="Crea assenza per questo socio">
                                            <i class="bi bi-plus-circle"></i> Crea Assenza
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="gestione_assenze.php" class="btn btn-primary">
                        <i class="bi bi-calendar-x"></i> Vai a Gestione Assenze
                    </a>
                    <a href="calendario.php" class="btn btn-outline-secondary">
                        <i class="bi bi-calendar-week"></i> Vai al Calendario
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>