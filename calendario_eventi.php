<?php
/**
 * Calendario Settimanale - Versione Eventi
 * Usa la nuova API eventi_calendario invece di lezioni
 */

require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Eventi';
$current_page = 'calendario';

// Inizializza Controllers
$auleCtrl = new AuleController();

// Gestione settimana - Supporta sia GET che POST
$settimana_offset = isset($_POST['settimana']) ? (int)$_POST['settimana'] : (int)get('settimana', 0);

// Calcola lunedì della settimana selezionata
$oggi = new DateTime();
$oggi->modify("this week monday");
if ($settimana_offset != 0) {
    $oggi->modify(($settimana_offset > 0 ? '+' : '') . $settimana_offset . ' weeks');
}

// Genera array giorni settimana con date
$giorni_settimana = [];
$giorno_corrente = clone $oggi;
$giorni_nomi = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
$giorni_keys = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];

for ($i = 0; $i < 6; $i++) {
    $data_corrente = $giorno_corrente->format('Y-m-d');
    $festivita = isFestivitaItaliana($data_corrente);
    
    $giorni_settimana[] = [
        'nome' => $giorni_nomi[$i],
        'key' => $giorni_keys[$i],
        'data' => $data_corrente,
        'data_display' => $giorno_corrente->format('d/m'),
        'is_today' => $data_corrente == date('Y-m-d'),
        'festivita' => $festivita
    ];
    $giorno_corrente->modify('+1 day');
}

// Giorno selezionato
$giorno_selezionato = isset($_POST['giorno']) ? $_POST['giorno'] : get('giorno', '');
if (empty($giorno_selezionato)) {
    $oggi_key = strtolower(date('l'));
    $mapping = [
        'monday' => 'lunedi', 'tuesday' => 'martedi', 'wednesday' => 'mercoledi',
        'thursday' => 'giovedi', 'friday' => 'venerdi', 'saturday' => 'sabato'
    ];
    $giorno_selezionato = $mapping[$oggi_key] ?? 'lunedi';
    
    if ($oggi_key == 'sunday' || $settimana_offset != 0) {
        $giorno_selezionato = 'lunedi';
    }
}

// Trova data del giorno selezionato
$data_selezionata = '';
$giorno_festivita = false;
foreach ($giorni_settimana as $g) {
    if ($g['key'] == $giorno_selezionato) {
        $data_selezionata = $g['data'];
        $giorno_festivita = $g['festivita'];
        break;
    }
}

// Ottieni aule
$aule = $auleCtrl->getAule();

// Ottieni slot orari
$slots = generaSlotOrari(ORA_INIZIO_SCUOLA, ORA_FINE_SCUOLA, DURATA_SLOT_DEFAULT);

// **NOVITÀ: Usa API Eventi invece di LezioniController**
use MusicAll\Models\EventoCalendario;

