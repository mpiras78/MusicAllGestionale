<?php
/**
 * Impostazioni Sistema
 * Solo per amministratori
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Solo admin
$auth->requireRole('admin');

$db = Database::getInstance();
$success = '';
$error = '';

// Carica impostazioni attuali da config.php
$currentSettings = [
    'docente_view_all_calendar' => DOCENTE_VIEW_ALL_CALENDAR,
    'docente_can_edit_lessons' => DOCENTE_CAN_EDIT_LESSONS,
    'docente_can_view_absences' => DOCENTE_CAN_VIEW_ABSENCES,
    'docente_can_view_students' => DOCENTE_CAN_VIEW_STUDENTS,
];

// Gestione salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Leggi config.php
        $configFile = __DIR__ . '/../config/config.php';
        $configContent = file_get_contents($configFile);
        
        // Aggiorna valori
        $viewAll = isset($_POST['docente_view_all_calendar']) ? 'true' : 'false';
        $canEdit = isset($_POST['docente_can_edit_lessons']) ? 'true' : 'false';
        $viewAbsences = isset($_POST['docente_can_view_absences']) ? 'true' : 'false';
        $viewStudents = isset($_POST['docente_can_view_students']) ? 'true' : 'false';
        
        // Sostituisci valori nel file
        $configContent = preg_replace(
            "/define\('DOCENTE_VIEW_ALL_CALENDAR',\s*(true|false)\);/",
            "define('DOCENTE_VIEW_ALL_CALENDAR', $viewAll);",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('DOCENTE_CAN_EDIT_LESSONS',\s*(true|false)\);/",
            "define('DOCENTE_CAN_EDIT_LESSONS', $canEdit);",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('DOCENTE_CAN_VIEW_ABSENCES',\s*(true|false)\);/",
            "define('DOCENTE_CAN_VIEW_ABSENCES', $viewAbsences);",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('DOCENTE_CAN_VIEW_STUDENTS',\s*(true|false)\);/",
            "define('DOCENTE_CAN_VIEW_STUDENTS', $viewStudents);",
            $configContent
        );
        
        // Salva file
        if (file_put_contents($configFile, $configContent)) {
            $success = 'Impostazioni salvate con successo!';
            
            // Aggiorna variabili correnti
            $currentSettings = [
                'docente_view_all_calendar' => $viewAll === 'true',
                'docente_can_edit_lessons' => $canEdit === 'true',
                'docente_can_view_absences' => $viewAbsences === 'true',
                'docente_can_view_students' => $viewStudents === 'true',
            ];
        } else {
            $error = 'Impossibile salvare le impostazioni';
        }
        
    } catch (Exception $e) {
        $error = 'Errore: ' . $e->getMessage();
    }
}

$pageTitle = 'Impostazioni Sistema';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="list-group">
                <a href="#permessi" class="list-group-item list-group-item-action active">
                    <i class="bi bi-shield-check"></i> Permessi Docenti
                </a>
                <a href="#" class="list-group-item list-group-item-action disabled">
                    <i class="bi bi-envelope"></i> Email (Prossimamente)
                </a>
                <a href="#" class="list-group-item list-group-item-action disabled">
                    <i class="bi bi-lock"></i> Sicurezza (Prossimamente)
                </a>
                <a href="#" class="list-group-item list-group-item-action disabled">
                    <i class="bi bi-palette"></i> Aspetto (Prossimamente)
                </a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2><i class="bi bi-sliders"></i> Impostazioni Sistema</h2>
            <p class="text-muted">Configura i permessi e le impostazioni globali dell'applicazione</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Sezione Permessi Docenti -->
            <div class="card mb-4" id="permessi">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Permessi Ruolo Docente</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Configura cosa possono fare gli utenti con ruolo <strong>Docente</strong> quando accedono al sistema.
                    </p>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Visualizzazione Calendario -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-calendar-week text-primary"></i> Visualizzazione Calendario
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="docente_view_all_calendar" 
                                                id="viewAll"
                                                <?= $currentSettings['docente_view_all_calendar'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="viewAll">
                                                <strong>Vede tutto il calendario</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <strong>✅ Attivo:</strong> Il docente vede tutte le lezioni<br>
                                            <strong>❌ Disattivo:</strong> Il docente vede solo le proprie lezioni
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Modifica Lezioni -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-pencil text-warning"></i> Modifica Lezioni
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="docente_can_edit_lessons" 
                                                id="canEdit"
                                                <?= $currentSettings['docente_can_edit_lessons'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="canEdit">
                                                <strong>Può modificare lezioni</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <strong>✅ Attivo:</strong> Il docente può creare/modificare lezioni<br>
                                            <strong>❌ Disattivo:</strong> Il docente ha accesso solo in lettura
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Visualizzazione Assenze -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-calendar-x text-danger"></i> Visualizzazione Assenze
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="docente_can_view_absences" 
                                                id="viewAbsences"
                                                <?= $currentSettings['docente_can_view_absences'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="viewAbsences">
                                                <strong>Può vedere assenze</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <strong>✅ Attivo:</strong> Il docente vede assenze dei propri soci<br>
                                            <strong>❌ Disattivo:</strong> Il docente non vede la sezione assenze
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Visualizzazione Soci -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-people text-success"></i> Visualizzazione Soci
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="docente_can_view_students" 
                                                id="viewStudents"
                                                <?= $currentSettings['docente_can_view_students'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="viewStudents">
                                                <strong>Può vedere soci</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <strong>✅ Attivo:</strong> Il docente vede lista dei propri soci<br>
                                            <strong>❌ Disattivo:</strong> Il docente non vede la sezione soci
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    Le modifiche saranno applicate immediatamente per tutti i docenti
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Salva Impostazioni
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Info Configurazione -->
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle-fill"></i> Come Funziona</h6>
                <p class="mb-0">
                    Le impostazioni vengono salvate nel file <code>config/config.php</code> e si applicano 
                    a <strong>tutti gli utenti con ruolo Docente</strong>. Per permessi personalizzati per 
                    singolo docente, sarà necessario implementare il sistema avanzato (Opzione B).
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>