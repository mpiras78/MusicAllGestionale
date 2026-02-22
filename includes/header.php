<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'MusicAll') ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body>

<?php if ($auth->isLoggedIn()): ?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?><?= $auth->hasRole('docente') ? '/calendario.php' : '/index.php' ?>">
            <img src="<?= BASE_URL ?>/assets/img/logo_musicall.png" alt="MusicAll Logo" height="40" class="me-2">
            <?php //<span><?//= APP_NAME </span>?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'allievi' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_allievi.php">
                                <i class="bi bi-people"></i> Allievi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_page === 'docenti' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_docenti.php">
                                <i class="bi bi-person-badge"></i> Docenti
                            </a>
                        </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page ?? '') == 'calendario' ? 'active' : '' ?>" href="<?= BASE_URL ?>/calendario.php">
                        <i class="bi bi-calendar-week"></i> Calendario
                    </a>
                </li>
                
                <?php if ($auth->hasRole('docente')): ?>
                    <!-- Menu Docente -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page ?? '') == 'assenze' ? 'active' : '' ?>" href="<?= BASE_URL ?>/assenze_docente.php">
                            <i class="bi bi-calendar-x"></i> Assenze
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page ?? '') == 'recuperi' ? 'active' : '' ?>" href="<?= BASE_URL ?>/recuperi.php">
                            <i class="bi bi-calendar-plus"></i> Recuperi
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Menu Admin e Segreteria -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestioneDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Gestione
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= ($current_page ?? '') == 'allievi' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_allievi.php">
                                <i class="bi bi-people"></i> Allievi
                            </a></li>
                            <li><a class="dropdown-item <?= ($current_page ?? '') == 'docenti' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_docenti.php">
                                <i class="bi bi-person-badge"></i> Docenti
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/lezioni/index.php">
                                <i class="bi bi-book"></i> Lezioni
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/gestione_iscrizioni.php">
                                <i class="bi bi-card-checklist"></i> Iscrizioni
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/gestione_pagamenti.php">
                                <i class="bi bi-cash-coin"></i> Pagamenti
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/aule/index.php">
                                <i class="bi bi-door-open"></i> Aule
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/materie/index.php">
                                <i class="bi bi-journal-text"></i> Materie
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page ?? '') == 'assenze' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_assenze.php">
                            <i class="bi bi-calendar-x"></i> Assenze
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page ?? '') == 'gestione_recuperi' ? 'active' : '' ?>" href="<?= BASE_URL ?>/gestione_recuperi.php">
                            <i class="bi bi-calendar-check"></i> Recuperi
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-bar-graph"></i> Report
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/report/statistiche.php">
                                <i class="bi bi-graph-up"></i> Statistiche
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/report/presenze.php">
                                <i class="bi bi-check2-circle"></i> Presenze
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/report/docenti.php">
                                <i class="bi bi-person-lines-fill"></i> Report Docenti
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= e($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">
                            <i class="bi bi-person"></i> Profilo
                        </a></li>
                        <?php if ($auth->isAdmin()): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/gestione_utenti.php">
                            <i class="bi bi-people-fill"></i> Gestione Utenti
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings.php">
                            <i class="bi bi-sliders"></i> Impostazioni
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">
                            <i class="bi bi-info-circle"></i> About
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Flash Messages -->
<?php
$flash = getFlashMessage();
if ($flash):
    $alert_type = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info'
    ][$flash['type']] ?? 'info';
?>
<div class="container mt-3">
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<main class="<?= $auth->isLoggedIn() ? 'py-4' : '' ?>">