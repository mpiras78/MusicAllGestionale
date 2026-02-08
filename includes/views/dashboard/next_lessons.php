<?php
/**
 * Dashboard Component: Next Lessons Today
 * Richiede: $prossime_lezioni (array), $giorno_corrente (string)
 */
if (!isset($prossime_lezioni) || !isset($giorno_corrente)) {
    die('Errore: variabili $prossime_lezioni e $giorno_corrente non definite');
}
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock"></i> Prossime Lezioni Oggi (<?= getGiornoItaliano($giorno_corrente) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($prossime_lezioni)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <p>Nessuna lezione programmata per oggi</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($prossime_lezioni as $lezione): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <?= e($lezione['allievo']) ?>
                                        <span class="badge bg-<?= getTipoLezioneBadge($lezione['tipo']) ?>">
                                            <?= e($lezione['tipo']) ?>
                                        </span>
                                    </h6>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <i class="bi bi-journal-text"></i> <?= e($lezione['materia']) ?>
                                            | <i class="bi bi-person"></i> <?= e($lezione['docente']) ?>
                                            | <i class="bi bi-door-open"></i> <?= e($lezione['aula']) ?>
                                        </small>
                                    </p>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    <?= formatTime($lezione['ora_inizio']) ?> - <?= formatTime($lezione['ora_fine']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>