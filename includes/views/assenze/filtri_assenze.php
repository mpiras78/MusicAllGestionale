<?php
/**
 * Componente: Filtri Assenze
 * Da includere in gestione_assenze.php
 * Richiede: $docenti, $allievi (arrays)
 */
if (!isset($docenti) || !isset($allievi)) {
    die('Errore: variabili $docenti e $allievi non definite');
}
?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtri</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cerca</label>
                <input type="text" name="search" class="form-control" 
                       value="<?= e($_GET['search'] ?? '') ?>" 
                       placeholder="Nome allievo o docente">
            </div>
            <div class="col-md-3">
                <label class="form-label">Docente</label>
                <select name="docente_id" class="form-select">
                    <option value="">Tutti</option>
                    <?php foreach ($docenti as $doc): ?>
                        <option value="<?= $doc['id'] ?>" <?= ($_GET['docente_id'] ?? '') == $doc['id'] ? 'selected' : '' ?>>
                            <?= e($doc['nome_completo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Allievo</label>
                <select name="allievo_id" class="form-select">
                    <option value="">Tutti</option>
                    <?php foreach ($allievi as $all): ?>
                        <option value="<?= $all['id'] ?>" <?= ($_GET['allievo_id'] ?? '') == $all['id'] ? 'selected' : '' ?>>
                            <?= e($all['nome_completo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Causata da</label>
                <select name="causata_da" class="form-select">
                    <option value="">Tutti</option>
                    <option value="allievo" <?= ($_GET['causata_da'] ?? '') == 'allievo' ? 'selected' : '' ?>>Allievo</option>
                    <option value="docente" <?= ($_GET['causata_da'] ?? '') == 'docente' ? 'selected' : '' ?>>Docente</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        <?php if (!empty($_GET)): ?>
        <div class="mt-2">
            <a href="gestione_assenze.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Reset Filtri
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>