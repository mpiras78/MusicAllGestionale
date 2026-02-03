<?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Statistiche
$stats = [
    'allievi' => $db->count("SELECT COUNT(*) FROM allievi WHERE attivo = 1"),
    'docenti' => $db->count("SELECT COUNT(*) FROM docenti WHERE attivo = 1"),
    'lezioni_settimana' => $db->count("SELECT COUNT(*) FROM lezioni WHERE attiva = 1"),
    'assenze_mese' => $db->count("SELECT COUNT(*) FROM assenze WHERE MONTH(data_assenza) = MONTH(CURRENT_DATE()) AND YEAR(data_assenza) = YEAR(CURRENT_DATE())")
];

// Prossime lezioni oggi
$oggi_giorno = strtolower(date('l'));
$giorni_mapping = [
    'monday' => 'lunedi',
    'tuesday' => 'martedi',
    'wednesday' => 'mercoledi',
    'thursday' => 'giovedi',
    'friday' => 'venerdi',
    'saturday' => 'sabato',
    'sunday' => 'domenica'
];
$giorno_corrente = $giorni_mapping[$oggi_giorno] ?? 'lunedi';

$prossime_lezioni = $db->query("
    SELECT l.*, 
           CONCAT(al.cognome, ' ', al.nome) as allievo,
           CONCAT(d.cognome, ' ', d.nome) as docente,
           m.nome as materia,
           a.nome as aula
    FROM lezioni l
    JOIN allievi al ON l.allievo_id = al.id
    JOIN docenti d ON l.docente_id = d.id
    JOIN materie m ON l.materia_id = m.id
    JOIN aule a ON l.aula_id = a.id
    WHERE l.attiva = 1 
    AND l.giorno_settimana = ?
    AND l.ora_inizio >= TIME(NOW())
    ORDER BY l.ora_inizio
    LIMIT 5
", [$giorno_corrente]);

// Assenze da recuperare
$assenze_da_recuperare = $db->query("
    SELECT a.*, 
           CONCAT(al.cognome, ' ', al.nome) as allievo,
           CONCAT(d.cognome, ' ', d.nome) as docente
    FROM assenze a
    JOIN allievi al ON a.allievo_id = al.id
    JOIN docenti d ON a.docente_id = d.id
    WHERE a.recuperata = 0 
    AND a.da_recuperare = 1
    ORDER BY a.data_assenza DESC
    LIMIT 10
");

// Ultimi allievi aggiunti
$ultimi_allievi = $db->query("
    SELECT * FROM allievi 
    WHERE attivo = 1 
    ORDER BY created_at DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h1>
            <p class="text-muted mb-0">Benvenuto, <?= e($_SESSION['username']) ?>!</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/calendario.php" class="btn btn-primary">
                <i class="bi bi-calendar-week"></i> Visualizza Calendario
            </a>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card stat-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Allievi Attivi</p>
                            <h3 class="stat-value text-primary"><?= $stats['allievi'] ?></h3>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/allievi/index.php" class="btn btn-sm btn-outline-primary mt-2">
                        Visualizza <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Docenti</p>
                            <h3 class="stat-value text-success"><?= $stats['docenti'] ?></h3>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/docenti/index.php" class="btn btn-sm btn-outline-success mt-2">
                        Visualizza <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Lezioni/Settimana</p>
                            <h3 class="stat-value text-warning"><?= $stats['lezioni_settimana'] ?></h3>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/lezioni/index.php" class="btn btn-sm btn-outline-warning mt-2">
                        Visualizza <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card stat-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Assenze (Mese)</p>
                            <h3 class="stat-value text-danger"><?= $stats['assenze_mese'] ?></h3>
                        </div>
                        <div class="stat-icon text-danger">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/assenze/index.php" class="btn btn-sm btn-outline-danger mt-2">
                        Visualizza <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Prossime Lezioni Oggi -->
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

        <!-- Assenze da Recuperare -->
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
                            <a href="<?= BASE_URL ?>/assenze/index.php" class="btn btn-sm btn-outline-primary">
                                Vedi Tutte <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ultimi Allievi Aggiunti -->
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

        <!-- Quick Actions -->
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
                        <a href="<?= BASE_URL ?>/assenze/add.php" class="btn btn-outline-warning">
                            <i class="bi bi-calendar-x"></i> Registra Assenza
                        </a>
                        <a href="<?= BASE_URL ?>/report/statistiche.php" class="btn btn-outline-info">
                            <i class="bi bi-graph-up"></i> Visualizza Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>