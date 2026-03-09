<?php
/**
 * Dashboard Component: Last Added Students
 * Richiede: $ultimi_soci (array)
 */
if (!isset($ultimi_soci)) {
    die('Errore: variabile $ultimi_soci non definita');
}
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-plus"></i> Ultimi Soci Aggiunti
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ultimi_soci)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <p>Nessun socio registrato</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($ultimi_soci as $socio): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= nomeCompleto($socio['cognome'], $socio['nome']) ?></h6>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= e($socio['email'] ?: 'N/D') ?>
                                        </small>
                                    </p>
                                </div>
                                <small class="text-muted"><?= formatDate($socio['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>