$eventi = [];
if ($data_selezionata) {
    try {
        $query = EventoCalendario::with(['tipologia', 'aula', 'docente', 'materia', 'socio'])
            ->where('attivo', true);
        
        // Eventi ricorrenti per questo giorno
        $giorno_key_map = [
            'Monday' => 'lunedi', 'Tuesday' => 'martedi', 'Wednesday' => 'mercoledi',
            'Thursday' => 'giovedi', 'Friday' => 'venerdi', 'Saturday' => 'sabato'
        ];
        $giorno_db = $giorno_key_map[date('l', strtotime($data_selezionata))] ?? 'lunedi';
        
        $eventi_ricorrenti = EventoCalendario::with(['tipologia', 'aula', 'docente', 'materia', 'socio'])
            ->where('attivo', true)
            ->where('ricorrente', true)
            ->where('giorno_settimana', $giorno_db)
            ->where(function($q) use ($data_selezionata) {
                $q->whereNull('data_inizio')
                  ->orWhere('data_inizio', '<=', $data_selezionata);
            })
            ->where(function($q) use ($data_selezionata) {
                $q->whereNull('data_fine')
                  ->orWhere('data_fine', '>=', $data_selezionata);
            })
            ->get();
        
        // Eventi singoli per questa data
        $eventi_singoli = EventoCalendario::with(['tipologia', 'aula', 'docente', 'materia', 'socio'])
            ->where('attivo', true)
            ->where('ricorrente', false)
            ->where('data_evento', $data_selezionata)
            ->get();
        
        // Unisci e trasforma
        $eventi_collection = $eventi_ricorrenti->merge($eventi_singoli);
        
        // Se docente e configurazione lo richiede, filtra
        if ($auth->hasRole('docente') && !DOCENTE_VIEW_ALL_CALENDAR) {
            $docentiCtrl = new DocentiController();
            $docente = $docentiCtrl->getDocenteByUserId($auth->getUserId());
            if ($docente) {
                $eventi_collection = $eventi_collection->where('docente_id', $docente['id']);
            }
        }
        
        $eventi = $eventi_collection->map(function($ev) {
            return [
                'id' => $ev->id,
                'tipologia_nome' => $ev->tipologia->nome ?? 'N/D',
                'tipologia_categoria' => $ev->tipologia->categoria ?? 'ALTRO',
                'colore_bg' => $ev->tipologia->colore_bg ?? '#f8f9fa',
                'colore_border' => $ev->tipologia->colore_border ?? '#dee2e6',
                'icona' => $ev->tipologia->icona ?? '📅',
                'ricorrente' => $ev->ricorrente,
                'giorno_settimana' => $ev->giorno_settimana,
                'ora_inizio' => $ev->ora_inizio,
                'ora_fine' => $ev->ora_fine,
                'aula_id' => $ev->aula_id,
                'aula_nome' => $ev->aula->nome ?? 'N/D',
                'docente_id' => $ev->docente_id,
                'docente_nome' => $ev->docente ? ($ev->docente->cognome . ' ' . $ev->docente->nome) : 'N/D',
                'materia_id' => $ev->materia_id,
                'materia_nome' => $ev->materia->nome ?? null,
                'socio_id' => $ev->socio_id,
                'partecipante_nome' => $ev->partecipante,
                'titolo' => $ev->titolo,
                'descrizione' => $ev->descrizione,
                'note' => $ev->note,
                'confermato' => $ev->confermato
            ];
        })->toArray();
        
    } catch (Exception $e) {
        error_log("Errore caricamento eventi: " . $e->getMessage());
        $eventi = [];
    }
}

// Organizza eventi per aula e ora (come prima con lezioni)
$calendario = [];
foreach ($eventi as $evento) {
    $aula_id = $evento['aula_id'];
    $ora = $evento['ora_inizio'];
    $calendario[$aula_id][$ora] = $evento;
}

