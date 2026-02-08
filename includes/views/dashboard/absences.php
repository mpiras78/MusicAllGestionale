<?php
/**
 * Dashboard Component: Absences to Recover
 * Richiede: $assenze_da_recuperare (array)
 */
if (!isset($assenze_da_recuperare)) {
    die('Errore: variabile $assenze_da_recuperare non definita');
}
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle"></i> Assenze da Recuperare
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($assenze_da_recuperare)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <p>Nessuna assenza da recuperare</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assenze_da_recuperare as $assenza): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <?= e($assenza['allievo']) ?>
                                        <span class="badge bg-<?= $assenza['tipo'] == 'allievo' ? 'warning' : 'info' ?>">
                                            <?= e($assenza['tipo']) ?>
                                        </span>
                                    </h6>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="bi bi-person-badge"></i> <?= e($assenza['docente']) ?>
                                        </small>
                                    </p>
                                </div>
                                <small class="text-muted"><?= formatDate($assenza['data_assenza']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>/gestione_assenze.php" class="btn btn-sm btn-outline-primary">
                        Vedi Tutte <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>