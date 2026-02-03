<?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Settimanale';
$current_page = 'calendario';

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

// Ottieni aule
$aule = $db->query("SELECT * FROM aule WHERE attiva = 1 ORDER BY ordine_visualizzazione");

// Ottieni slot orari
$slots = generaSlotOrari(ORA_INIZIO_SCUOLA, ORA_FINE_SCUOLA, DURATA_SLOT_DEFAULT);

// Ottieni lezioni per il giorno selezionato
$lezioni = $db->query("
    SELECT l.*, 
           CONCAT(al.cognome, ' ', al.nome) as allievo_nome,
           CONCAT(d.cognome, ' ', d.nome) as docente_nome,
           m.nome as materia_nome,
           m.categoria as materia_categoria,
           a.nome as aula_nome,
           a.id as aula_id
    FROM lezioni l
    JOIN allievi al ON l.allievo_id = al.id
    JOIN docenti d ON l.docente_id = d.id
    JOIN materie m ON l.materia_id = m.id
    JOIN aule a ON l.aula_id = a.id
    WHERE l.attiva = 1 
    AND l.giorno_settimana = ?
    ORDER BY l.ora_inizio, a.ordine_visualizzazione
", [$giorno_selezionato]);

// Organizza lezioni per aula e ora
$calendario = [];
foreach ($lezioni as $lezione) {
    $aula_id = $lezione['aula_id'];
    $ora = $lezione['ora_inizio'];
    $calendario[$aula_id][$ora] = $lezione;
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
                                        // Controlla se c'è una lezione in questo slot
                                        $lezione_slot = null;
                                        if (isset($calendario[$aula['id']])) {
                                            foreach ($calendario[$aula['id']] as $ora => $lez) {
                                                if ($ora >= $slot['inizio'] && $ora < $slot['fine']) {
                                                    $lezione_slot = $lez;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if ($lezione_slot): ?>
                                            <div class="lezione-slot tipo-<?= e($lezione_slot['tipo']) ?>" 
                                                 data-lezione-id="<?= $lezione_slot['id'] ?>"
                                                 title="<?= e($lezione_slot['allievo_nome']) ?> - <?= e($lezione_slot['materia_nome']) ?>">
                                                <span class="lezione-allievo">
                                                    <?= e($lezione_slot['allievo_nome']) ?>
                                                </span>
                                                <span class="lezione-materia">
                                                    <?= e($lezione_slot['materia_nome']) ?>
                                                </span>
                                                <span class="lezione-docente">
                                                    <?= e($lezione_slot['docente_nome']) ?>
                                                </span>
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