// Funzione icone materie (usa emoji invece di Bootstrap Icons che mancano di alcuni strumenti)
function getIconaMateria($materia) {
    $materia_lower = strtolower($materia ?? '');
    if (strpos($materia_lower, 'chitar') !== false) return '🎸';
    if (strpos($materia_lower, 'piano') !== false) return '🎹';
    if (strpos($materia_lower, 'canto') !== false) return '🎤';
    if (strpos($materia_lower, 'batter') !== false) return '🥁';
    if (strpos($materia_lower, 'basso') !== false) return '🎸';
    if (strpos($materia_lower, 'violino') !== false) return '🎻';
    if (strpos($materia_lower, 'sax') !== false) return '🎷';
    return '🎵';
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Alert Versione Eventi -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-calendar-event"></i>
        <strong>Nuovo Sistema Eventi</strong> - Stai visualizzando il calendario con il nuovo sistema eventi (eventi_calendario).
        <a href="calendario.php" class="alert-link">Torna al calendario classico</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-week"></i> Calendario Eventi
            </h1>
            <p class="text-muted mb-0">
                Visualizzazione con nuovo sistema eventi • 
                <span class="badge bg-primary"><?= count($eventi) ?> eventi</span>
            </p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success me-2" onclick="apriModalCreaEvento('<?= $data_selezionata ?>')">
                <i class="bi bi-plus-circle"></i> Nuovo Evento
            </button>
            <a href="test_api_eventi_browser.php" class="btn btn-outline-info me-2">
                <i class="bi bi-tools"></i> Test API
            </a>
            <button class="btn btn-outline-primary btn-print">
                <i class="bi bi-printer"></i> Stampa
            </button>
        </div>
    </div>

    <!-- Navigazione Settimana -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="settimana" value="<?= $settimana_offset - 1 ?>">
                    <input type="hidden" name="giorno" value="<?= $giorno_selezionato ?>">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-chevron-left"></i> Settimana Precedente
                    </button>
                </form>
                <h5 class="mb-0">
                    <i class="bi bi-calendar-week"></i> 
                    Settimana dal <?= $giorni_settimana[0]['data_display'] ?> al <?= $giorni_settimana[5]['data_display'] ?>
                    <?php if ($settimana_offset == 0): ?>
                        <span class="badge bg-success ms-2">Corrente</span>
                    <?php endif; ?>
                </h5>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="settimana" value="<?= $settimana_offset + 1 ?>">
                    <input type="hidden" name="giorno" value="<?= $giorno_selezionato ?>">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        Settimana Successiva <i class="bi bi-chevron-right"></i>
                    </button>
                </form>
            </div>
            
            <!-- Tab Giorni -->
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <?php foreach ($giorni_settimana as $giorno): ?>
                    <li class="nav-item" role="presentation">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="settimana" value="<?= $settimana_offset ?>">
                            <input type="hidden" name="giorno" value="<?= $giorno['key'] ?>">
                            <button type="submit" 
                                    class="nav-link <?= $giorno_selezionato == $giorno['key'] ? 'active' : '' ?> <?= $giorno['is_today'] ? 'fw-bold' : '' ?>"
                                    style="<?= $giorno['is_today'] ? 'background-color: #fff3cd;' : '' ?> border: none; width: 100%;">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-6"><?= $giorno['nome'] ?></span>
                                    <span class="badge bg-secondary mt-1"><?= $giorno['data_display'] ?></span>
                                    <?php if ($giorno['is_today']): ?>
                                        <span class="badge bg-warning text-dark mt-1">OGGI</span>
                                    <?php endif; ?>
                                    <?php if ($giorno['festivita']): ?>
                                        <span class="badge bg-danger mt-1"><?= $giorno['festivita']['nome'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Legenda Tipologie -->
            <div class="mt-3 mb-2">
                <h6 class="mb-2"><i class="bi bi-palette"></i> Tipologie Eventi</h6>
                <div class="row g-2">
                    <?php
                    // Raggruppa eventi per tipologia per mostrare legenda dinamica
                    $tipologie_presenti = [];
                    foreach ($eventi as $ev) {
                        $key = $ev['tipologia_nome'];
                        if (!isset($tipologie_presenti[$key])) {
                            $tipologie_presenti[$key] = [
                                'nome' => $ev['tipologia_nome'],
                                'bg' => $ev['colore_bg'],
                                'border' => $ev['colore_border'],
                                'icona' => $ev['icona'] ?? '📅'
                            ];
                        }
                    }
                    ?>
                    <?php foreach ($tipologie_presenti as $tip): ?>
                        <div class="col-auto">
                            <span class="badge" style="background-color: <?= $tip['bg'] ?>; color: #333; border-left: 3px solid <?= $tip['border'] ?>;">
                                <?= $tip['icona'] ?> <?= $tip['nome'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
                            <?php foreach ($aule as $aula): 
                                $aula_nome_lower = strtolower($aula['nome']);
                                $aula_class = 'aula-header';
                                
                                if (strpos($aula_nome_lower, 'midi') !== false) $aula_class .= ' aula-midi';
                                elseif (strpos($aula_nome_lower, 'piano') !== false) $aula_class .= ' aula-piano';
                                elseif (strpos($aula_nome_lower, 'magna') !== false) $aula_class .= ' aula-magna';
                                elseif (strpos($aula_nome_lower, 'jazz') !== false) $aula_class .= ' aula-jazz';
                            ?>
                                <th class="<?= $aula_class ?>">
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
                                        data-ora="<?= $slot['inizio'] ?>"
                                        onclick="apriModalCreaEvento('<?= $data_selezionata ?>', <?= $aula['id'] ?>, '<?= $slot['inizio'] ?>')"
                                        style="cursor: pointer;"
                                        title="Click per creare un nuovo evento">
                                        <?php
                                        $evento_slot = null;
                                        if (isset($calendario[$aula['id']])) {
                                            $slot_start = strtotime($slot['inizio']);
                                            $slot_end = strtotime($slot['fine']);
                                            
                                            foreach ($calendario[$aula['id']] as $ora => $evt) {
                                                $evento_start = strtotime($ora);
                                                if ($evento_start >= $slot_start && $evento_start < $slot_end) {
                                                    $evento_slot = $evt;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if ($evento_slot): 
                                            $icona = getIconaMateria($evento_slot['materia_nome']);
                                            $is_annullata = !$evento_slot['confermato'] || $giorno_festivita;
                                            $classe_annullata = $is_annullata ? ' lezione-annullata' : '';
                                        ?>
                                            <div class="lezione-slot<?= $classe_annullata ?>" 
                                                 data-evento-id="<?= $evento_slot['id'] ?>"
                                                 onclick="apriModalDettaglioPrenotazione(<?= $evento_slot['id'] ?>); event.stopPropagation();"
                                                 style="background-color: <?= $evento_slot['colore_bg'] ?>; border-left: 3px solid <?= $evento_slot['colore_border'] ?>; position: relative; cursor: pointer;"
                                                 title="<?= e($evento_slot['partecipante_nome'] ?? 'N/D') ?> - <?= e($evento_slot['tipologia_nome']) ?> (click per dettagli)">
                                                
                                                <!-- Pulsante Modifica -->
                                                <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-1" 
                                                        onclick="apriModalModificaEvento(<?= $evento_slot['id'] ?>); event.stopPropagation();"
                                                        title="Modifica evento"
                                                        style="opacity: 0.7; padding: 2px 6px; font-size: 0.75rem; z-index: 10;">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                
                                                <div class="lezione-orario-badge">
                                                    <?= substr($evento_slot['ora_inizio'], 0, 5) ?>-<?= substr($evento_slot['ora_fine'], 0, 5) ?>
                                                </div>
                                                
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="me-2" style="font-size: 1.2rem;"><?= $evento_slot['icona'] ?? '📅' ?></span>
                                                    <small class="badge" style="background-color: <?= $evento_slot['colore_border'] ?>; color: white;">
                                                        <?= e($evento_slot['tipologia_nome']) ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="lezione-header">
                                                    <span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona ?></span>
                                                    <?php if ($evento_slot['socio_id']): ?>
                                                        <span class="lezione-socio" 
                                                              style="cursor: pointer; text-decoration: underline;" 
                                                              data-socio-id="<?= $evento_slot['socio_id'] ?>"
                                                              onclick="caricaInfoSocio(<?= $evento_slot['socio_id'] ?>, <?= $evento_slot['id'] ?>, '<?= addslashes($evento_slot['partecipante_nome']) ?>', '<?= addslashes($evento_slot['materia_nome'] ?? '') ?>', '<?= $data_selezionata ?>'); return false;">
                                                            <?= e($evento_slot['partecipante_nome']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= e($evento_slot['titolo'] ?? 'Evento') ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="lezione-info-row">
                                                    <div class="lezione-docente">
                                                        <i class="bi bi-person-fill"></i> <?= e($evento_slot['docente_nome'] ?? 'N/D') ?>
                                                    </div>
                                                    <?php if ($evento_slot['materia_nome']): ?>
                                                        <div class="lezione-materia-inline">
                                                            <?= e($evento_slot['materia_nome']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($evento_slot['note']): ?>
                                                    <div class="mt-1">
                                                        <small class="text-muted">
                                                            <i class="bi bi-chat-dots"></i> <?= e(substr($evento_slot['note'], 0, 50)) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
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

</div>

<!-- Modal Dettaglio Prenotazione -->
<div class="modal fade" id="modalDettaglioPrenotazione" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> Dettaglio Prenotazione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipologia</label>
                            <p id="dettaglioTipologia" class="text-muted">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Stato</label>
                            <p id="dettaglioStato">
                                <span id="dettaglioStatoBadge" class="badge bg-secondary">-</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data</label>
                            <p id="dettaglioData" class="text-muted">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Orario</label>
                            <p id="dettaglioOrario" class="text-muted">-</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-door-open"></i> Sala</label>
                            <p id="dettaglioSala" class="text-muted">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-person-fill"></i> Docente</label>
                            <p id="dettaglioDocente" class="text-muted">-</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-music-note-beamed"></i> Materia</label>
                            <p id="dettaglioMateria" class="text-muted">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-person"></i> Socio</label>
                            <p id="dettaglioSocio" class="text-muted">-</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titolo</label>
                        <p id="dettaglioTitolo" class="text-muted">-</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrizione</label>
                        <p id="dettaglioDescrizione" class="text-muted">-</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Note</label>
                        <p id="dettaglioNote" class="text-muted">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Chiudi
                </button>
                <button type="button" class="btn btn-primary" id="btnModificaPrenotazione" onclick="modificaPrenotazioneAttuale(); event.stopPropagation();">
                    <i class="bi bi-pencil-fill"></i> Modifica Orario/Sala
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crea/Modifica Evento -->
<div class="modal fade" id="modalCreaEvento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCreaEventoTitle">
                    <i class="bi bi-plus-circle"></i> Nuovo Evento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreaEvento">
                    <!-- Tipologia -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipologia Evento *</label>
                        <select class="form-select" id="eventoTipologia" required>
                            <option value="">-- Seleziona --</option>
                        </select>
                        <div id="previewColoreTipologia" class="mt-2 p-2 rounded" style="display:none;"></div>
                    </div>
                    
                    <!-- Ricorrente -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <select class="form-select" id="eventoRicorrente">
                            <option value="0">Evento Singolo</option>
                            <option value="1">Evento Ricorrente</option>
                        </select>
                    </div>
                    
                    <!-- Campi Singolo -->
                    <div id="campiSingolo">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data Evento *</label>
                            <input type="date" class="form-control" id="eventoDataEvento">
                        </div>
                    </div>
                    
                    <!-- Campi Ricorrente -->
                    <div id="campiRicorrente" style="display:none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Giorno Settimana *</label>
                                <select class="form-select" id="eventoGiornoSettimana">
                                    <option value="">-- Seleziona --</option>
                                    <option value="lunedi">Lunedì</option>
                                    <option value="martedi">Martedì</option>
                                    <option value="mercoledi">Mercoledì</option>
                                    <option value="giovedi">Giovedì</option>
                                    <option value="venerdi">Venerdì</option>
                                    <option value="sabato">Sabato</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Data Inizio</label>
                                <input type="date" class="form-control" id="eventoDataInizio">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Data Fine</label>
                                <input type="date" class="form-control" id="eventoDataFine">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orari -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" id="eventoOraInizio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" id="eventoOraFine" required>
                        </div>
                    </div>
                    
                    <!-- Aula -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Aula *</label>
                        <select class="form-select" id="eventoAula" required>
                            <option value="">-- Seleziona --</option>
                        </select>
                    </div>
                    
                    <!-- Docente e Materia -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Docente</label>
                            <select class="form-select" id="eventoDocente">
                                <option value="">-- Nessuno --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Materia</label>
                            <select class="form-select" id="eventoMateria">
                                <option value="">-- Nessuna --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Socio -->
                    <div class="mb-3">
                        <label class="form-label">Socio</label>
                        <select class="form-select" id="eventoSocio">
                            <option value="">-- Nessuno --</option>
                        </select>
                    </div>
                    
                    <!-- Titolo -->
                    <div class="mb-3">
                        <label class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="eventoTitolo" placeholder="Es: Saggio di Natale">
                    </div>
                    
                    <!-- Descrizione -->
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" id="eventoDescrizione" rows="2" placeholder="Descrizione evento"></textarea>
                    </div>
                    
                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" id="eventoNote" rows="2" placeholder="Note aggiuntive"></textarea>
                    </div>
                    
                    <!-- Confermato -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="eventoConfermato" checked>
                        <label class="form-check-label" for="eventoConfermato">
                            Evento Confermato
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvaEvento">
                    <i class="bi bi-check-circle"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Info Socio (riuso quello esistente) -->
<div class="modal fade" id="infoSociModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle"></i> <span id="modalSociNome">Info Socio</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalSociBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Caricamento...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $_SESSION['csp_nonce'] ?>">
// Riuso funzioni esistenti da calendario.php
let currentLezioneData = null;

function caricaInfoSocio(sociId, eventoId = null, nomeSocio = '', materiaEvento = '', dataEvento = '') {
    const modalBody = document.getElementById('modalSociBody');
    const modalNome = document.getElementById('modalSociNome');
    const modalElement = document.getElementById('infoSociModal');
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    currentLezioneData = eventoId ? {
        lezione_id: eventoId,
        socio_nome: nomeSocio,
        materia: materiaEvento,
        data: dataEvento
    } : null;
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Caricamento...</span>
            </div>
        </div>
    `;
    
    fetch(`<?= BASE_URL ?>/api/api_get_info_socio.php?socio_id=${sociId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            
            modalNome.textContent = data.socio.nome_completo;
            
            let html = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-calendar-x"></i> Assenze</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Totali:</span>
                                    <span class="badge bg-secondary">${data.statistiche.assenze.totale}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Da Recuperare:</span>
                                    <span class="badge bg-danger">${data.statistiche.assenze.da_recuperare}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Recuperi</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Programmati:</span>
                                    <span class="badge bg-primary">${data.statistiche.recuperi.programmati}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Completati:</span>
                                    <span class="badge bg-success">${data.statistiche.recuperi.completati}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Errore: ${error.message}
                </div>
            `;
        });
}
</script>

<script src="assets/js/eventi.js"></script>

<?php include 'includes/footer.php'; ?>
