<?php
/**
 * Dashboard - Architettura Modulare
 * View pulita che include componenti separati
 */

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Inizializza Controllers
$sociCtrl = new SociController();
$docentiCtrl = new DocentiController();
$lezioniCtrl = new LezioniController();
$assenzeCtrl = new AssenzeController();

// Statistiche usando i controller
$stats = [
    'soci' => $sociCtrl->countSoci(),
    'docenti' => $docentiCtrl->countDocenti(),
    'lezioni_settimana' => $lezioniCtrl->countLezioniSettimana(),
    'assenze_mese' => $assenzeCtrl->countAssenzeMeseCorrente()
];

// Determina giorno corrente
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

// Dati per la dashboard usando i controller
$prossime_lezioni = $lezioniCtrl->getProssimeLezioniOggi($giorno_corrente, 5);
$assenze_da_recuperare = $assenzeCtrl->getAssenzeDaRecuperare(10);
$ultimi_soci = $sociCtrl->getUltimiSoci(5);

include 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
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
    <?php require 'includes/views/dashboard/statistics.php'; ?>

    <!-- Azioni Rapide (full width) -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <?php require 'includes/views/dashboard/quick_actions.php'; ?>
        </div>
    </div>

    <!-- Cards -->
    <div class="row g-3">
        <!-- Prossime Lezioni Oggi -->
        <?php require 'includes/views/dashboard/next_lessons.php'; ?>

        <!-- Assenze da Recuperare -->
        <?php require 'includes/views/dashboard/absences.php'; ?>

        <!-- Ultimi Soci Aggiunti -->
        <?php require 'includes/views/dashboard/last_added_students.php'; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>