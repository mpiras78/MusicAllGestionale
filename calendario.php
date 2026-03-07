 <?php
require_once 'includes/bootstrap.php';

// Richiede login
$auth->requireLogin();

$page_title = 'Calendario Settimanale';
$current_page = 'calendario';

// Inizializza Controllers
$lezioniCtrl = new LezioniController();
$auleCtrl = new AuleController();
$eventiCtrl = new EventiController();

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

// Carica eventi specifici da eventi_calendario tramite controller
$eventi = $eventiCtrl->getEventiPerData($data_selezionata);

// Aggiungi marker source_type alle lezioni ricorrenti
foreach ($lezioni as &$lez) {
    $lez['source_type'] = 'lezione';
}
unset($lez);

// Unisci lezioni ricorrenti + eventi specifici
$lezioni = array_merge($lezioni, $eventi);

// Organizza lezioni per aula (array di tutte le lezioni per aula)
$calendario = [];
foreach ($lezioni as $lezione) {
    $aula_id = $lezione['aula_id'];
    if (!isset($calendario[$aula_id])) {
        $calendario[$aula_id] = [];
    }
    $calendario[$aula_id][] = $lezione;
}

// LOG: Debug array calendario per aula Piano
$log_file = __DIR__ . '/tests/caso3_debug.log';
$aula_piano_id = 2; // AULA PIANO
if (isset($calendario[$aula_piano_id]) && $data_selezionata == '2026-02-10') {
    $log_msg = "\n=== ARRAY CALENDARIO PER AULA PIANO (ID $aula_piano_id) ===\n";
    $log_msg .= "Numero elementi: " . count($calendario[$aula_piano_id]) . "\n\n";
    foreach ($calendario[$aula_piano_id] as $idx => $elem) {
        $log_msg .= "[$idx] " . ($elem['source_type'] == 'evento' ? 'EVENTO' : 'LEZIONE') . " ID {$elem['id']}: ";
        $log_msg .= "{$elem['ora_inizio']}-{$elem['ora_fine']} ";
        $log_msg .= "(" . ($elem['allievo'] ?? 'N/D') . ")\n";
    }
    $log_msg .= "\n";
    file_put_contents($log_file, $log_msg, FILE_APPEND);
}

// Funzione per calcolare quanti slot da 15' occupa una lezione
function calcolaRowspan($ora_inizio, $ora_fine) {
    $start = strtotime($ora_inizio);
    $end = strtotime($ora_fine);
    $durata_minuti = ($end - $start) / 60;
    $rowspan = ceil($durata_minuti / 15); // Ogni slot = 15 minuti
    return max(1, $rowspan);
}

