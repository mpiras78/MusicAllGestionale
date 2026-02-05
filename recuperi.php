<?php
/**
 * Pagina Recuperi - Per Docenti
 * Gestione lezioni di recupero
 */

require_once 'includes/bootstrap.php';

// Solo per docenti
$auth->requireRole('docente');

$page_title = 'Recuperi';
$current_page = 'recuperi';

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-plus"></i> Gestione Recuperi
            </h1>
            <p class="text-muted mb-0">Pianifica e gestisci le lezioni di recupero</p>
        </div>
    </div>

    <div class="alert alert-info">
        <h5><i class="bi bi-info-circle-fill"></i> Funzionalità in Sviluppo</h5>
        <p class="mb-0">
            Questa sezione permetterà ai docenti di:
        </p>
        <ul class="mb-0 mt-2">
            <li>Visualizzare lezioni annullate che necessitano di recupero</li>
            <li>Proporre nuove date per le lezioni di recupero</li>
            <li>Gestire il calendario dei recuperi</li>
            <li>Comunicare con la segreteria per l'organizzazione</li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-plus text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">Sezione Recuperi</h4>
            <p class="text-muted">Questa funzionalità sarà implementata prossimamente.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>