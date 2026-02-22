<?php
/**
 * Gestione Assenze - Architettura Modulare
 * View pulita che include componenti separati
 */

require_once 'includes/bootstrap.php';

// Controllers
$assenzeCtrl = new AssenzeController();
$docentiCtrl = new DocentiController();

// Determina ruolo e permessi
$isDocente = $auth->hasRole('docente');
$canCreate = $auth->hasRole(['admin', 'segreteria']);

// Se docente, ottieni ID docente tramite controller
$docente_id = null;
if ($isDocente) {
    $user = $auth->getUser();
    $docente = $docentiCtrl->getDocenteByUserId($user['id']);
    if (!$docente) {
        die('Errore: docente non trovato');
    }
    $docente_id = $docente['id'];
}

// Gestione azioni POST (solo admin/segreteria)
$success = '';
$error = '';

if ($canCreate && isPost()) {
    $action = post('action');
    
    try {
        switch ($action) {
            case 'crea_assenza':
                $data = [
                    'lezione_id' => post('lezione_id'),
                    'data' => post('data'),
                    'causata_da' => post('causata_da'),
                    'necessita_recupero' => post('necessita_recupero') ?? 1,
                    'note_annullamento' => post('note_annullamento')
                ];
                $assenzeCtrl->creaAssenza($data);
                $success = 'Assenza registrata con successo!';
                break;
                
            case 'modifica_assenza':
                $id = post('assenza_id');
                $data_assenza = post('data_assenza');
                $assenzeCtrl->aggiornaDataAssenza($id, $data_assenza);
                $success = 'Data assenza modificata con successo!';
                break;
                
            case 'elimina':
                $id = post('assenza_id');
                $assenzeCtrl->eliminaAssenza($id);
                $success = 'Assenza eliminata';
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Prepara filtri per query
$filters = [];

// Filtro mese: se non specificato, usa il mese corrente come default
if (!isset($_GET['mese']) && empty($_GET)) {
    // Nessun parametro GET = primo caricamento -> usa mese corrente
    $filters['mese'] = (int)date('n');
} elseif (!empty($_GET['mese'])) {
    // Mese specificato dall'utente
    $filters['mese'] = (int)$_GET['mese'];
}
// Se mese = "" (Tutti), non aggiungiamo il filtro

if ($isDocente) {
    $filters['docente_id'] = $docente_id;
} else {
    if (!empty($_GET['docente_id'])) $filters['docente_id'] = $_GET['docente_id'];
    if (!empty($_GET['allievo_id'])) $filters['allievo_id'] = $_GET['allievo_id'];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    if (!empty($_GET['causata_da'])) $filters['causata_da'] = $_GET['causata_da'];
    if (!empty($_GET['stato_recupero'])) $filters['stato_recupero'] = $_GET['stato_recupero'];
}

// Ottieni dati tramite controllers
$assenze = $assenzeCtrl->getAllAssenze($filters);
$conta = $assenzeCtrl->contaAssenze();

// Dati per form/filtri (solo se necessario)
$docenti = $canCreate || !$isDocente ? $assenzeCtrl->getDocenti() : [];
$allievi = $canCreate || !$isDocente ? $assenzeCtrl->getAllievi() : [];

// Aule per modal recupero
if ($canCreate) {
    $auleCtrl = new AuleController();
    $tutte_aule = $auleCtrl->getAllAule() ?: [];
}

// Impostazioni pagina
$page_title = 'Gestione Assenze';
$current_page = 'assenze';

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-x"></i> Gestione Assenze
            </h1>
            <p class="text-muted mb-0">
                <?= $isDocente ? 'Visualizza le assenze dei tuoi allievi' : 'Registra e gestisci le assenze' ?>
            </p>
        </div>
        <?php if ($canCreate): ?>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creaAssenzaModal">
                <i class="bi bi-plus-lg"></i> Nuova Assenza
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Alert Success/Error -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= e($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiche (solo segreteria) -->
    <?php if (!$isDocente): ?>
        <?php require 'includes/views/assenze/statistiche_assenze.php'; ?>
    <?php endif; ?>

    <!-- Filtri (solo segreteria) -->
    <?php if (!$isDocente): ?>
        <?php require 'includes/views/assenze/filtri_assenze.php'; ?>
    <?php endif; ?>

    <!-- Tabella Assenze -->
    <?php require 'includes/views/assenze/tabella_assenze.php'; ?>
</div>

<!-- Modal Crea Assenza (solo segreteria) -->
<?php if ($canCreate): ?>
    <?php require 'includes/views/assenze/modal_crea_assenza.php'; ?>
    
    <!-- JavaScript per modal -->
    <script>
        // Definisci BASE_URL per assenze.js
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>/assets/js/assenze.js"></script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>