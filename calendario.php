 <?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Settimanale';
$current_page = 'calendario';

// Inizializza Controllers
$lezioniCtrl = new LezioniController();
$auleCtrl = new AuleController();

// Gestione settimana - Supporta sia GET che POST
$settimana_offset = isset($_POST['settimana']) ? (int)$_POST['settimana'] : (int)get('settimana', 0);

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

// Giorno selezionato - Supporta sia GET che POST
$giorno_selezionato = isset($_POST['giorno']) ? $_POST['giorno'] : get('giorno', '');
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

// Trova data del giorno selezionato e verifica se è festività
$data_selezionata = '';
$giorno_festivita = false;
foreach ($giorni_settimana as $g) {
    if ($g['key'] == $giorno_selezionato) {
        $data_selezionata = $g['data'];
        $giorno_festivita = $g['festivita'];
        break;
    }
}

// Ottieni aule tramite controller
$aule = $auleCtrl->getAule();

// Ottieni slot orari (default 15 minuti)
$slots = generaSlotOrari(ORA_INIZIO_SCUOLA, ORA_FINE_SCUOLA, DURATA_SLOT_DEFAULT);

// Ottieni lezioni per il giorno selezionato tramite controller
// IMPORTANTE: Passa la data specifica per il JOIN con assenze
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
    // Passa la data selezionata per check assenze
    $lezioni = $lezioniCtrl->getLezioniPerGiorno($giorno_selezionato, true, $data_selezionata);
}

// NUOVO: Carica anche eventi specifici da eventi_calendario
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT 
        e.id,
        e.ora_inizio,
        e.ora_fine,
        e.aula_id,
        t.codice as tipo,
        t.nome as tipologia_nome,
        t.colore_bg,
        t.colore_border,
        COALESCE(a.cognome || ' ' || a.nome, '') as allievo,
        COALESCE(a.id, 0) as allievo_id,
        COALESCE(d.cognome || ' ' || d.nome, '') as docente,
        COALESCE(m.nome, e.titolo, 'Prenotazione') as materia,
        au.nome as aula,
        e.note,
        e.attivo as attiva,
        e.confermato,
        'evento' as source_type
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN allievi a ON e.allievo_id = a.id
    LEFT JOIN docenti d ON e.docente_id = d.id
    LEFT JOIN materie m ON e.materia_id = m.id
    LEFT JOIN aule au ON e.aula_id = au.id
    WHERE e.data_evento = ?
    AND e.attivo = 1
    AND t.attiva = 1
");
$stmt->execute([$data_selezionata]);
$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aggiungi marker source_type alle lezioni ricorrenti
foreach ($lezioni as &$lez) {
    $lez['source_type'] = 'lezione';
}
unset($lez);

