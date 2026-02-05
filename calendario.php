<?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Settimanale';
$current_page = 'calendario';

// Inizializza Controllers
$lezioniCtrl = new LezioniController();
$auleCtrl = new AuleController();

// Giorno selezionato (default: giorno corrente)
$giorno_selezionato = get('giorno', '');
if (empty($giorno_selezionato)) {
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
    $giorno_selezionato = $giorni_mapping[$oggi_giorno] ?? 'lunedi';
}

// Ottieni aule tramite controller
$aule = $auleCtrl->getAule();

// Ottieni slot orari (default 15 minuti)
$slots = generaSlotOrari(ORA_INIZIO_SCUOLA, ORA_FINE_SCUOLA, DURATA_SLOT_DEFAULT);

// Ottieni lezioni per il giorno selezionato tramite controller
// Se docente e configurazione lo richiede, filtra solo sue lezioni
if ($auth->hasRole('docente') && !DOCENTE_VIEW_ALL_CALENDAR) {
    // Ottieni istanza database
    $db = Database::getInstance();
    
    // Ottieni ID docente dell'utente loggato
    $docente_id = $db->queryOne(
        "SELECT id FROM docenti WHERE user_id = ?", 
        [$auth->getUserId()]
    );
    
    if ($docente_id) {
        $lezioni = $lezioniCtrl->getLezioniPerDocenteEGiorno($docente_id['id'], $giorno_selezionato);
    } else {
        $lezioni = []; // Nessuna lezione se docente non collegato
    }
} else {
    $lezioni = $lezioniCtrl->getLezioniPerGiorno($giorno_selezionato);
}

// Organizza lezioni per aula e ora
$calendario = [];
foreach ($lezioni as $lezione) {
    $aula_id = $lezione['aula_id'];
    $ora = $lezione['ora_inizio'];
    $calendario[$aula_id][$ora] = $lezione;
}

// Funzione per calcolare quanti slot occupa una lezione
function calcolaRowspan($ora_inizio, $ora_fine, $slots) {
    $count = 0;
    foreach ($slots as $slot) {
        if ($slot['inizio'] >= $ora_inizio && $slot['inizio'] < $ora_fine) {
            $count++;
        }
    }
    return max(1, $count);
}

