 <?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Settimanale';
$current_page = 'calendario';

// Inizializza Controllers
$lezioniCtrl = new LezioniController();
$auleCtrl = new AuleController();

// Gestione settimana
$settimana_offset = (int)get('settimana', 0); // 0 = settimana corrente, -1 = precedente, +1 = successiva

// Calcola lunedì della settimana selezionata
$oggi = new DateTime();
$oggi->modify("this week monday"); // Va al lunedì della settimana corrente
if ($settimana_offset != 0) {
    $oggi->modify(($settimana_offset > 0 ? '+' : '') . $settimana_offset . ' weeks');
}

// Genera array giorni settimana con date
$giorni_settimana = [];
$giorno_corrente = clone $oggi;
$giorni_nomi = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
$giorni_keys = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];

for ($i = 0; $i < 6; $i++) {
    $giorni_settimana[] = [
        'nome' => $giorni_nomi[$i],
        'key' => $giorni_keys[$i],
        'data' => $giorno_corrente->format('Y-m-d'),
        'data_display' => $giorno_corrente->format('d/m'),
        'is_today' => $giorno_corrente->format('Y-m-d') == date('Y-m-d')
    ];
    $giorno_corrente->modify('+1 day');
}

// Giorno selezionato (default: oggi se nella settimana corrente, altrimenti lunedì)
$giorno_selezionato = get('giorno', '');
if (empty($giorno_selezionato)) {
    $oggi_key = strtolower(date('l'));
    $mapping = [
        'monday' => 'lunedi', 'tuesday' => 'martedi', 'wednesday' => 'mercoledi',
        'thursday' => 'giovedi', 'friday' => 'venerdi', 'saturday' => 'sabato'
    ];
    $giorno_selezionato = $mapping[$oggi_key] ?? 'lunedi';
    
    // Se oggi è domenica o non nella settimana visualizzata, usa lunedì
    if ($oggi_key == 'sunday' || $settimana_offset != 0) {
        $giorno_selezionato = 'lunedi';
    }
}

// Trova data del giorno selezionato
$data_selezionata = '';
foreach ($giorni_settimana as $g) {
    if ($g['key'] == $giorno_selezionato) {
        $data_selezionata = $g['data'];
        break;
    }
}

// Ottieni aule tramite controller
$aule = $auleCtrl->getAule();

// Ottieni slot orari (default 15 minuti)
$slots = generaSlotOrari(ORA_INIZIO_SCUOLA, ORA_FINE_SCUOLA, DURATA_SLOT_DEFAULT);