// Unisci lezioni ricorrenti + eventi specifici
$lezioni = array_merge($lezioni, $eventi);

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
            
            <!-- Tab Giorni Orizzontali -->
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <?php foreach ($giorni_settimana as $giorno): ?>
                    <li class="nav-item" role="presentation">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="settimana" value="<?= $settimana_offset ?>">
                            <input type="hidden" name="giorno" value="<?= $giorno['key'] ?>">
                            <button type="submit" 
                                    class="nav-link <?= $giorno_selezionato == $giorno['key'] ? 'active' : '' ?> <?= $giorno['is_today'] ? 'fw-bold' : '' ?> <?= $giorno['festivita'] ? 'border-danger' : '' ?>"
                                    style="<?= $giorno['is_today'] ? 'background-color: #fff3cd; border-color: #ffc107;' : '' ?><?= $giorno['festivita'] ? 'border-width: 3px !important;' : '' ?> border: none; width: 100%;">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-6"><?= $giorno['nome'] ?></span>
                                    <span class="badge bg-secondary mt-1"><?= $giorno['data_display'] ?></span>
                                    <?php if ($giorno['is_today']): ?>
                                        <span class="badge bg-warning text-dark mt-1">OGGI</span>
                                    <?php endif; ?>
                                    <?php if ($giorno['festivita']): ?>
                                        <span class="badge bg-danger mt-1" title="Festività Nazionale"><?= $giorno['festivita']['nome'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Legenda -->
            <div class="mt-3 mb-2">
                <h6 class="mb-2"><i class="bi bi-info-circle"></i> Legenda</h6>
                <div class="row g-2">
                    <div class="col-auto">
                        <span class="badge" style="background-color: #fff5f0; color: #333; border-left: 3px solid #ff6b35;">
                            Regolare
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background-color: #e3f2fd; color: #333; border-left: 3px solid #2196f3;">
                            Custom
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background-color: #e8f5e9; color: #333; border-left: 3px solid #4caf50;">
                            Recupero
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background-color: #fff9c4; color: #333; border-left: 3px solid #fdd835;">
                            <i class="bi bi-calendar-plus"></i> Prenotazioni
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background-color: #f3e5f5; color: #333; border-left: 3px solid #9c27b0;">
                            Laboratorio
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background-color: #e0e0e0; color: #757575; border-left: 3px solid #9e9e9e;">
                            <i class="bi bi-x-circle"></i> Festività/Assenza
                        </span>
                    </div>
                </div>
            </div>
            
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
                                    <td class="calendario-cell calendario-cell-hoverable" 
                                        data-aula-id="<?= $aula['id'] ?>" 
                                        data-aula-nome="<?= e($aula['nome']) ?>"
                                        data-ora="<?= $slot['inizio'] ?>"
                                        data-giorno="<?= $giorno_selezionato ?>"
                                        data-data="<?= $data_selezionata ?>">
                                        <?php
                                        // Trova lezione che INIZIA in questo slot o prima della fine dello slot
                                        $lezione_slot = null;
                                        $evento_slot = null;
                                        if (isset($calendario[$aula['id']])) {
                                            $slot_start = strtotime($slot['inizio']);
                                            $slot_end = strtotime($slot['fine']);
                                            
                                            foreach ($calendario[$aula['id']] as $ora => $lez) {
                                                $lezione_start = strtotime($ora);
                                                // La lezione inizia nello slot se:
                                                // - inizia esattamente all'inizio dello slot
                                                // - inizia dopo l'inizio ma prima della fine dello slot
                                                if ($lezione_start >= $slot_start && $lezione_start < $slot_end) {
                                                    // Separa lezioni ricorrenti da eventi
                                                    if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
                                                        $evento_slot = $lez;
                                                    } else {
                                                        $lezione_slot = $lez;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Caso 1: Solo lezione (o evento senza lezione)
                                        if ($lezione_slot && !$evento_slot):
                                            $icona = getIconaMateria($lezione_slot['materia']);
                                            // Determina se lezione è annullata (attiva = 0 O giorno festività)
                                            $is_annullata = (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) || $giorno_festivita;
                                            $classe_annullata = $is_annullata ? ' lezione-annullata' : '';
                                            $motivo_annullamento = '';
                                            if ($is_annullata) {
                                                if ($giorno_festivita) {
                                                    $motivo_annullamento = ' (' . $giorno_festivita['nome'] . ')';
                                                } elseif (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) {
                                                    $motivo_annullamento = ' (ANNULLATA)';
                                                }
                                            }
                                        ?>
                                            <div class="lezione-slot tipo-<?= e($lezione_slot['tipo']) ?><?= $classe_annullata ?>" 
                                                 data-lezione-id="<?= $lezione_slot['id'] ?>"
                                                 title="<?= e($lezione_slot['allievo']) ?> - <?= e($lezione_slot['materia']) ?><?= $motivo_annullamento ?>">
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
                                                
                                                <?php if ($is_annullata): ?>
                                                    <!-- Lezione annullata = slot libero → mostra + -->
                                                    <div class="empty-slot-add slot-libero-assenza" 
                                                         onclick="apriModalNuovaPrenotazione(<?= $aula['id'] ?>, '<?= e($aula['nome']) ?>', '<?= $slot['inizio'] ?>', '<?= $giorno_selezionato ?>', '<?= $data_selezionata ?>')">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($evento_slot && !$lezione_slot): ?>
                                            <!-- Caso 2: Solo evento (senza lezione) - CLICCABILE -->
                                            <?php
                                            $icona = getIconaMateria($evento_slot['materia']);
                                            $is_annullata = (isset($evento_slot['attiva']) && $evento_slot['attiva'] == 0) || $giorno_festivita;
                                            $classe_annullata = $is_annullata ? ' lezione-annullata' : '';
                                            
                                            $tipo_css = 'regolare';
                                            $icona_prenotazione = '';
                                            $classe_icona_pren = '';
                                            $is_prenotazione = false;
                                            
                                            if (isset($evento_slot['tipo'])) {
                                                $tipo_lower = strtolower($evento_slot['tipo']);
                                                if (strpos($tipo_lower, 'recupero') !== false || $tipo_lower === 'lez_recupero') {
                                                    $tipo_css = 'recupero';
                                                } elseif (strpos($tipo_lower, 'pren_sala') !== false) {
                                                    $tipo_css = 'prenotazione-allievi';
                                                    $icona_prenotazione = 'bi-mortarboard';
                                                    $classe_icona_pren = 'tipo-allievi';
                                                    $is_prenotazione = true;
                                                } elseif (strpos($tipo_lower, 'pren_docente') !== false) {
                                                    $tipo_css = 'prenotazione-docente';
                                                    $icona_prenotazione = 'bi-person-workspace';
                                                    $classe_icona_pren = 'tipo-docente';
                                                    $is_prenotazione = true;
                                                } elseif (strpos($tipo_lower, 'pren_esterno') !== false) {
                                                    $tipo_css = 'prenotazione-esterno';
                                                    $icona_prenotazione = 'bi-person-x';
                                                    $classe_icona_pren = 'tipo-esterno';
                                                    $is_prenotazione = true;
                                                }
                                            }
                                            
                                            // Determina azione click: recuperi vanno su info allievo, prenotazioni su info evento
                                            $is_recupero = (strpos(strtolower($evento_slot['tipo'] ?? ''), 'recupero') !== false || strtolower($evento_slot['tipo'] ?? '') === 'lez_recupero');
                                            $is_prenotazione = (strpos(strtolower($evento_slot['tipo'] ?? ''), 'pren_') === 0);
                                            
                                            if ($is_recupero && $evento_slot['allievo_id'] > 0) {
                                                $onclick_action = "caricaInfoAllievo({$evento_slot['allievo_id']}, {$evento_slot['id']}, '" . addslashes($evento_slot['allievo']) . "', '" . addslashes($evento_slot['materia']) . "', '{$data_selezionata}'); return false.";
                                            } elseif ($is_prenotazione) {
                                                $onclick_action = "mostraInfoEvento({$evento_slot['id']}, 'evento'); return false.";
                                            } else {
                                                $onclick_action = "mostraInfoEvento({$evento_slot['id']}, 'evento'); return false.";
                                            }
                                        ?>
                                            <div class="lezione-slot tipo-<?= e($tipo_css) ?><?= $classe_annullata ?>" 
                                                 data-lezione-id="<?= $evento_slot['id'] ?>"
                                                 data-evento-id="<?= $evento_slot['id'] ?>"
                                                 title="<?= e($evento_slot['allievo'] ?: $evento_slot['docente'] ?: 'Prenotazione') ?>"
                                                 style="cursor: pointer;"
                                                 onclick="<?= $onclick_action ?>">
                                                <?php if ($icona_prenotazione): ?>
                                                    <i class="<?= $icona_prenotazione ?> prenotazione-tipo-icon <?= $classe_icona_pren ?>"></i>
                                                <?php endif; ?>
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($evento_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($evento_slot['ora_fine'])) ?>
                                                </div>
                                                
                                                <?php if ($is_prenotazione): ?>
                                                    <!-- Layout uniforme per PRENOTAZIONI -->
                                                    <div class="lezione-header">
                                                        <span class="lezione-allievo">PRENOTAZIONE</span>
                                                        <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                            <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="lezione-info-row">
                                                        <div class="lezione-docente">
                                                            <i class="bi bi-person-fill"></i> 
                                                            <?= e($evento_slot['allievo'] ?: $evento_slot['docente'] ?: 'Esterno') ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Layout standard per RECUPERI e altri eventi -->
                                                    <div class="lezione-header">
                                                        <i class="bi <?= $icona ?> icona-strumento"></i>
                                                        <span class="lezione-allievo">
                                                            <?= e($evento_slot['allievo'] ?: 'Evento') ?>
                                                            <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                                <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="lezione-info-row">
                                                        <div class="lezione-docente">
                                                            <i class="bi bi-person-fill"></i> <?= e($evento_slot['docente']) ?>
                                                        </div>
                                                        <div class="lezione-materia-inline">
                                                            <?= e($evento_slot['materia']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($evento_slot['note']): ?>
                                                    <div class="lezione-note-badge">
                                                        <i class="bi bi-sticky" title="<?= e($evento_slot['note']) ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <?php elseif ($lezione_slot && $evento_slot): ?>
                                            <!-- Caso 3: Lezione annullata + Evento sovrapposto -->
                                            <?php
                                            $icona_lez = getIconaMateria($lezione_slot['materia']);
                                            $is_annullata_lez = (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) || $giorno_festivita;
                                            
                                            $icona_evt = getIconaMateria($evento_slot['materia']);
                                            $tipo_css_evt = 'regolare';
                                            $icona_prenotazione_evt = '';
                                            $classe_icona_pren_evt = '';
                                            if (isset($evento_slot['tipo'])) {
                                                $tipo_lower = strtolower($evento_slot['tipo']);
                                                if (strpos($tipo_lower, 'recupero') !== false) {
                                                    $tipo_css_evt = 'recupero';
                                                } elseif (strpos($tipo_lower, 'pren_sala') !== false) {
                                                    $tipo_css_evt = 'prenotazione-allievi';
                                                    $icona_prenotazione_evt = 'bi-mortarboard';
                                                    $classe_icona_pren_evt = 'tipo-allievi';
                                                } elseif (strpos($tipo_lower, 'pren_docente') !== false) {
                                                    $tipo_css_evt = 'prenotazione-docente';
                                                    $icona_prenotazione_evt = 'bi-person-workspace';
                                                    $classe_icona_pren_evt = 'tipo-docente';
                                                } elseif (strpos($tipo_lower, 'pren_esterno') !== false) {
                                                    $tipo_css_evt = 'prenotazione-esterno';
                                                    $icona_prenotazione_evt = 'bi-person-x';
                                                    $classe_icona_pren_evt = 'tipo-esterno';
                                                }
                                            }
                                        ?>
                                            <!-- Lezione annullata (sfondo, 100%) -->
                                            <div class="lezione-slot lezione-annullata" 
                                                 style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 1;">
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($lezione_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($lezione_slot['ora_fine'])) ?>
                                                </div>
                                                <div class="lezione-header">
                                                    <i class="bi <?= $icona_lez ?> icona-strumento"></i>
                                                    <span class="lezione-allievo"><?= e($lezione_slot['allievo']) ?></span>
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
                                            
                                            <!-- Evento sovrapposto (primo piano, 80%) - CLICCABILE -->
                                            <div class="lezione-slot tipo-<?= e($tipo_css_evt) ?> slot-sovrapposto" 
                                                 data-lezione-id="<?= $evento_slot['id'] ?>"
                                                 data-evento-id="<?= $evento_slot['id'] ?>"
                                                 title="<?= e($evento_slot['allievo'] ?: 'Prenotazione') ?> - <?= e($evento_slot['materia']) ?>"
                                                 style="cursor: pointer;"
                                                 onclick="mostraInfoEvento(<?= $evento_slot['id'] ?>); return false;">
                                                <?php if ($icona_prenotazione_evt): ?>
                                                    <i class="<?= $icona_prenotazione_evt ?> prenotazione-tipo-icon <?= $classe_icona_pren_evt ?>"></i>
                                                <?php endif; ?>
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($evento_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($evento_slot['ora_fine'])) ?>
                                                </div>
                                                <div class="lezione-header">
                                                    <i class="bi <?= $icona_evt ?> icona-strumento"></i>
                                                    <span class="lezione-allievo">
                                                        <?= e($evento_slot['allievo'] ?: 'Prenotazione') ?>
                                                        <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                            <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="lezione-info-row">
                                                    <div class="lezione-docente">
                                                        <i class="bi bi-person-fill"></i> <?= e($evento_slot['docente']) ?>
                                                    </div>
                                                    <div class="lezione-materia-inline">
                                                        <?= e($evento_slot['materia']) ?>
                                                    </div>
                                                </div>
                                                <?php if ($evento_slot['note']): ?>
                                                    <div class="lezione-note-badge">
                                                        <i class="bi bi-sticky" title="<?= e($evento_slot['note']) ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <?php else: ?>
                                            <!-- Caso 4: Cella vuota - mostra + al hover -->
                                            <div class="empty-slot-add" onclick="apriModalNuovaPrenotazione(<?= $aula['id'] ?>, '<?= e($aula['nome']) ?>', '<?= $slot['inizio'] ?>', '<?= $giorno_selezionato ?>', '<?= $data_selezionata ?>')">
                                                <i class="bi bi-plus-circle"></i>
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
            <div class="modal-header" style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle"></i> <span id="modalAllieviNome">Info Allievo</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<!-- Modal Nuova Prenotazione Rapida -->
<div class="modal fade" id="nuovaPrenotazioneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Nuova Prenotazione Rapida
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong id="slotInfo"></strong>
                </div>
                
                <form id="formNuovaPrenotazione">
                    <input type="hidden" id="prenotAulaId" name="aula_id">
                    <input type="hidden" id="prenotOra" name="ora_inizio">
                    <input type="hidden" id="prenotGiorno" name="giorno">
                    <input type="hidden" id="prenotData" name="data">
                    
                    <!-- Tipo Prenotazione -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo Prenotazione *</label>
                        <select class="form-select" id="prenotTipo" name="tipo" required onchange="cambiaTipoPrenotazione()">
                            <option value="">Seleziona tipo...</option>
                            <option value="PREN_SALA">🎓 Prenotazione Allievi (lezione)</option>
                            <option value="PREN_DOCENTE">💼 Prenotazione Docente (personale)</option>
                            <option value="PREN_ESTERNO">👤 Prenotazione Esterno</option>
                        </select>
                    </div>
                    
                    <!-- Campi Prenotazione Allievi -->
                    <div id="campiAllievi" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Allievo *</label>
                                <select class="form-select" id="prenotAllievoId" name="allievo_id">
                                    <option value="">Caricamento...</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Materia *</label>
                                <select class="form-select" id="prenotMateriaId" name="materia_id">
                                    <option value="">Caricamento...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campi Prenotazione Docente -->
                    <div id="campiDocente" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Docente *</label>
                            <select class="form-select" id="prenotDocenteSoloId" name="docente_solo_id">
                                <option value="">Caricamento...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo Prenotazione</label>
                            <input type="text" class="form-control" id="prenotMotivoDocente" name="motivo_docente" placeholder="Es: Preparazione esami, Studio personale...">
                        </div>
                    </div>
                    
                    <!-- Campi Prenotazione Esterno -->
                    <div id="campiEsterno" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nome *</label>
                                <input type="text" class="form-control" id="prenotNomeEsterno" name="nome_esterno" placeholder="Nome">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cognome *</label>
                                <input type="text" class="form-control" id="prenotCognomeEsterno" name="cognome_esterno" placeholder="Cognome">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="prenotEmailEsterno" name="email_esterno" placeholder="email@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefono</label>
                                <input type="tel" class="form-control" id="prenotTelefonoEsterno" name="telefono_esterno" placeholder="+39 ...">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Durata (comune a tutti) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Durata (minuti) *</label>
                        <select class="form-select" id="prenotDurata" name="durata" required>
                            <option value="30">30 minuti</option>
                            <option value="45">45 minuti</option>
                            <option value="60" selected>60 minuti</option>
                            <option value="90">90 minuti</option>
                            <option value="120">120 minuti</option>
                        </select>
                    </div>
                    
                    <!-- Note (comuni a tutti) -->
                    <div class="mb-3">
                        <label class="form-label">Note (opzionale)</label>
                        <textarea class="form-control" id="prenotNote" name="note" rows="2" placeholder="Eventuali note aggiuntive..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-success" onclick="salvaPrenotazione()">
                    <i class="bi bi-check-circle"></i> Crea Prenotazione
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentLezioneData = null;

function apriModalNuovaPrenotazione(aulaId, aulaNome, ora, giorno, data) {
    // Popola info slot
    const giornoNice = {
        'lunedi': 'Lunedì',
        'martedi': 'Martedì',
        'mercoledi': 'Mercoledì',
        'giovedi': 'Giovedì',
        'venerdi': 'Venerdì',
        'sabato': 'Sabato'
    };
    
    document.getElementById('slotInfo').textContent = 
        `Aula: ${aulaNome} - ${giornoNice[giorno]} ${new Date(data).toLocaleDateString('it-IT')} alle ${ora}`;
    
    // Popola campi hidden
    document.getElementById('prenotAulaId').value = aulaId;
    document.getElementById('prenotOra').value = ora;
    document.getElementById('prenotGiorno').value = giorno;
    document.getElementById('prenotData').value = data;
    
    // Carica select allievi, docenti, materie
    caricaOpzioniPrenotazione();
    
    // Apri modal
    const modalElement = document.getElementById('nuovaPrenotazioneModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function cambiaTipoPrenotazione() {
    const tipo = document.getElementById('prenotTipo').value;
    const campiAllievi = document.getElementById('campiAllievi');
    const campiDocente = document.getElementById('campiDocente');
    const campiEsterno = document.getElementById('campiEsterno');
    
    // Nascondi tutti
    campiAllievi.style.display = 'none';
    campiDocente.style.display = 'none';
    campiEsterno.style.display = 'none';
    
    // Mostra in base al tipo
    if (tipo === 'PREN_SALA') {
        campiAllievi.style.display = 'block';
    } else if (tipo === 'PREN_DOCENTE') {
        campiDocente.style.display = 'block';
    } else if (tipo === 'PREN_ESTERNO') {
        campiEsterno.style.display = 'block';
    }
}

function caricaOpzioniPrenotazione() {
    const selectAllievo = document.getElementById('prenotAllievoId');
    const selectDocenteSolo = document.getElementById('prenotDocenteSoloId');
    const selectMateria = document.getElementById('prenotMateriaId');
    
    // Mostra loading
    selectAllievo.innerHTML = '<option value="">Caricamento...</option>';
    selectDocenteSolo.innerHTML = '<option value="">Caricamento...</option>';
    selectMateria.innerHTML = '<option value="">Caricamento...</option>';
    
    // Carica allievi
    fetch('<?= BASE_URL ?>/api_get_helpers.php?type=allievi')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let htmlAllievi = '<option value="">Seleziona allievo...</option>';
                data.data.forEach(a => {
                    htmlAllievi += `<option value="${a.id}">${a.cognome} ${a.nome}</option>`;
                });
                selectAllievo.innerHTML = htmlAllievi;
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('Errore caricamento allievi:', error);
            selectAllievo.innerHTML = '<option value="">Errore caricamento</option>';
        });
    
    // Carica docenti
    fetch('<?= BASE_URL ?>/api_get_helpers.php?type=docenti')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let htmlDocenti = '<option value="">Seleziona docente...</option>';
                data.data.forEach(d => {
                    htmlDocenti += `<option value="${d.id}">${d.cognome} ${d.nome}</option>`;
                });
                selectDocenteSolo.innerHTML = htmlDocenti;
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('Errore caricamento docenti:', error);
            selectDocenteSolo.innerHTML = '<option value="">Errore caricamento</option>';
        });
    
    // Carica materie
    fetch('<?= BASE_URL ?>/api_get_helpers.php?type=materie')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let htmlMaterie = '<option value="">Seleziona materia...</option>';
                data.data.forEach(m => {
                    htmlMaterie += `<option value="${m.id}">${m.nome}</option>`;
                });
                selectMateria.innerHTML = htmlMaterie;
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('Errore caricamento materie:', error);
            selectMateria.innerHTML = '<option value="">Errore caricamento</option>';
        });
}

