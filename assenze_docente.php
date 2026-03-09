<?php
/**
 * Pagina Assenze - Per Docenti
 * Visualizza solo le assenze dei propri soci
 */

require_once 'includes/bootstrap.php';

// Solo per docenti
$auth->requireRole('docente');

$assenzeCtrl = new AssenzeController();

// Ottieni ID docente dal user
$user = $auth->getUser();
$db = Database::getInstance();
$docente = $db->queryOne("SELECT * FROM docenti WHERE user_id = ?", [$user['id']]);

if (!$docente) {
    die('Errore: docente non trovato');
}

$docente_id = $docente['id'];

// Ottieni assenze del docente con filtri
$filters = ['docente_id' => $docente_id];

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['causata_da'])) {
    $filters['causata_da'] = $_GET['causata_da'];
}

$assenze = $assenzeCtrl->getAllAssenze($filters);

// Conta assenze
$stats = [
    'totale' => count($assenze),
    'da_socio' => count(array_filter($assenze, fn($a) => $a['causata_da'] == 'socio')),
    'da_docente' => count(array_filter($assenze, fn($a) => $a['causata_da'] == 'docente')),
    'da_recuperare' => count(array_filter($assenze, fn($a) => $a['necessita_recupero'] == 1 && $a['ha_recupero'] == 0))
];

$page_title = 'Le Mie Assenze';
$current_page = 'assenze';

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-x"></i> Le Mie Assenze
            </h1>
            <p class="text-muted mb-0">Visualizza le assenze dei tuoi soci</p>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card stat-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Totale Assenze</p>
                            <h3 class="stat-value text-primary"><?= $stats['totale'] ?></h3>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Da Socio</p>
                            <h3 class="stat-value text-warning"><?= $stats['da_socio'] ?></h3>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="bi bi-person-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Da Me</p>
                            <h3 class="stat-value text-info"><?= $stats['da_docente'] ?></h3>
                        </div>
                        <div class="stat-icon text-info">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Da Recuperare</p>
                            <h3 class="stat-value text-danger"><?= $stats['da_recuperare'] ?></h3>
                        </div>
                        <div class="stat-icon text-danger">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtri</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cerca Socio</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome o cognome socio..." 
                           value="<?= e($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Causata da</label>
                    <select name="causata_da" class="form-select">
                        <option value="">Tutti</option>
                        <option value="socio" <?= ($_GET['causata_da'] ?? '') == 'socio' ? 'selected' : '' ?>>Socio</option>
                        <option value="docente" <?= ($_GET['causata_da'] ?? '') == 'docente' ? 'selected' : '' ?>>Docente</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtra
                    </button>
                    <a href="assenze_docente.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabella Assenze -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Assenze
                <span class="badge bg-primary ms-2"><?= count($assenze) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($assenze)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <p>Nessuna assenza trovata</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Socio</th>
                                <th>Materia</th>
                                <th>Lezione</th>
                                <th>Causata da</th>
                                <th>Recupero</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assenze as $ass): ?>
                                <tr>
                                    <td><?= formatDate($ass['data']) ?></td>
                                    <td>
                                        <strong><?= e($ass['socio']) ?></strong>
                                    </td>
                                    <td><?= e($ass['materia'] ?? '-') ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= e($ass['giorno_settimana']) ?> 
                                            <?= formatTime($ass['ora_inizio']) ?>-<?= formatTime($ass['ora_fine']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $ass['causata_da'] == 'socio' ? 'warning' : 'info' ?>">
                                            <?= e($ass['causata_da']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ass['ha_recupero'] > 0): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Programmato
                                            </span>
                                        <?php elseif ($ass['necessita_recupero']): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-circle"></i> Da recuperare
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ass['note_annullamento']): ?>
                                            <small class="text-muted" title="<?= e($ass['note_annullamento']) ?>">
                                                <?= e(mb_substr($ass['note_annullamento'], 0, 30)) ?><?= mb_strlen($ass['note_annullamento']) > 30 ? '...' : '' ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>