// Ottieni lezioni per il giorno selezionato tramite controller
// Se docente e configurazione lo richiede, filtra solo sue lezioni
if ($auth->hasRole('docente') && !DOCENTE_VIEW_ALL_CALENDAR) {
    $docentiCtrl = new DocentiController();
    $docente = $docentiCtrl->getDocenteByUserId($auth->getUserId());
    
    if ($docente) {
        $lezioni = $lezioniCtrl->getLezioniPerDocenteEGiorno($docente['id'], $giorno_selezionato);
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

    <!-- Navigazione Settimana -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="?settimana=<?= $settimana_offset - 1 ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-chevron-left"></i> Settimana Precedente
                </a>
                <h5 class="mb-0">
                    <i class="bi bi-calendar-week"></i> 
                    Settimana dal <?= $giorni_settimana[0]['data_display'] ?> al <?= $giorni_settimana[5]['data_display'] ?>
                    <?php if ($settimana_offset == 0): ?>
                        <span class="badge bg-success ms-2">Corrente</span>
                    <?php endif; ?>
                </h5>
                <a href="?settimana=<?= $settimana_offset + 1 ?>" class="btn btn-outline-primary btn-sm">
                    Settimana Successiva <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            
            <!-- Tab Giorni Orizzontali -->
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <?php foreach ($giorni_settimana as $giorno): ?>
                    <li class="nav-item" role="presentation">
                        <a href="?settimana=<?= $settimana_offset ?>&giorno=<?= $giorno['key'] ?>" 
                           class="nav-link <?= $giorno_selezionato == $giorno['key'] ? 'active' : '' ?> <?= $giorno['is_today'] ? 'fw-bold' : '' ?>"
                           style="<?= $giorno['is_today'] ? 'background-color: #fff3cd; border-color: #ffc107;' : '' ?>">
                            <div class="d-flex flex-column align-items-center">
                                <span class="fs-6"><?= $giorno['nome'] ?></span>
                                <span class="badge bg-secondary mt-1"><?= $giorno['data_display'] ?></span>
                                <?php if ($giorno['is_today']): ?>
                                    <span class="badge bg-warning text-dark mt-1">OGGI</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="mt-3 text-center">
                <span class="badge bg-primary fs-6">
                    <?= count($lezioni) ?> lezioni programmate
                </span>
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
                                // Determina classe CSS per colore aula
                                $aula_nome_lower = strtolower($aula['nome']);
                                $aula_class = 'aula-header';
                                
                                if (strpos($aula_nome_lower, 'midi') !== false) {
                                    $aula_class .= ' aula-midi';
                                } elseif (strpos($aula_nome_lower, 'piano') !== false) {
                                    $aula_class .= ' aula-piano';
                                } elseif (strpos($aula_nome_lower, 'magna') !== false) {
                                    $aula_class .= ' aula-magna';
                                } elseif (strpos($aula_nome_lower, 'jazz') !== false) {
                                    $aula_class .= ' aula-jazz';
                                } elseif (strpos($aula_nome_lower, 'pop') !== false) {
                                    $aula_class .= ' aula-pop';
                                } elseif (strpos($aula_nome_lower, 'rock') !== false) {
                                    $aula_class .= ' aula-rock';
                                }
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
                                                    <span class="lezione-allievo" 
                                                          style="cursor: pointer; text-decoration: underline;" 
                                                          data-allievo-id="<?= $lezione_slot['allievo_id'] ?>"
                                                          data-lezione-id="<?= $lezione_slot['id'] ?>"
                                                          onclick="caricaInfoAllievo(<?= $lezione_slot['allievo_id'] ?>, <?= $lezione_slot['id'] ?>, '<?= addslashes($lezione_slot['allievo']) ?>', '<?= addslashes($lezione_slot['materia']) ?>', '<?= $data_selezionata ?>'); return false;">
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

<!-- Modal Segna Assenza -->
<div class="modal fade" id="segnaAssenzaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Segna Assenza
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="assenzaInfo">
                    <!-- Info lezione compilata da JS -->
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Causale Assenza *</label>
                    <select class="form-select" id="causaleAssenza" required>
                        <option value="allievo">Causata da Allievo</option>
                        <option value="docente">Causata da Docente (genera recupero automatico)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="noteAssenza" class="form-label">Note (opzionale)</label>
                    <textarea class="form-control" id="noteAssenza" rows="3" 
                              placeholder="Aggiungi eventuali note..."></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>Attenzione:</strong> L'assenza verrà registrata immediatamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-warning" onclick="confermaAssenza()">
                    <i class="bi bi-check-circle"></i> Conferma Assenza
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Info Allievo -->
<div class="modal fade" id="infoAllieviModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle"></i> <span id="modalAllieviNome">Info Allievo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAllieviBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Caricamento...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="chiudiModalInfoAllievo()">
                    <i class="bi bi-x-circle"></i> Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentLezioneData = null;

function caricaInfoAllievo(allieviId, lezioneId = null, nomeAllievo = '', materiaLezione = '', dataLezione = '') {
    const modalBody = document.getElementById('modalAllieviBody');
    const modalNome = document.getElementById('modalAllieviNome');
    const modalElement = document.getElementById('infoAllieviModal');
    
    // Apri la modal (verifica che Bootstrap sia caricato)
    if (typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        // Fallback: aggiungi classi manualmente
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        document.body.classList.add('modal-open');
        
        // Aggiungi backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'tempBackdrop';
        document.body.appendChild(backdrop);
    }
    
    // Salva dati lezione corrente per pulsante assenza
    currentLezioneData = lezioneId ? {
        lezione_id: lezioneId,
        allievo_nome: nomeAllievo,
        materia: materiaLezione,
        data: dataLezione
    } : null;
    
    // Mostra loader
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Caricamento...</span>
            </div>
        </div>
    `;
    
    // Fetch dati
    fetch(`<?= BASE_URL ?>/api_get_info_allievo.php?allievo_id=${allieviId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Aggiorna titolo
            modalNome.textContent = data.allievo.nome_completo;
            
            // Costruisci HTML stile card recuperi
            let html = `
                ${currentLezioneData ? `
                <!-- Alert Lezione Selezionata -->
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-calendar-event"></i>
                        <strong>Lezione:</strong> ${currentLezioneData.materia} - ${new Date(currentLezioneData.data).toLocaleDateString('it-IT')}
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="segnaAssenza()">
                        <i class="bi bi-x-circle"></i> Segna Assenza
                    </button>
                </div>
                ` : ''}
                <!-- Statistiche -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-calendar-x"></i> Assenze</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Totali:</span>
                                    <span class="badge bg-secondary">${data.statistiche.assenze.totale}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Da Recuperare:</span>
                                    <span class="badge bg-danger">${data.statistiche.assenze.da_recuperare}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Causate da Allievo:</span>
                                    <span class="badge bg-warning text-dark">${data.statistiche.assenze.causate_da_allievo}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Causate da Docente:</span>
                                    <span class="badge bg-info">${data.statistiche.assenze.causate_da_docente}</span>
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
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Totali:</span>
                                    <span class="badge bg-secondary">${data.statistiche.recuperi.totale}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Programmati:</span>
                                    <span class="badge bg-primary">${data.statistiche.recuperi.programmati}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Completati:</span>
                                    <span class="badge bg-success">${data.statistiche.recuperi.completati}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Annullati:</span>
                                    <span class="badge bg-danger">${data.statistiche.recuperi.annullati}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Corsi Frequentati -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-book"></i> Corsi Frequentati</h6>
                    </div>
                    <div class="card-body">
                        ${data.corsi.length > 0 ? `
                            <div class="list-group list-group-flush">
                                ${data.corsi.map(corso => `
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="bi bi-music-note"></i> ${corso.materia || 'N/D'}
                                            </h6>
                                            <small>${corso.giorno_settimana}</small>
                                        </div>
                                        <p class="mb-1">
                                            <i class="bi bi-person"></i> ${corso.docente}<br>
                                            <i class="bi bi-clock"></i> ${corso.ora_inizio.substr(0,5)} - ${corso.ora_fine.substr(0,5)}
                                            ${corso.aula ? `<br><i class="bi bi-door-open"></i> ${corso.aula}` : ''}
                                        </p>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-muted">Nessun corso registrato</p>'}
                    </div>
                </div>
                
                <!-- Prossimi Recuperi -->
                ${data.prossimi_recuperi.length > 0 ? `
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-calendar-event"></i> Prossimi Recuperi</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                ${data.prossimi_recuperi.map(rec => `
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="bi bi-calendar-check text-success"></i> 
                                                ${new Date(rec.data_recupero).toLocaleDateString('it-IT')}
                                            </h6>
                                            <small>${rec.ora_inizio.substr(0,5)} - ${rec.ora_fine.substr(0,5)}</small>
                                        </div>
                                        <p class="mb-0">
                                            ${rec.materia || 'N/D'} - ${rec.docente}
                                            ${rec.aula ? `<br><i class="bi bi-door-open"></i> ${rec.aula}` : ''}
                                        </p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                ` : ''}
            `;
            
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Errore nel caricamento: ${error.message}
                </div>
            `;
        });
}

function chiudiModalInfoAllievo() {
    const modalElement = document.getElementById('infoAllieviModal');
    
    if (typeof bootstrap !== 'undefined') {
        // Usa API Bootstrap
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    } else {
        // Fallback manuale
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        // Rimuovi backdrop
        const backdrop = document.getElementById('tempBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

function segnaAssenza() {
    if (!currentLezioneData) {
        alert('Errore: nessuna lezione selezionata');
        return;
    }
    
    // Chiudi modal info allievo
    chiudiModalInfoAllievo();
    
    // Apri modal segna assenza
    setTimeout(() => {
        const assenzaInfo = document.getElementById('assenzaInfo');
        assenzaInfo.innerHTML = `
            <strong>Allievo:</strong> ${currentLezioneData.allievo_nome}<br>
            <strong>Materia:</strong> ${currentLezioneData.materia}<br>
            <strong>Data:</strong> ${new Date(currentLezioneData.data).toLocaleDateString('it-IT')}
        `;
        
        // Reset form
        document.getElementById('causaleAssenza').value = 'allievo';
        document.getElementById('noteAssenza').value = '';
        
        // Apri modal
        const modalElement = document.getElementById('segnaAssenzaModal');
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            document.body.classList.add('modal-open');
            
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'assenzaBackdrop';
            document.body.appendChild(backdrop);
        }
    }, 300);
}

function confermaAssenza() {
    if (!currentLezioneData) {
        alert('Errore: nessuna lezione selezionata');
        return;
    }
    
    const causale = document.getElementById('causaleAssenza').value;
    const note = document.getElementById('noteAssenza').value;
    
    // Disabilita pulsante
    const btnConferma = event.target;
    btnConferma.disabled = true;
    btnConferma.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvataggio...';
    
    // Invia richiesta
    fetch('<?= BASE_URL ?>/api_salva_assenza_calendario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            lezione_id: currentLezioneData.lezione_id,
            data_lezione: currentLezioneData.data,
            causale: causale,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Chiudi modal
            const modalElement = document.getElementById('segnaAssenzaModal');
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            } else {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('assenzaBackdrop');
                if (backdrop) backdrop.remove();
            }
            
            // Mostra messaggio successo
            let messaggio = 'Assenza registrata con successo!';
            if (data.recupero_creato) {
                messaggio += ' È stato creato automaticamente un recupero da programmare.';
            }
            
            alert(messaggio);
            
            // Ricarica pagina per aggiornare statistiche
            location.reload();
        } else {
            throw new Error(data.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => {
        alert('Errore: ' + error.message);
        btnConferma.disabled = false;
        btnConferma.innerHTML = '<i class="bi bi-check-circle"></i> Conferma Assenza';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Click su lezione apre modal info allievo
    const lezioniSlots = document.querySelectorAll('.lezione-slot');
    lezioniSlots.forEach(slot => {
        slot.addEventListener('click', function(e) {
            // Se il click è sul nome allievo, non fare nulla (ha già il suo onclick)
            if (e.target.classList.contains('lezione-allievo') || e.target.closest('.lezione-allievo')) {
                return;
            }
            
            // Altrimenti apri modal info allievo
            const allieviSpan = this.querySelector('.lezione-allievo');
            if (allieviSpan) {
                allieviSpan.click();
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