function salvaPrenotazione() {
    const form = document.getElementById('formNuovaPrenotazione');
    const tipo = document.getElementById('prenotTipo').value;
    
    // Valida tipo selezionato
    if (!tipo) {
        mostraToast('Errore', 'Seleziona un tipo di prenotazione', 'danger');
        return;
    }
    
    // Valida form base
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Disabilita pulsante
    const btnSalva = event.target;
    btnSalva.disabled = true;
    btnSalva.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvataggio...';
    
    // Costruisci payload in base al tipo
    const formData = new FormData(form);
    const data = {
        tipo: tipo,
        aula_id: formData.get('aula_id'),
        ora_inizio: formData.get('ora_inizio'),
        giorno: formData.get('giorno'),
        data: formData.get('data'),
        durata: parseInt(formData.get('durata')),
        note: formData.get('note')
    };
    
    // Aggiungi campi specifici per tipo
    if (tipo === 'PREN_SALA') {
        // Prenotazione Allievi - richiede allievo e materia (NO docente)
        const allieviId = formData.get('allievo_id');
        const materiaId = formData.get('materia_id');
        
        if (!allieviId || !materiaId) {
            mostraToast('Errore', 'Compila tutti i campi richiesti (Allievo, Materia)', 'danger');
            btnSalva.disabled = false;
            btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
            return;
        }
        
        data.allievo_id = parseInt(allieviId);
        data.materia_id = parseInt(materiaId);
        
    } else if (tipo === 'PREN_DOCENTE') {
        // Prenotazione Docente - richiede solo docente
        const docenteSoloId = formData.get('docente_solo_id');
        
        if (!docenteSoloId) {
            mostraToast('Errore', 'Seleziona un docente', 'danger');
            btnSalva.disabled = false;
            btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
            return;
        }
        
        data.docente_id = parseInt(docenteSoloId);
        data.motivo = formData.get('motivo_docente') || '';
        
    } else if (tipo === 'PREN_ESTERNO') {
        // Prenotazione Esterno - richiede nome e cognome, opzionali email/telefono
        const nomeEsterno = formData.get('nome_esterno');
        const cognomeEsterno = formData.get('cognome_esterno');
        
        if (!nomeEsterno || nomeEsterno.trim() === '' || !cognomeEsterno || cognomeEsterno.trim() === '') {
            mostraToast('Errore', 'Inserisci nome e cognome', 'danger');
            btnSalva.disabled = false;
            btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
            return;
        }
        
        data.nome_esterno = nomeEsterno.trim();
        data.cognome_esterno = cognomeEsterno.trim();
        data.email_esterno = formData.get('email_esterno') || '';
        data.telefono_esterno = formData.get('telefono_esterno') || '';
    }
    
    // Converti tipo in tipologia_id (ID reali dal database)
    const tipoToTipologiaId = {
        'PREN_SALA': 6,  // ID 6 = PREN_SALA_ALLIEVI
        'PREN_DOCENTE': 7,  // ID 7 = PREN_DOCENTE
        'PREN_ESTERNO': 8   // ID 8 = PREN_ESTERNO
    };
    
    // Crea FormData invece di JSON
    const formDataToSend = new FormData();
    formDataToSend.append('tipologia_id', tipoToTipologiaId[tipo]);
    formDataToSend.append('aula_id', data.aula_id);
    formDataToSend.append('ora_inizio', data.ora_inizio);
    formDataToSend.append('giorno', data.giorno);
    formDataToSend.append('data', data.data);
    formDataToSend.append('durata', data.durata);
    formDataToSend.append('note', data.note || '');
    
    // Aggiungi campi specifici per tipo con nomi corretti per API
    if (tipo === 'PREN_SALA') {
        formDataToSend.append('allievo_id_pren', data.allievo_id);
        formDataToSend.append('materia_id', data.materia_id);
    } else if (tipo === 'PREN_DOCENTE') {
        formDataToSend.append('docente_id_pren', data.docente_id);
        formDataToSend.append('titolo', data.motivo || 'Prenotazione Docente');
    } else if (tipo === 'PREN_ESTERNO') {
        // Per esterno, prima dobbiamo creare il socio occasionale
        // TODO: Implementare creazione socio occasionale
        mostraToast('Errore', 'Prenotazione esterno richiede implementazione soci occasionali', 'warning');
        btnSalva.disabled = false;
        btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
        return;
    }
    
    // Invia richiesta
    fetch('<?= BASE_URL ?>/api_salva_prenotazione.php', {
        method: 'POST',
        body: formDataToSend
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Chiudi modal
            const modalElement = document.getElementById('nuovaPrenotazioneModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
            
            // Mostra toast successo
            mostraToast('Successo', 'Prenotazione creata correttamente', 'success');
            
            // Ricarica pagina dopo breve pausa
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => {
        mostraToast('Errore', error.message, 'danger');
        btnSalva.disabled = false;
        btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
    });
}

function mostraInfoEvento(eventoId) {
    // Apri modal con info evento/prenotazione
    fetch(`<?= BASE_URL ?>/api_eventi.php?id=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.evento) {
                const evt = data.evento;
                
                // Crea contenuto modal
                let html = `
                    <div class="alert alert-info">
                        <h5><i class="bi bi-calendar-event"></i> ${evt.tipologia_nome}</h5>
                        <p class="mb-0">
                            <strong>Data:</strong> ${new Date(evt.data_evento).toLocaleDateString('it-IT')}<br>
                            <strong>Orario:</strong> ${evt.ora_inizio.substr(0,5)} - ${evt.ora_fine.substr(0,5)}<br>
                            <strong>Aula:</strong> ${evt.aula || 'N/D'}<br>
                            ${evt.allievo ? `<strong>Allievo:</strong> ${evt.allievo}<br>` : ''}
                            ${evt.docente ? `<strong>Docente:</strong> ${evt.docente}<br>` : ''}
                            ${evt.materia ? `<strong>Materia:</strong> ${evt.materia}<br>` : ''}
                            ${evt.note ? `<strong>Note:</strong> ${evt.note}<br>` : ''}
                            <strong>Stato:</strong> ${evt.confermato ? '<span class="badge bg-success">Confermato</span>' : '<span class="badge bg-warning">Da confermare</span>'}
                        </p>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger" onclick="annullaEvento(${eventoId})">
                            <i class="bi bi-trash"></i> Annulla Prenotazione
                        </button>
                    </div>
                `;
                
                // Usa modal info allievo per mostrare info evento
                const modalBody = document.getElementById('modalAllieviBody');
                const modalNome = document.getElementById('modalAllieviNome');
                modalNome.textContent = 'Dettagli Prenotazione';
                modalBody.innerHTML = html;
                
                const modalElement = document.getElementById('infoAllieviModal');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                throw new Error(data.error || 'Evento non trovato');
            }
        })
        .catch(error => {
            mostraToast('Errore', 'Impossibile caricare i dettagli: ' + error.message, 'danger');
        });
}

function annullaEvento(eventoId) {
    if (!confirm('Sei sicuro di voler annullare questa prenotazione?')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>/api_annulla_prenotazione.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ evento_id: eventoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostraToast('Successo', 'Prenotazione annullata', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Errore durante l\'annullamento');
        }
    })
    .catch(error => {
        mostraToast('Errore', error.message, 'danger');
    });
}

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
            
            // Mostra toast successo
            mostraToast('Successo', 'Assenza registrata correttamente', 'success');
            
            // Ricarica pagina dopo breve pausa per visualizzare toast
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => {
        mostraToast('Errore', error.message, 'danger');
        btnConferma.disabled = false;
        btnConferma.innerHTML = '<i class="bi bi-check-circle"></i> Conferma Assenza';
    });
}

// Funzione per mostrare toast (NO auto-hide, utente deve chiudere manualmente)
function mostraToast(titolo, messaggio, tipo = 'info') {
    // Crea container toast se non esiste
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Mappa colori
    const bgColors = {
        'success': 'bg-success',
        'danger': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    
    // Mappa icone
    const icons = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    
    // Crea toast
    const toastId = 'toast_' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgColors[tipo]} text-white">
                <i class="bi ${icons[tipo]} me-2"></i>
                <strong class="me-auto">${titolo}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${messaggio}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Mostra toast SENZA auto-hide - utente deve cliccare X per chiudere
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false  // ← Cambiato: NO auto-hide
    });
    toast.show();
    
    // Rimuovi dopo nascosto manualmente
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
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
