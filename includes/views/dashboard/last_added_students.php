<?php
/**
 * Dashboard Component: Last Added Students
 * Richiede: $ultimi_allievi (array)
 */
if (!isset($ultimi_allievi)) {
    die('Errore: variabile $ultimi_allievi non definita');
}
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-plus"></i> Ultimi Allievi Aggiunti
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ultimi_allievi)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <p>Nessun allievo registrato</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($ultimi_allievi as $allievo): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= nomeCompleto($allievo['cognome'], $allievo['nome']) ?></h6>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= e($allievo['email'] ?: 'N/D') ?>
                                        </small>
                                    </p>
                                </div>
                                <small class="text-muted"><?= formatDate($allievo['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>