// Mappa icone strumenti (Bootstrap Icons)
function getIconaMateria($materia) {
    $materia_lower = strtolower($materia);
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
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-week"></i> Calendario Settimanale
            </h1>
            <p class="text-muted mb-0">Visualizzazione programmazione giornaliera</p>
        </div>
        <div class="col-auto">
            <button class="btn me-2" style="background-color: #9C27B0; color: white;" data-bs-toggle="modal" data-bs-target="#modalLezioneProva">
                <i class="bi bi-star"></i> Nuova Lezione
            </button>
            <button class="btn btn-outline-primary btn-print">
                <i class="bi bi-printer"></i> Stampa
            </button>
        </div>
    </div>

    <!-- Navigazione Settimana -->
    <div class="card mb-2">
        <div class="card-body py-1" style="padding-left: 8px; padding-right: 8px;">
            <div class="d-flex align-items-center justify-content-between mb-1">
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
            
            <!-- Contatore + Legenda sulla stessa riga -->
            <div class="mt-2 mb-1 d-flex justify-content-between align-items-center">
                <!-- Contatore a sinistra -->
                <div>
                    <span class="badge bg-primary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="bi bi-calendar-check"></i> <?= count($lezioni) ?> lezioni programmate
                    </span>
                </div>
                
                <!-- Legenda a destra -->
                <div>
                    <h6 class="mb-1" style="font-size: 0.9rem;"><i class="bi bi-info-circle"></i> Legenda</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge" style="background-color: #fff5f0; color: #333; border-left: 3px solid #ff6b35;">
                            Regolare
                        </span>
                        <span class="badge" style="background-color: #e3f2fd; color: #333; border-left: 3px solid #2196f3;">
                            Custom
                        </span>
                        <span class="badge" style="background-color: #e8f5e9; color: #333; border-left: 3px solid #4caf50;">
                            Recupero
                        </span>
                        <span class="badge" style="background-color: #fff9c4; color: #333; border-left: 3px solid #fdd835;">
                            <i class="bi bi-calendar-plus"></i> Prenotazioni
                        </span>
                        <span class="badge" style="background-color: #f3e5f5; color: #333; border-left: 3px solid #9c27b0;">
                            Laboratorio
                        </span>
                        <span class="badge" style="background-color: #e0e0e0; color: #757575; border-left: 3px solid #9e9e9e;">
                            <i class="bi bi-x-circle"></i> Festività/Assenza
                        </span>
                    </div>
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
                        <?php 
                        // Array per tracciare celle occupate da rowspan: [aula_id][slot_index] = rowspan_rimanente
                        $celle_occupate = [];
                        foreach ($aule as $aula) {
                            $celle_occupate[$aula['id']] = [];
                        }
                        ?>
                        <?php foreach ($slots as $slot_index => $slot): ?>
                            <tr>
                                <td class="time-col text-center">
                                    <strong><?= substr($slot['inizio'], 0, 5) ?></strong>
                                </td>
                                <?php foreach ($aule as $aula): ?>
                                    <?php
                                    // Verifica se cella è occupata da rowspan precedente
                                    if (isset($celle_occupate[$aula['id']][$slot_index]) && $celle_occupate[$aula['id']][$slot_index] > 0) {
                                        // LOG: Slot saltato per rowspan
                                        $log_file = __DIR__ . '/tests/caso3_debug.log';
                                        $log_msg = "SLOT SALTATO (rowspan): {$slot['inizio']} in aula {$aula['nome']}\n";
                                        file_put_contents($log_file, $log_msg, FILE_APPEND);
                                        
                                        // Decrementa contatore e salta rendering
                                        $celle_occupate[$aula['id']][$slot_index]--;
                                        continue; // Salta questa <td>, è coperta da rowspan
                                    }
                                    
                                    // Calcola rowspan per lezione/evento che inizia in questo slot
                                    $rowspan = 1;
                                    $lezione_trovata = false;
                                    $evento_trovato_in_slot = null;
                                    
                                    if (isset($calendario[$aula['id']])) {
                                        $slot_start = strtotime($slot['inizio']);
                                        $slot_end = strtotime($slot['fine']);
                                        
                                        // PRIORITÀ: cerca PRIMA eventi (per calcolare il loro rowspan corretto)
                                        foreach ($calendario[$aula['id']] as $lez) {
                                            if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
                                                $evento_start = strtotime($lez['ora_inizio']);
                                                if ($evento_start >= $slot_start && $evento_start < $slot_end) {
                                                    $evento_trovato_in_slot = $lez;
                                                    $rowspan = calcolaRowspan($lez['ora_inizio'], $lez['ora_fine']);
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        // Se NON c'è evento, cerca lezione che inizia qui
                                        if (!$evento_trovato_in_slot) {
                                            foreach ($calendario[$aula['id']] as $lez) {
                                                $lezione_start = strtotime($lez['ora_inizio']);
                                                if ($lezione_start >= $slot_start && $lezione_start < $slot_end) {
                                                    if (!isset($lez['source_type']) || $lez['source_type'] != 'evento') {
                                                        // Lezione inizia in questo slot
                                                        // FIX: Se lezione annullata, rowspan=1 (non occupa slot successivi)
                                                        $is_annullata_lez = (isset($lez['attiva']) && $lez['attiva'] == 0) || $giorno_festivita;
                                                        $rowspan = $is_annullata_lez ? 1 : calcolaRowspan($lez['ora_inizio'], $lez['ora_fine']);
                                                        $lezione_trovata = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Marca celle successive come occupate, MA solo se NON c'è un evento che inizia lì
                                        for ($i = 1; $i < $rowspan; $i++) {
                                            $next_slot_index = $slot_index + $i;
                                            $next_slot_start = strtotime($slots[$next_slot_index]['inizio']);
                                            $next_slot_end = strtotime($slots[$next_slot_index]['fine']);
                                            
                                            // Controlla se c'è un evento che inizia in quel slot
                                            $evento_inizia_li = false;
                                            foreach ($calendario[$aula['id']] as $lez) {
                                                if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
                                                    $evt_start = strtotime($lez['ora_inizio']);
                                                    if ($evt_start >= $next_slot_start && $evt_start < $next_slot_end) {
                                                        $evento_inizia_li = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // Marca come occupato SOLO se NON c'è evento
                                            if (!$evento_inizia_li) {
                                                $celle_occupate[$aula['id']][$next_slot_index] = $rowspan - $i;
                                            }
                                        }
                                    }
                                    ?>
                                    <td class="calendario-cell calendario-cell-hoverable" 
                                        rowspan="<?= $rowspan ?>"
                                        data-aula-id="<?= $aula['id'] ?>" 
                                        data-aula-nome="<?= e($aula['nome']) ?>"
                                        data-ora="<?= $slot['inizio'] ?>"
                                        data-giorno="<?= $giorno_selezionato ?>"
                                        data-data="<?= $data_selezionata ?>">
                                        <?php
                                        // Trova lezione/evento per questo slot
                                        // IMPORTANTE: Cerca eventi sovrapposti in TUTTA la durata della lezione che inizia qui
                                        $lezione_slot = null;
                                        $evento_slot = null;
                                        if (isset($calendario[$aula['id']])) {
                                            $slot_start = strtotime($slot['inizio']);
                                            $slot_end = strtotime($slot['fine']);
                                            
                                            // LOG: Debug slot corrente
                                            $log_file = __DIR__ . '/tests/caso3_debug.log';
                                            
                                            // FILTRA solo lezioni/eventi che hanno relazione con questo slot
                                            $elementi_rilevanti = array_filter($calendario[$aula['id']], function($lez) use ($slot_start, $slot_end) {
                                                $lez_start = strtotime($lez['ora_inizio']);
                                                $lez_end = strtotime($lez['ora_fine']);
                                                // FIX: usa <= per includere eventi che iniziano ESATTAMENTE alla fine dello slot
                                                return ($lez_start <= $slot_end && $lez_end > $slot_start);
                                            });
                                            
                                            if (count($elementi_rilevanti) > 0) {
                                                $num_elementi = count($elementi_rilevanti);
                                                $log_msg = "DEBUG Slot: {$slot['inizio']} in aula {$aula['nome']} ($num_elementi elementi)\n";
                                                file_put_contents($log_file, $log_msg, FILE_APPEND);
                                            }
                                            
                                            // PRIORITÀ: Cerca PRIMA eventi, POI lezioni
                                            // Se evento inizia qui, mostra solo l'evento (ignora lezione annullata)
                                            foreach ($elementi_rilevanti as $lez) {
                                                if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
                                                    $evento_start = strtotime($lez['ora_inizio']);
                                                    if ($evento_start >= $slot_start && $evento_start < $slot_end) {
                                                        $evento_slot = $lez;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // Solo se NON c'è evento, cerca lezione
                                            if (!$evento_slot) {
                                                foreach ($elementi_rilevanti as $lez) {
                                                    $lezione_start = strtotime($lez['ora_inizio']);
                                                    $inizia_in_slot = ($lezione_start >= $slot_start && $lezione_start < $slot_end);
                                                    
                                                    if (!isset($lez['source_type']) || $lez['source_type'] != 'evento') {
                                                        if ($inizia_in_slot) {
                                                            $lezione_slot = $lez;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Caso 1: Solo lezione (o evento senza lezione)
                                        if ($lezione_slot && !$evento_slot):
                                            $icona = getIconaMateria($lezione_slot['materia']);
                                            // Determina se lezione è annullata (attiva = 0 O giorno festività)
                                            $is_annullata = (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) || $giorno_festivita;
                                            
                                            if ($is_annullata):
                                                // Lezione ANNULLATA: mostra solo icona piccola + pulsante +
                                                $motivo = $giorno_festivita ? $giorno_festivita['nome'] : 'Assenza';
                                                $tooltip = e($lezione_slot['allievo']) . ' - ' . e($lezione_slot['materia']) . ' (' . $motivo . ')';
                                        ?>
                                                <!-- Icona lezione annullata piccola in alto a sinistra -->
                                                <div style="position: absolute; top: 5px; left: 5px; z-index: 10;">
                                                    <i class="bi bi-calendar-x text-muted" 
                                                       style="font-size: 1.2rem; opacity: 0.5;" 
                                                       title="<?= $tooltip ?>"></i>
                                                </div>
                                                
                                                <!-- Slot disponibile per prenotazione -->
                                                <div class="empty-slot-add" 
                                                     onclick="apriModalNuovaPrenotazione(<?= $aula['id'] ?>, '<?= e($aula['nome']) ?>', '<?= $slot['inizio'] ?>', '<?= $giorno_selezionato ?>', '<?= $data_selezionata ?>', '<?= $lezione_slot['ora_inizio'] ?>', '<?= $lezione_slot['ora_fine'] ?>')">
                                                    <i class="bi bi-plus-circle"></i>
                                                </div>
                                        <?php
                                            else:
                                                // Lezione ATTIVA: mostra card completa
                                        ?>
                                            <div class="lezione-slot tipo-<?= e($lezione_slot['tipo']) ?>" 
                                                 data-lezione-id="<?= $lezione_slot['id'] ?>"
                                                 title="<?= e($lezione_slot['allievo']) ?> - <?= e($lezione_slot['materia']) ?>">
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($lezione_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($lezione_slot['ora_fine'])) ?>
                                                </div>
                                                <div class="lezione-header">
                                                    <span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona ?></span>
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
                                        <?php
                                            endif; // Fine if ($is_annullata)
                                        endif; // Fine if ($lezione_slot && !$evento_slot)
                                        ?>
                                        <?php if ($evento_slot && !$lezione_slot): ?>
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
                                                    $icona_prenotazione = '👨‍🎓';
                                                    $classe_icona_pren = 'tipo-allievi';
                                                    $is_prenotazione = true;
                                                } elseif (strpos($tipo_lower, 'pren_docente') !== false) {
                                                    $tipo_css = 'prenotazione-docente';
                                                    $icona_prenotazione = '🎓';
                                                    $classe_icona_pren = 'tipo-docente';
                                                    $is_prenotazione = true;
                                                } elseif (strpos($tipo_lower, 'pren_esterno') !== false) {
                                                    $tipo_css = 'prenotazione-esterno';
                                                    $icona_prenotazione = '👤';
                                                    $classe_icona_pren = 'tipo-esterno';
                                                    $is_prenotazione = true;
                                                }
                                            }
                                            
                                            // TUTTI gli eventi (recuperi E prenotazioni) vanno su mostraInfoEvento
                                            $onclick_action = "mostraInfoEvento({$evento_slot['id']}); return false;";
                                        ?>
                                            <div class="lezione-slot tipo-<?= e($tipo_css) ?><?= $classe_annullata ?>" 
                                                 data-lezione-id="<?= $evento_slot['id'] ?>"
                                                 data-evento-id="<?= $evento_slot['id'] ?>"
                                                 title="<?= e($evento_slot['allievo'] ?: $evento_slot['docente'] ?: 'Prenotazione') ?>"
                                                 style="cursor: pointer;"
                                                 onclick="<?= $onclick_action ?>">
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($evento_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($evento_slot['ora_fine'])) ?>
                                                </div>
                                                
                                            <?php if ($is_prenotazione): ?>
                                                    <!-- Layout uniforme per PRENOTAZIONI -->
                                                    <div class="lezione-header">
                                                        <span class="lezione-allievo">
                                                            <?php if ($icona_prenotazione): ?>
                                                                <span class="prenotazione-tipo-icon <?= $classe_icona_pren ?>" style="font-size: 1.1rem;"><?= $icona_prenotazione ?></span>
                                                            <?php endif; ?>
                                                            PRENOTAZIONE
                                                        </span>
                                                        <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                            <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="lezione-info-row">
                                                        <div class="lezione-docente">
                                                            <i class="bi bi-person-fill"></i> 
                                                            <?= e($evento_slot['allievo'] ?: $evento_slot['docente'] ?: 'Partecipante') ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Layout standard per RECUPERI e altri eventi -->
                                                    <div class="lezione-header">
                                                        <span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona ?></span>
                                                        <span class="lezione-allievo">
                                                            <?= e($evento_slot['allievo'] ?: ($evento_slot['docente'] ?: 'Evento')) ?>
                                                            <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                                <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="lezione-info-row">
                                                        <div class="lezione-docente">
                                                            <i class="bi bi-person-fill"></i> <?= e($evento_slot['docente']) ?>
                                                        </div>
                                                        <?php if ($evento_slot['materia']): ?>
                                                            <div class="lezione-materia-inline">
                                                                <?= e($evento_slot['materia']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($evento_slot['note']): ?>
                                                    <div class="lezione-note-badge">
                                                        <i class="bi bi-sticky" title="<?= e($evento_slot['note']) ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <?php elseif ($lezione_slot && $evento_slot): ?>
                                            <!-- Caso 3: Evento sopra lezione annullata - SOLO ICONA per lezione annullata -->
                                            <?php
                                            // LOG: Caso 3 rilevato
                                            $log_msg = "\n=== CASO 3 RILEVATO ===\n";
                                            $log_msg .= "Data: {$data_selezionata}, Aula: {$aula['nome']}, Slot: {$slot['inizio']}\n";
                                            $log_msg .= "Lezione: " . json_encode([
                                                'id' => $lezione_slot['id'],
                                                'allievo' => $lezione_slot['allievo'],
                                                'materia' => $lezione_slot['materia'],
                                                'ora_inizio' => $lezione_slot['ora_inizio'],
                                                'ora_fine' => $lezione_slot['ora_fine'],
                                                'attiva' => $lezione_slot['attiva'] ?? 'N/D'
                                            ]) . "\n";
                                            $log_msg .= "Evento: " . json_encode([
                                                'id' => $evento_slot['id'],
                                                'tipo' => $evento_slot['tipo'] ?? 'N/D',
                                                'allievo' => $evento_slot['allievo'] ?? 'N/D',
                                                'ora_inizio' => $evento_slot['ora_inizio'],
                                                'ora_fine' => $evento_slot['ora_fine']
                                            ]) . "\n\n";
                                            file_put_contents($log_file, $log_msg, FILE_APPEND);
                                            
                                            $icona_lez = getIconaMateria($lezione_slot['materia']);
                                            $is_annullata_lez = (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) || $giorno_festivita;
                                            
                                            $icona_evt = getIconaMateria($evento_slot['materia']);
                                            $tipo_css_evt = 'regolare';
                                            $icona_prenotazione_evt = '';
                                            $classe_icona_pren_evt = '';
                                            $is_prenotazione_evt = false;
                                            if (isset($evento_slot['tipo'])) {
                                                $tipo_lower = strtolower($evento_slot['tipo']);
                                                if (strpos($tipo_lower, 'recupero') !== false) {
                                                    $tipo_css_evt = 'recupero';
                                                } elseif (strpos($tipo_lower, 'pren_sala') !== false) {
                                                    $tipo_css_evt = 'prenotazione-allievi';
                                                    $icona_prenotazione_evt = '👨‍🎓';
                                                    $classe_icona_pren_evt = 'tipo-allievi';
                                                    $is_prenotazione_evt = true;
                                                } elseif (strpos($tipo_lower, 'pren_docente') !== false) {
                                                    $tipo_css_evt = 'prenotazione-docente';
                                                    $icona_prenotazione_evt = '🎓';
                                                    $classe_icona_pren_evt = 'tipo-docente';
                                                    $is_prenotazione_evt = true;
                                                } elseif (strpos($tipo_lower, 'pren_esterno') !== false) {
                                                    $tipo_css_evt = 'prenotazione-esterno';
                                                    $icona_prenotazione_evt = '👤';
                                                    $classe_icona_pren_evt = 'tipo-esterno';
                                                    $is_prenotazione_evt = true;
                                                }
                                            }
                                            
                                            // Info tooltip lezione annullata
                                            $tooltip_annullata = e($lezione_slot['allievo']) . ' - ' . e($lezione_slot['materia']) . ' (ANNULLATA)';
                                        ?>
                                            <!-- Piccola icona lezione annullata in alto a sinistra -->
                                            <div class="lezione-annullata-indicator" 
                                                 title="<?= $tooltip_annullata ?>">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </div>
                                            
                                            <!-- Evento occupa TUTTO lo spazio - CLICCABILE (SENZA classe slot-sovrapposto) -->
                                            <div class="lezione-slot tipo-<?= e($tipo_css_evt) ?>"
                                                 data-lezione-id="<?= $evento_slot['id'] ?>"
                                                 data-evento-id="<?= $evento_slot['id'] ?>"
                                                 title="<?= e($evento_slot['allievo'] ?: ($evento_slot['docente'] ?: 'Prenotazione')) ?>"
                                                 style="cursor: pointer;"
                                                 onclick="mostraInfoEvento(<?= $evento_slot['id'] ?>); return false;">
                                                <div class="lezione-orario-badge">
                                                    <?= date('H:i', strtotime($evento_slot['ora_inizio'])) ?>-<?= date('H:i', strtotime($evento_slot['ora_fine'])) ?>
                                                </div>
                                                
                                                <?php if ($is_prenotazione_evt): ?>
                                                    <!-- Layout uniforme per PRENOTAZIONI -->
                                                    <div class="lezione-header">
                                                        <span class="lezione-allievo">
                                                            <?php if ($icona_prenotazione_evt): ?>
                                                                <span class="prenotazione-tipo-icon <?= $classe_icona_pren_evt ?>" style="font-size: 1.1rem;"><?= $icona_prenotazione_evt ?></span>
                                                            <?php endif; ?>
                                                            PRENOTAZIONE
                                                        </span>
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
                                                        <span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona_evt ?></span>
                                                        <span class="lezione-allievo">
                                                            <?= e($evento_slot['allievo'] ?: ($evento_slot['docente'] ?: 'Evento')) ?>
                                                            <?php if (isset($evento_slot['confermato']) && $evento_slot['confermato'] == 0): ?>
                                                                <i class="bi bi-clock-history text-warning" title="Da confermare"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="lezione-info-row">
                                                        <div class="lezione-docente">
                                                            <i class="bi bi-person-fill"></i> <?= e($evento_slot['docente']) ?>
                                                        </div>
                                                        <?php if ($evento_slot['materia']): ?>
                                                            <div class="lezione-materia-inline">
                                                                <?= e($evento_slot['materia']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
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

<!-- Modal Conferma Annullamento -->
<div class="modal fade" id="confermaAnnullamentoModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog" style="z-index: 1060;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Conferma Annullamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Sei sicuro di voler annullare questo evento?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>Attenzione:</strong> Questa azione non può essere annullata.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> No, torna indietro
                </button>
                <button type="button" class="btn btn-danger" id="btnConfermaAnnullamento">
                    <i class="bi bi-trash"></i> Sì, annulla evento
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
                    
                    <!-- Select Ora Inizio (visibile solo per lezioni annullate con più slot) -->
                    <div id="selectOraContainer" style="display: none;" class="mb-3">
                        <label class="form-label fw-bold">Orario Inizio *</label>
                        <select class="form-select" id="prenotOraSelect" onchange="document.getElementById('prenotOra').value = this.value">
                            <option value="">Seleziona orario...</option>
                        </select>
                        <small class="text-muted">Seleziona l'orario di inizio desiderato tra gli slot disponibili</small>
                    </div>
                    
                    <!-- Tipo Prenotazione -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo Prenotazione *</label>
                        <select class="form-select" id="prenotTipo" name="tipo" required onchange="cambiaTipoPrenotazione()">
                            <option value="">Seleziona tipo...</option>
                            <option value="PREN_SALA">👨‍🎓 Prenotazione Allievi (lezione)</option>
                            <option value="PREN_DOCENTE">🎓 Prenotazione Docente (personale)</option>
                            <option value="PREN_ESTERNO">👤 Prenotazione Esterno</option>
                        </select>
                    </div>
                    
                    <!-- Campi Prenotazione Allievi -->
                    <div id="campiAllievi" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Allievo *</label>
                            <select class="form-select" id="prenotAllievoId" name="allievo_id">
                                <option value="">Caricamento...</option>
                            </select>
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
                        <!-- Select Socio Esistente -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Socio Esterno *</label>
                            <select class="form-select" id="prenotSocioEsternoId" name="socio_esterno_id" onchange="toggleNuovoSocioEsterno()">
                                <option value="">Caricamento...</option>
                            </select>
                        </div>
                        
                        <!-- Campi Nuovo Socio (nascosti di default) -->
                        <div id="campiNuovoSocioEsterno" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Inserisci i dati del nuovo socio esterno
                            </div>
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

function apriModalNuovaPrenotazione(aulaId, aulaNome, ora, giorno, data, oraInizioLezione = null, oraFineLezione = null) {
    // Popola info slot
    const giornoNice = {
        'lunedi': 'Lunedì',
        'martedi': 'Martedì',
        'mercoledi': 'Mercoledì',
        'giovedi': 'Giovedì',
        'venerdi': 'Venerdì',
        'sabato': 'Sabato'
    };
    
    // Se viene da lezione annullata, mostra range orario disponibile
    let infoText = `Aula: ${aulaNome} - ${giornoNice[giorno]} ${new Date(data).toLocaleDateString('it-IT')}`;
    if (oraInizioLezione && oraFineLezione) {
        infoText += ` - Slot disponibile: ${oraInizioLezione.substr(0,5)}-${oraFineLezione.substr(0,5)}`;
    } else {
        infoText += ` alle ${ora}`;
    }
    
    document.getElementById('slotInfo').textContent = infoText;
    
    // Popola campi hidden
    document.getElementById('prenotAulaId').value = aulaId;
    document.getElementById('prenotGiorno').value = giorno;
    document.getElementById('prenotData').value = data;
    
    // Gestisci select ora di inizio
    const selectOraContainer = document.getElementById('selectOraContainer');
    const selectOra = document.getElementById('prenotOraSelect');
    
    if (oraInizioLezione && oraFineLezione) {
        // Mostra select con slot da 15 minuti
        selectOraContainer.style.display = 'block';
        document.getElementById('prenotOra').value = ''; // Sarà popolato da select
        
        // Genera opzioni slot da 15 minuti
        const slotOptions = generaSlotOrari(oraInizioLezione, oraFineLezione);
        let htmlOptions = '<option value="">Seleziona orario...</option>';
        slotOptions.forEach(slot => {
            htmlOptions += `<option value="${slot}">${slot.substr(0,5)}</option>`;
        });
        selectOra.innerHTML = htmlOptions;
        selectOra.required = true;
    } else {
        // Nascondi select, usa ora passata
        selectOraContainer.style.display = 'none';
        document.getElementById('prenotOra').value = ora;
        selectOra.required = false;
    }
    
    // Carica select allievi, docenti, materie
    caricaOpzioniPrenotazione();
    
    // Apri modal
    const modalElement = document.getElementById('nuovaPrenotazioneModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Genera slot orari da 15 minuti tra ora inizio e fine
function generaSlotOrari(oraInizio, oraFine) {
    const slots = [];
    let current = new Date(`2000-01-01 ${oraInizio}`);
    const end = new Date(`2000-01-01 ${oraFine}`);
    
    while (current < end) {
        const hours = current.getHours().toString().padStart(2, '0');
        const minutes = current.getMinutes().toString().padStart(2, '0');
        slots.push(`${hours}:${minutes}:00`);
        current.setMinutes(current.getMinutes() + 15);
    }
    
    return slots;
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

function toggleNuovoSocioEsterno() {
    const selectSocio = document.getElementById('prenotSocioEsternoId');
    const campiNuovo = document.getElementById('campiNuovoSocioEsterno');
    
    // Se selezionato "NUOVO", mostra campi
    if (selectSocio.value === 'NUOVO') {
        campiNuovo.style.display = 'block';
    } else {
        campiNuovo.style.display = 'none';
        // Pulisci campi quando si seleziona un socio esistente
        document.getElementById('prenotNomeEsterno').value = '';
        document.getElementById('prenotCognomeEsterno').value = '';
        document.getElementById('prenotEmailEsterno').value = '';
        document.getElementById('prenotTelefonoEsterno').value = '';
    }
}

function caricaOpzioniPrenotazione() {
    const selectAllievo = document.getElementById('prenotAllievoId');
    const selectDocenteSolo = document.getElementById('prenotDocenteSoloId');
    const selectSocioEsterno = document.getElementById('prenotSocioEsternoId');
    
    // Mostra loading
    selectAllievo.innerHTML = '<option value="">Caricamento...</option>';
    selectDocenteSolo.innerHTML = '<option value="">Caricamento...</option>';
    selectSocioEsterno.innerHTML = '<option value="">Caricamento...</option>';
    
    // Carica allievi
    fetch('<?= BASE_URL ?>/api/api_get_helpers.php?type=allievi')
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
    fetch('<?= BASE_URL ?>/api/api_get_helpers.php?type=docenti')
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
    
    // Carica soci esterni
    fetch('<?= BASE_URL ?>/api/api_get_soci_esterni.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let htmlSoci = '<option value="">Seleziona socio...</option>';
                data.data.forEach(s => {
                    htmlSoci += `<option value="${s.id}">${s.cognome} ${s.nome}</option>`;
                });
                htmlSoci += '<option value="NUOVO">➕ Nuovo Socio Esterno</option>';
                selectSocioEsterno.innerHTML = htmlSoci;
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('Errore caricamento soci esterni:', error);
            selectSocioEsterno.innerHTML = '<option value="">Errore caricamento</option>';
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
        // Prenotazione Allievi - richiede solo allievo (materia rimossa)
        const allieviId = formData.get('allievo_id');
        
        if (!allieviId) {
            mostraToast('Errore', 'Seleziona un allievo', 'danger');
            btnSalva.disabled = false;
            btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
            return;
        }
        
        data.allievo_id = parseInt(allieviId);
        
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
        // Prenotazione Esterno - può essere socio esistente o nuovo
        const socioEsternoId = formData.get('socio_esterno_id');
        
        if (!socioEsternoId) {
            mostraToast('Errore', 'Seleziona un socio esterno', 'danger');
            btnSalva.disabled = false;
            btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
            return;
        }
        
        if (socioEsternoId === 'NUOVO') {
            // Nuovo socio - valida nome e cognome
            const nomeEsterno = formData.get('nome_esterno');
            const cognomeEsterno = formData.get('cognome_esterno');
            
            if (!nomeEsterno || nomeEsterno.trim() === '' || !cognomeEsterno || cognomeEsterno.trim() === '') {
                mostraToast('Errore', 'Inserisci nome e cognome del nuovo socio', 'danger');
                btnSalva.disabled = false;
                btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Crea Prenotazione';
                return;
            }
            
            data.nome_esterno = nomeEsterno.trim();
            data.cognome_esterno = cognomeEsterno.trim();
            data.email_esterno = formData.get('email_esterno') || '';
            data.telefono_esterno = formData.get('telefono_esterno') || '';
        } else {
            // Socio esistente - passa solo ID
            data.socio_esterno_id = parseInt(socioEsternoId);
        }
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
    } else if (tipo === 'PREN_DOCENTE') {
        formDataToSend.append('docente_id_pren', data.docente_id);
        formDataToSend.append('titolo', data.motivo || 'Prenotazione Docente');
    } else if (tipo === 'PREN_ESTERNO') {
        // Per esterno, invia dati per creazione/utilizzo partecipante
        formDataToSend.append('nome_esterno', data.nome_esterno);
        formDataToSend.append('cognome_esterno', data.cognome_esterno);
        formDataToSend.append('email_esterno', data.email_esterno);
        formDataToSend.append('telefono_esterno', data.telefono_esterno);
    }
    
    // Invia richiesta
    fetch('<?= BASE_URL ?>/api/api_salva_prenotazione.php', {
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
                
                // Ricarica pagina dopo breve pausa (SENZA attendere chiusura toast)
                setTimeout(() => location.reload(), 800);
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
    fetch(`<?= BASE_URL ?>/api/api_eventi.php?id=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const evt = data.data;
                
                // Determina se è recupero o prenotazione
                const isRecupero = evt.tipologia_categoria === 'recupero' || evt.tipologia_nome.toLowerCase().includes('recupero');
                const isPrenotazione = evt.tipologia_categoria === 'prenotazione' || evt.tipologia_nome.toLowerCase().includes('prenotazione');
                
                // Titolo modal e pulsante in base al tipo
                let titoloModal = evt.tipologia_nome;
                let testoPulsante = 'Annulla Evento';
                
                if (isRecupero) {
                    titoloModal = 'Dettagli Recupero';
                    testoPulsante = 'Annulla Recupero';
                } else if (isPrenotazione) {
                    titoloModal = 'Dettagli Prenotazione';
                    testoPulsante = 'Annulla Prenotazione';
                }
                
                // Nome partecipante (recupera da API con formato corretto)
                let nomePartecipante = '';
                if (evt.partecipante_nome) {
                    nomePartecipante = evt.partecipante_nome;
                } else if (evt.allievo_id && evt.docente_nome) {
                    // Se allievo_id è presente, docente_nome contiene in realtà "Cognome Nome" dell'allievo
                    nomePartecipante = evt.docente_nome;
                } else if (evt.docente_nome) {
                    nomePartecipante = evt.docente_nome;
                }
                
                // Crea contenuto modal - FORMATO SPECIALE PER RECUPERI
                let html = '';
                
                if (isRecupero) {
                    // RECUPERI: UPPERCASE + data assenza
                    html = `
                        <div class="alert alert-success">
                            <h5><i class="bi bi-calendar-check"></i> RECUPERO LEZIONE</h5>
                            <p class="mb-0" style="text-transform: uppercase;">
                                ${nomePartecipante ? `<strong>ALLIEVO:</strong> ${nomePartecipante}<br>` : ''}
                                <strong>DATA RECUPERO:</strong> ${new Date(evt.data_evento).toLocaleDateString('it-IT')}<br>
                                <strong>ORARIO:</strong> ${evt.ora_inizio.substr(0,5)} - ${evt.ora_fine.substr(0,5)}<br>
                                <strong>AULA:</strong> ${evt.aula_nome || 'N/D'}<br>
                                ${evt.materia_nome ? `<strong>MATERIA:</strong> ${evt.materia_nome}<br>` : ''}
                                ${evt.assenza_data ? `<strong>ASSENZA DEL:</strong> ${new Date(evt.assenza_data).toLocaleDateString('it-IT')} 
                                    ${evt.assenza_causata_da ? `(${evt.assenza_causata_da === 'allievo' ? 'causata da allievo' : 'causata da docente'})` : ''}<br>` : ''}
                            </p>
                            ${evt.note ? `<p class="mb-0 mt-2"><strong>NOTE:</strong><br>${evt.note}</p>` : ''}
                        </div>
                    `;
                } else {
                    // PRENOTAZIONI: formato standard
                    html = `
                        <div class="alert alert-info">
                            <h5><i class="bi bi-calendar-event"></i> ${evt.tipologia_nome}</h5>
                            <p class="mb-0">
                                ${nomePartecipante ? `<strong>${evt.allievo_id ? 'Allievo' : 'Docente'}:</strong> ${nomePartecipante}<br>` : ''}
                                <strong>Data:</strong> ${new Date(evt.data_evento).toLocaleDateString('it-IT')}<br>
                                <strong>Orario:</strong> ${evt.ora_inizio.substr(0,5)} - ${evt.ora_fine.substr(0,5)}<br>
                                <strong>Aula:</strong> ${evt.aula_nome || 'N/D'}<br>
                                ${evt.materia_nome ? `<strong>Materia:</strong> ${evt.materia_nome}<br>` : ''}
                                <strong>Stato:</strong> ${evt.confermato ? '<span class="badge bg-success">Confermato</span>' : '<span class="badge bg-warning">Da confermare</span>'}
                            </p>
                            ${evt.note ? `<p class="mb-0 mt-2"><strong>Note:</strong><br>${evt.note}</p>` : ''}
                        </div>
                    `;
                }
                html += `
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="apriModalModificaEvento(${eventoId})">
                            <i class="bi bi-pencil"></i> Modifica Orario/Sala
                        </button>
                        <button class="btn btn-danger" onclick="annullaEvento(${eventoId})">
                            <i class="bi bi-trash"></i> ${testoPulsante}
                        </button>
                    </div>
                `;
                
                // Usa modal info allievo per mostrare info evento
                const modalBody = document.getElementById('modalAllieviBody');
                const modalNome = document.getElementById('modalAllieviNome');
                modalNome.textContent = titoloModal;
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
    // Memorizza evento ID per conferma
    window.eventoIdDaAnnullare = eventoId;
    
    // Apri modal conferma annullamento
    const modalConferma = new bootstrap.Modal(document.getElementById('confermaAnnullamentoModal'));
    modalConferma.show();
}

function apriModalModificaEvento(eventoId) {
    // Carica dati evento
    fetch(`<?= BASE_URL ?>/api/api_eventi.php?id=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const evt = data.data;
                
                // Popola form modifica
                document.getElementById('modEvento_id').value = eventoId;
                document.getElementById('modEvento_oraInizio').value = evt.ora_inizio;
                document.getElementById('modEvento_oraFine').value = evt.ora_fine;
                document.getElementById('modEvento_aulaId').value = evt.aula_id;
                
                // Apri modal
                const modal = new bootstrap.Modal(document.getElementById('modificaEventoModal'));
                modal.show();
            } else {
                throw new Error(data.error || 'Evento non trovato');
            }
        })
        .catch(error => {
            mostraToast('Errore', 'Impossibile caricare i dettagli: ' + error.message, 'danger');
        });
}

function salvaModificaEvento() {
    const eventoId = document.getElementById('modEvento_id').value;
    const oraInizio = document.getElementById('modEvento_oraInizio').value;
    const oraFine = document.getElementById('modEvento_oraFine').value;
    const aulaId = document.getElementById('modEvento_aulaId').value;
    
    if (!oraInizio || !oraFine || !aulaId) {
        mostraToast('Errore', 'Compila tutti i campi', 'danger');
        return;
    }
    
    const btnSalva = event.target;
    btnSalva.disabled = true;
    btnSalva.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvataggio...';
    
    const formData = new FormData();
    formData.append('evento_id', eventoId);
    formData.append('ora_inizio', oraInizio);
    formData.append('ora_fine', oraFine);
    formData.append('aula_id', aulaId);
    
    fetch('<?= BASE_URL ?>/api/api_modifica_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modificaEventoModal'));
            if (modal) modal.hide();
            
            mostraToast('Successo', 'Prenotazione modificata', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => {
        mostraToast('Errore', error.message, 'danger');
        btnSalva.disabled = false;
        btnSalva.innerHTML = '<i class="bi bi-check-circle"></i> Salva Modifiche';
    });
}

// Gestisci click su pulsante conferma annullamento
document.addEventListener('DOMContentLoaded', function() {
    const btnConferma = document.getElementById('btnConfermaAnnullamento');
    if (btnConferma) {
        btnConferma.addEventListener('click', function() {
            const eventoId = window.eventoIdDaAnnullare;
            if (!eventoId) return;
            
            // Disabilita pulsante
            const originalHTML = btnConferma.innerHTML;
            btnConferma.disabled = true;
            btnConferma.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Annullamento...';
            
            fetch(`<?= BASE_URL ?>/api/api_annulla_prenotazione.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ evento_id: eventoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Chiudi entrambe le modal
                    const modalConferma = bootstrap.Modal.getInstance(document.getElementById('confermaAnnullamentoModal'));
                    const modalInfo = bootstrap.Modal.getInstance(document.getElementById('infoAllieviModal'));
                    if (modalConferma) modalConferma.hide();
                    if (modalInfo) modalInfo.hide();
                    
                    mostraToast('Successo', 'Prenotazione annullata', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Errore durante l\'annullamento');
                }
            })
            .catch(error => {
                mostraToast('Errore', error.message, 'danger');
                btnConferma.disabled = false;
                btnConferma.innerHTML = originalHTML;
            });
        });
    }
});

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
    fetch(`<?= BASE_URL ?>/api/api_get_info_allievo.php?allievo_id=${allieviId}`)
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
    
    // FIX: Rimuovi tutti i backdrop rimasti (bug Bootstrap)
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
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
    
    // Ottieni token CSRF dalla meta tag o dal DOM
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        // Fallback: cerca nel primo form hidden field
        csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    }
    
    // Invia richiesta
    fetch('<?= BASE_URL ?>/api/api_salva_assenza_calendario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: csrfToken,
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
    
    // FIX: Pulisci backdrop e body quando QUALSIASI modal si chiude
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Rimuovi TUTTI i backdrop rimasti
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            
            // Ripristina body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
});
</script>

<?php include 'includes/modals/modal_lezione_prova.php'; ?>
<?php include 'includes/modals/modal_modifica_evento.php'; ?>

<?php include 'includes/footer.php'; ?>