// Mappa icone strumenti (Bootstrap Icons)
function getIconaMateria($materia) {
    $materia_lower = strtolower($materia);
    if (strpos($materia_lower, 'chitar') !== false) return 'bi-music-note-beamed';
    if (strpos($materia_lower, 'piano') !== false) return 'bi-piano';
    if (strpos($materia_lower, 'canto') !== false) return 'bi-mic';
    if (strpos($materia_lower, 'batter') !== false) return 'bi-disc';
    if (strpos($materia_lower, 'basso') !== false) return 'bi-soundwave';
    if (strpos($materia_lower, 'violino') !== false) return 'bi-violin';
    if (strpos($materia_lower, 'sax') !== false) return 'bi-trumpet';
    return 'bi-music-note';
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-week"></i> Calendario Settimanale
            </h1>
            <p class="text-muted mb-0">Visualizzazione programmazione giornaliera</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addLezioneModal">
                <i class="bi bi-plus-circle"></i> Nuova Lezione
            </button>
            <button class="btn btn-outline-primary btn-print">
                <i class="bi bi-printer"></i> Stampa
            </button>
        </div>
    </div>

    <!-- Selezione Giorno -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0">
                        <strong><i class="bi bi-calendar3"></i> Seleziona Giorno:</strong>
                    </label>
                </div>
                <div class="col-auto">
                    <select name="giorno" id="selectGiorno" class="form-select" onchange="this.form.submit()">
                        <?php foreach (GIORNI_SETTIMANA as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $giorno_selezionato == $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <span class="badge bg-primary fs-6">
                        <?= count($lezioni) ?> lezioni programmate
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendario -->
    <div class="card">
        <div class="card-body p-0">
            <div class="calendario-container">
                <table class="table table-bordered calendario-table mb-0">
                    <thead>
                        <tr>
                            <th class="time-col">Orario</th>
                            <?php foreach ($aule as $aula): ?>
                                <th class="aula-header">
                                    <i class="bi bi-door-open"></i> <?= e($aula['nome']) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $slot): ?>
                            <tr>
                                <td class="time-col text-center">
                                    <strong><?= $slot['inizio'] ?></strong><br>
                                    <small class="text-muted"><?= $slot['fine'] ?></small>
                                </td>
                                <?php foreach ($aule as $aula): ?>
                                    <td class="calendario-cell" 
                                        data-aula-id="<?= $aula['id'] ?>" 
                                        data-ora="<?= $slot['inizio'] ?>">
                                        <?php
                                        // Trova lezione che INIZIA in questo slot o prima della fine dello slot
                                        $lezione_slot = null;
                                        if (isset($calendario[$aula['id']])) {
                                            $slot_start = strtotime($slot['inizio']);
                                            $slot_end = strtotime($slot['fine']);
                                            
                                            foreach ($calendario[$aula['id']] as $ora => $lez) {
                                                $lezione_start = strtotime($ora);
                                                // La lezione inizia nello slot se:
                                                // - inizia esattamente all'inizio dello slot
                                                // - inizia dopo l'inizio ma prima della fine dello slot
                                                if ($lezione_start >= $slot_start && $lezione_start < $slot_end) {
                                                    $lezione_slot = $lez;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if ($lezione_slot): 
                                            $icona = getIconaMateria($lezione_slot['materia']);
                                        ?>
                                            <div class="lezione-slot tipo-<?= e($lezione_slot['tipo']) ?>" 
                                                 data-lezione-id="<?= $lezione_slot['id'] ?>"
                                                 title="<?= e($lezione_slot['allievo']) ?> - <?= e($lezione_slot['materia']) ?>">
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($lezione_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($lezione_slot['ora_fine'])) ?>
                                                </div>
                                                <div class="lezione-header">
                                                    <i class="bi <?= $icona ?> icona-strumento"></i>
                                                    <span class="lezione-allievo">
                                                        <?= e($lezione_slot['allievo']) ?>
                                                    </span>
                                                </div>
                                                <div class="lezione-info-row">
                                                    <div class="lezione-docente">
                                                        <i class="bi bi-person-fill"></i> <?= e($lezione_slot['docente']) ?>
                                                    </div>
                                                    <div class="lezione-materia-inline">
                                                        <?= e($lezione_slot['materia']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legenda -->
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="card-title"><i class="bi bi-info-circle"></i> Legenda</h6>
            <div class="row g-2">
                <div class="col-auto">
                    <span class="badge" style="background-color: #e3f2fd; color: #333; border-left: 3px solid #2196f3;">
                        Regolare
                    </span>
                </div>
                <div class="col-auto">
                    <span class="badge" style="background-color: #fff3e0; color: #333; border-left: 3px solid #ff9800;">
                        Custom
                    </span>
                </div>
                <div class="col-auto">
                    <span class="badge" style="background-color: #e8f5e9; color: #333; border-left: 3px solid #4caf50;">
                        Recupero
                    </span>
                </div>
                <div class="col-auto">
                    <span class="badge" style="background-color: #f3e5f5; color: #333; border-left: 3px solid #9c27b0;">
                        Laboratorio
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Lezione (placeholder) -->
<div class="modal fade" id="addLezioneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Nuova Lezione
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Funzionalità in sviluppo. Usa la sezione "Gestione > Lezioni" per aggiungere nuove lezioni.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <a href="<?= BASE_URL ?>/lezioni/add.php" class="btn btn-primary">
                    Vai a Gestione Lezioni
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = '<script>
$(document).ready(function() {
    // Click su lezione per mostrare dettagli
    $(".lezione-slot").on("click", function() {
        var lezioneId = $(this).data("lezione-id");
        // TODO: Implementare modal dettagli lezione
        alert("Dettagli lezione ID: " + lezioneId + "\n(Funzionalità in sviluppo)");
    });
});
</script>';

include 'includes/footer.php'; 
?>