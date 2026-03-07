<?php
require_once 'includes/bootstrap.php';

$auth->requireLogin();

$page_title = 'Gestione Iscrizioni';
$current_page = 'iscrizioni';

$iscrizioniCtrl = new IscrizioniController();

// Anno scolastico corrente
$anno_corrente = date('Y') . '-' . (date('Y') + 1);

// Determina il mese e anno selezionati
$mese_selezionato = $_GET['mese'] ?? date('Y-m');
list($anno_selezionato, $mese_num) = explode('-', $mese_selezionato) + ['', ''];

if (!$anno_selezionato || !$mese_num) {
    $anno_selezionato = (int)date('Y');
    $mese_num = (int)date('m');
} else {
    $anno_selezionato = (int)$anno_selezionato;
    $mese_num = (int)$mese_num;
}

$mese_display = sprintf('%04d-%02d', $anno_selezionato, $mese_num);

// Carica iscrizioni da database tramite controller
$iscrizioni = $iscrizioniCtrl->getIscrizioniPerMese($anno_selezionato, $mese_num);

$tipi_corso = $iscrizioniCtrl->getTipiCorso();
$statistiche = $iscrizioniCtrl->getStatistiche($anno_corrente);

// Assicura che siano array
if (!is_array($iscrizioni)) $iscrizioni = [];
if (!is_array($tipi_corso)) $tipi_corso = [];
if (!is_array($statistiche)) $statistiche = [];

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-card-checklist"></i> Gestione Iscrizioni
            </h1>
            <p class="text-muted mb-0">Anno Scolastico <?= $anno_corrente ?></p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIscrizioneModal">
                <i class="bi bi-plus-circle"></i> Nuova Iscrizione
            </button>
        </div>
    </div>

    <!-- Filtro Mese -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <label class="form-label fw-bold mb-2">
                        <i class="bi bi-calendar"></i> Filtra per Mese Anno Accademico
                    </label>
                    <div class="input-group mb-2">
                        <select class="form-select" id="filtroMese" onchange="cambiaFiltroMese()">
                            <?php
                            // Calcola l'anno accademico corrente
                            $mese_corrente = (int)date('m');
                            $anno_corrente = (int)date('Y');
                            
                            // Anno accademico (inizia a Settembre)
                            $anno_accademico_inizio = ($mese_corrente >= 9) ? $anno_corrente : $anno_corrente - 1;
                            
                            // Genera opzioni per i 12 mesi (Settembre - Luglio)
                            $mesi_it = ['', 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
                            
                            for ($i = 0; $i < 12; $i++) {
                                $mese_num = 9 + $i;
                                $anno_opt = $anno_accademico_inizio;
                                if ($mese_num > 12) {
                                    $mese_num -= 12;
                                    $anno_opt++;
                                }
                                
                                $option_value = sprintf('%04d-%02d', $anno_opt, $mese_num);
                                $selected = ($option_value === $mese_display) ? 'selected' : '';
                                $label = $mesi_it[$mese_num] . ' ' . $anno_opt;
                                
                                echo "<option value=\"{$option_value}\" {$selected}>{$label}</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" onclick="resetFiltroMese()" title="Ripristina mese corrente">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block">
                        <i class="bi bi-info-circle"></i> Anno accademico: Settembre - Luglio
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <?php 
        $totale_attive = 0;
        $totale_importo = 0;
        $totale_pagato = 0;
        foreach ($statistiche as $stat) {
            if ($stat['stato'] == 'attiva') {
                $totale_attive = $stat['num_iscrizioni'];
                $totale_importo = $stat['importo_totale'];
                $totale_pagato = $stat['importo_pagato'];
            }
        }
        $saldo_residuo = $totale_importo - $totale_pagato;
        ?>
        <div class="col-md-3">
            <div class="card stat-card stat-primary">
                <div class="card-body text-center">
                    <h3 class="stat-value mb-1"><?= $totale_attive ?></h3>
                    <p class="stat-label mb-0">Iscrizioni Attive</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-success">
                <div class="card-body text-center">
                    <h3 class="stat-value mb-1">€ <?= number_format($totale_importo, 2) ?></h3>
                    <p class="stat-label mb-0">Importo Totale</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-info">
                <div class="card-body text-center">
                    <h3 class="stat-value mb-1">€ <?= number_format($totale_pagato, 2) ?></h3>
                    <p class="stat-label mb-0">Importo Pagato</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-warning">
                <div class="card-body text-center">
                    <h3 class="stat-value mb-1">€ <?= number_format($saldo_residuo, 2) ?></h3>
                    <p class="stat-label mb-0">Saldo Residuo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Iscrizioni -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Elenco Iscrizioni
                <span class="badge bg-primary ms-2"><?= count($iscrizioni) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Allievo</th>
                            <th>Tipo Corso</th>
                            <th>Materia</th>
                            <th>Docente</th>
                            <th>Data Inizio</th>
                            <th>Stato</th>
                            <th class="text-end">Importo</th>
                            <th class="text-end">Pagato</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($iscrizioni)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Nessuna iscrizione trovata</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($iscrizioni as $i): ?>
                                <tr>
                                    <td><?= e($i['allievo']) ?></td>
                                    <td>
                                        <?= e($i['tipo_corso']) ?>
                                        <?php if ($i['is_pacchetto']): ?>
                                            <br><small class="text-muted">
                                                <?= $i['lezioni_utilizzate'] ?>/<?= $i['lezioni_totali'] ?> lezioni
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($i['materia']) ?></td>
                                    <td><?= e($i['docente']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($i['data_inizio'])) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = [
                                            'attiva' => 'bg-success',
                                            'sospesa' => 'bg-warning',
                                            'conclusa' => 'bg-secondary',
                                            'annullata' => 'bg-danger'
                                        ][$i['stato']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($i['stato']) ?></span>
                                    </td>
                                    <td class="text-end">€ <?= number_format($i['importo_totale'], 2) ?></td>
                                    <td class="text-end">€ <?= number_format($i['importo_pagato'], 2) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#dettaglioIscrizioneModal" onclick="caricaDettagliIscrizione(<?= $i['id'] ?>)" title="Visualizza">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="apriModificaIscrizione(<?= $i['id'] ?>)" title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dettaglio Iscrizione -->
<div class="modal fade" id="dettaglioIscrizioneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> Dettaglio Iscrizione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">ALLIEVO</h6>
                        <p class="mb-0 fw-bold" id="det_allievo">-</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">STATO</h6>
                        <p class="mb-0" id="det_stato">-</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">TIPO CORSO</h6>
                        <p class="mb-0" id="det_tipo_corso">-</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">MATERIA</h6>
                        <p class="mb-0" id="det_materia">-</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">DOCENTE</h6>
                        <p class="mb-0" id="det_docente">-</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">DATA INIZIO</h6>
                        <p class="mb-0" id="det_data_inizio">-</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-muted small mb-2">GIORNO</h6>
                        <p class="mb-0" id="det_giorno">-</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted small mb-2">ORARIO</h6>
                        <p class="mb-0" id="det_orario">-</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted small mb-2">AULA</h6>
                        <p class="mb-0" id="det_aula">-</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">QUOTA ISCRIZIONE</h6>
                        <p class="mb-0 fw-bold" id="det_quota">€ 0.00</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">SCONTO FRATELLI</h6>
                        <p class="mb-0" id="det_sconto">€ 0.00</p>
                    </div>
                </div>

                <div id="det_note_container" style="display:none;">
                    <h6 class="text-muted small mb-2">NOTE</h6>
                    <p class="mb-0" id="det_note">-</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-warning" id="btnModificaDaDettaglio" onclick="apriModificaDaDettaglio()">
                    <i class="bi bi-pencil"></i> Modifica Iscrizione
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuova Iscrizione - Multi Step -->
<div class="modal fade" id="addIscrizioneModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> <span id="modalTitle">Nuova Iscrizione</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Progress Steps -->
                <div class="progress mb-4" style="height: 3px;">
                    <div class="progress-bar bg-success" id="progressBar" style="width: 33%"></div>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <div class="text-center flex-fill" id="step1Indicator">
                        <div class="badge bg-success">1</div>
                        <div class="small mt-1">Allievo</div>
                    </div>
                    <div class="text-center flex-fill" id="step2Indicator">
                        <div class="badge bg-secondary">2</div>
                        <div class="small mt-1">Corso</div>
                    </div>
                    <div class="text-center flex-fill" id="step3Indicator">
                        <div class="badge bg-secondary">3</div>
                        <div class="small mt-1">Orario</div>
                    </div>
                </div>

                <form id="formIscrizione">
                    <input type="hidden" id="iscrizione_id" name="id">
                    <input type="hidden" id="selected_allievo_id" name="allievo_id">
                    
                    <!-- STEP 1: Selezione/Creazione Allievo -->
                    <div id="step1" class="wizard-step">
                        <h5 class="mb-3">Seleziona o Crea Allievo</h5>
                        
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="allievo_mode" id="mode_existing" value="existing" checked>
                            <label class="btn btn-outline-primary" for="mode_existing">
                                <i class="bi bi-person-check"></i> Allievo Esistente
                            </label>
                            <input type="radio" class="btn-check" name="allievo_mode" id="mode_new" value="new">
                            <label class="btn btn-outline-success" for="mode_new">
                                <i class="bi bi-person-plus"></i> Nuovo Allievo
                            </label>
                        </div>
                        
                        <!-- Selezione Allievo Esistente -->
                        <div id="div_existing_allievo">
                            <label class="form-label">Cerca Allievo</label>
                            <select class="form-select form-select-lg" id="select_allievo">
                                <option value="">Seleziona allievo...</option>
                            </select>
                        </div>
                        
                        <!-- Form Nuovo Allievo -->
                        <div id="div_new_allievo" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cognome *</label>
                                    <input type="text" class="form-control" id="new_cognome">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" class="form-control" id="new_nome">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Data Nascita</label>
                                    <input type="date" class="form-control" id="new_data_nascita">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="new_telefono">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="new_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Indirizzo</label>
                                    <input type="text" class="form-control" id="new_indirizzo">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 2: Corso e Materia -->
                    <div id="step2" class="wizard-step" style="display:none;">
                        <h5 class="mb-3">Dettagli Corso</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo Corso *</label>
                                <select class="form-select" name="tipo_corso_config_id" id="tipo_corso_id" required>
                                    <option value="">Seleziona tipo corso</option>
                                </select>
                                <div id="info_corso" class="form-text"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Materia *</label>
                                <select class="form-select" name="materia_id" id="materia_id" required>
                                    <option value="">Seleziona materia</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Docente *</label>
                            <select class="form-select" name="docente_id" id="docente_id" required>
                                <option value="">Seleziona prima una materia</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Anno Scolastico *</label>
                                <input type="text" class="form-control" name="anno_scolastico" 
                                       value="<?= date('Y') ?>/<?= date('Y')+1 ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data Inizio *</label>
                                <input type="date" class="form-control" name="data_inizio" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 3: Orario e Aula -->
                    <div id="step3" class="wizard-step" style="display:none;">
                        <h5 class="mb-3">Slot Settimanale</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giorno Settimana *</label>
                                <select class="form-select" name="giorno_settimana" id="giorno_settimana" required>
                                    <option value="">Seleziona</option>
                                    <option value="1">Lunedì</option>
                                    <option value="2">Martedì</option>
                                    <option value="3">Mercoledì</option>
                                    <option value="4">Giovedì</option>
                                    <option value="5">Venerdì</option>
                                    <option value="6">Sabato</option>
                                    <option value="7">Domenica</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ora Inizio *</label>
                                <input type="time" class="form-control" name="ora_inizio" id="ora_inizio" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Aula *</label>
                                <select class="form-select" name="aula_id" id="aula_id" required>
                                    <option value="">Seleziona aula</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Alert Conflitti -->
                        <div id="conflitti_alert" class="alert alert-warning d-none">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Conflitto Orario</h6>
                            <div id="conflitti_details"></div>
                            <hr>
                            <div id="alternative_rooms"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quota Iscrizione (€)</label>
                                <input type="number" class="form-control" name="quota_iscrizione" 
                                       step="0.01" value="30.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sconto Fratelli (€)</label>
                                <input type="number" class="form-control" name="sconto_fratelli" 
                                       step="0.01" value="0.00">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" name="note" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Stato</label>
                            <select class="form-select" name="stato">
                                <option value="attiva">Attiva</option>
                                <option value="sospesa">Sospesa</option>
                                <option value="conclusa">Conclusa</option>
                                <option value="annullata">Annullata</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" style="display:none;">
                            <input type="hidden" name="data_fine">
                        </div>
                    </div>
                    
                    <!-- STEP 4: Errore Conflitto -->
                    <div id="step4_error" class="wizard-step text-center" style="display:none;">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-danger mb-3">Conflitto Orario</h4>
                        <div id="conflict_details" class="alert alert-warning text-start"></div>
                        <div id="alternative_rooms_list" class="mt-4"></div>
                    </div>
                    
                    <!-- STEP 5: Successo -->
                    <div id="step5_success" class="wizard-step text-center" style="display:none;">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-success mb-3">Iscrizione Creata con Successo!</h4>
                        <div id="success_details" class="alert alert-success text-start"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-outline-secondary" id="btnPrev" style="display:none;">
                    <i class="bi bi-arrow-left"></i> Indietro
                </button>
                <button type="button" class="btn btn-primary" id="btnNext">
                    Avanti <i class="bi bi-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-success" id="btnSave" style="display:none;" onclick="salvaIscrizione()">
                    <i class="bi bi-check-circle"></i> Salva Iscrizione
                </button>
                <button type="button" class="btn btn-primary" id="btnNewEnrollment" style="display:none;" onclick="nuovaIscrizione()">
                    <i class="bi bi-plus-circle"></i> Nuova Iscrizione
                </button>
                <button type="button" class="btn btn-secondary" id="btnClose" style="display:none;" data-bs-dismiss="modal">
                    Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let newAllieveId = null;

// Carica dati iniziali
document.addEventListener('DOMContentLoaded', function() {
    caricaAllievi();
    caricaTipiCorso();
    caricaMaterie();
    caricaAule();
    
    // Toggle allievo mode
    document.querySelectorAll('[name="allievo_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                document.getElementById('div_existing_allievo').style.display = 'block';
                document.getElementById('div_new_allievo').style.display = 'none';
            } else {
                document.getElementById('div_existing_allievo').style.display = 'none';
                document.getElementById('div_new_allievo').style.display = 'block';
            }
        });
    });
    
    // Navigation buttons
    document.getElementById('btnNext').addEventListener('click', nextStep);
    document.getElementById('btnPrev').addEventListener('click', prevStep);
    
    // Check conflicts on change
    ['giorno_settimana', 'ora_inizio', 'aula_id'].forEach(id => {
        document.getElementById(id).addEventListener('change', checkConflicts);
    });
    
    // Carica docenti quando cambia materia
    document.getElementById('materia_id').addEventListener('change', function() {
        const materiaId = this.value;
        const selectDocente = document.getElementById('docente_id');
        selectDocente.innerHTML = '<option value="">Caricamento...</option>';
        
        if (!materiaId) {
            selectDocente.innerHTML = '<option value="">Seleziona prima una materia</option>';
            return;
        }
        
        console.log('Caricamento docenti per materia:', materiaId);
        
        fetch(`<?= BASE_URL ?>/api/api_docenti_per_materia.php?materia_id=${materiaId}`)
        .then(r => r.json())
        .then(data => {
            console.log('Risposta API docenti:', data);
            selectDocente.innerHTML = '<option value="">Seleziona docente</option>';
            if (data.success && data.docenti && data.docenti.length > 0) {
                data.docenti.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.cognome + ' ' + d.nome;
                    selectDocente.appendChild(option);
                });
                console.log('Docenti caricati:', data.docenti.length);
            } else {
                selectDocente.innerHTML = '<option value="">Nessun docente disponibile</option>';
                console.log('Nessun docente trovato');
            }
        })
        .catch(err => {
            console.error('Errore caricamento docenti:', err);
            selectDocente.innerHTML = '<option value="">Errore caricamento</option>';
        });
    });
    
    // Mostra info corso
    document.getElementById('tipo_corso_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const durata = option.dataset.durata;
        const costo = option.dataset.costo;
        
        if (durata && costo) {
            document.getElementById('info_corso').innerHTML = 
                `<i class="bi bi-info-circle"></i> Durata: ${durata} min | Costo base: €${costo}/mese (4 lezioni)`;
        }
    });
});

function nextStep() {
    if (currentStep === 1) {
        const mode = document.querySelector('[name="allievo_mode"]:checked').value;
        if (mode === 'existing') {
            const allieveId = document.getElementById('select_allievo').value;
            if (!allieveId) {
                mostraToast('Attenzione', 'Seleziona un allievo', 'warning');
                return;
            }
            document.getElementById('selected_allievo_id').value = allieveId;
        } else {
            // Nuovo allievo
            if (newAllieveId) {
                // Già creato, vai avanti
                document.getElementById('selected_allievo_id').value = newAllieveId;
            } else {
                // Crea nuovo
                if (!document.getElementById('new_cognome').value || !document.getElementById('new_nome').value) {
                    mostraToast('Attenzione', 'Inserisci cognome e nome', 'warning');
                    return;
                }
                creaAllievo();
                return; // Aspetta callback
            }
        }
    }
    
    if (currentStep < 3) {
        currentStep++;
        showStep(currentStep);
    }
}

function prevStep() {
    if (currentStep === 4) {
        currentStep = 3;
        showStep(currentStep);
    } else if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

function showStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
    
    if (step === 4) {
        document.getElementById('step4_error').style.display = 'block';
    } else if (step === 5) {
        document.getElementById('step5_success').style.display = 'block';
    } else {
        document.getElementById('step' + step).style.display = 'block';
    }
    
    // Update indicators (solo per step 1-3)
    if (step <= 3) {
        for (let i = 1; i <= 3; i++) {
            const indicator = document.getElementById('step' + i + 'Indicator').querySelector('.badge');
            indicator.className = i <= step ? 'badge bg-success' : 'badge bg-secondary';
        }
        document.getElementById('progressBar').style.width = (step * 33.33) + '%';
    }
    
    // Update buttons
    document.getElementById('btnPrev').style.display = (step > 1 && step <= 3) ? 'inline-block' : (step === 4 ? 'inline-block' : 'none');
    document.getElementById('btnNext').style.display = (step < 3) ? 'inline-block' : 'none';
    document.getElementById('btnSave').style.display = (step === 3) ? 'inline-block' : 'none';
    document.getElementById('btnNewEnrollment').style.display = (step === 5) ? 'inline-block' : 'none';
    document.getElementById('btnClose').style.display = (step === 5) ? 'inline-block' : 'none';
}

function creaAllievo() {
    const data = {
        cognome: document.getElementById('new_cognome').value,
        nome: document.getElementById('new_nome').value,
        data_nascita: document.getElementById('new_data_nascita').value,
        email: document.getElementById('new_email').value,
        telefono: document.getElementById('new_telefono').value,
        indirizzo: document.getElementById('new_indirizzo').value,
        attivo: 1
    };
    
    fetch('<?= BASE_URL ?>/api/api_allievi_crud.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', data: data})
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            newAllieveId = result.id;
            document.getElementById('selected_allievo_id').value = newAllieveId;
            mostraToast('Successo', 'Allievo creato', 'success');
            currentStep++;
            showStep(currentStep);
        } else {
            mostraToast('Errore', result.message, 'danger');
        }
    });
}

function checkConflicts() {
    const giorno = document.getElementById('giorno_settimana').value;
    const oraInizio = document.getElementById('ora_inizio').value;
    const aulaId = document.getElementById('aula_id').value;
    const tipoCorsoId = document.getElementById('tipo_corso_id').value;
    
    if (!giorno || !oraInizio || !aulaId || !tipoCorsoId) return;
    
    fetch(`<?= BASE_URL ?>/api/api_check_conflicts.php?giorno=${giorno}&ora_inizio=${oraInizio}&aula_id=${aulaId}&tipo_corso_id=${tipoCorsoId}`)
    .then(r => r.json())
    .then(data => {
        const alert = document.getElementById('conflitti_alert');
        if (data.conflict) {
            alert.classList.remove('d-none');
            
            let html = '<p><strong>Lezione esistente:</strong></p>';
            html += `<ul><li>${data.conflict.allievo} - ${data.conflict.materia}</li>`;
            html += `<li>Docente: ${data.conflict.docente}</li>`;
            html += `<li>Orario: ${data.conflict.ora_inizio} - ${data.conflict.ora_fine}</li></ul>`;
            document.getElementById('conflitti_details').innerHTML = html;
            
            if (data.alternatives && data.alternatives.length > 0) {
                let altHtml = '<p><strong>Sale alternative disponibili:</strong></p><ul>';
                data.alternatives.forEach(a => {
                    altHtml += `<li><a href="#" onclick="selectAula(${a.id}); return false;">${a.nome}</a></li>`;
                });
                altHtml += '</ul>';
                document.getElementById('alternative_rooms').innerHTML = altHtml;
            } else {
                document.getElementById('alternative_rooms').innerHTML = '<p class="text-muted">Nessuna sala alternativa disponibile</p>';
            }
        } else {
            alert.classList.add('d-none');
        }
    });
}

function selectAula(aulaId) {
    document.getElementById('aula_id').value = aulaId;
    checkConflicts();
}

function caricaAllievi() {
    fetch('<?= BASE_URL ?>/api/api_get_helpers.php?type=allievi')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('select_allievo');
        select.innerHTML = '<option value="">Seleziona allievo...</option>';
        if (data.success && data.data) {
            data.data.forEach(a => {
                select.innerHTML += `<option value="${a.id}">${a.cognome} ${a.nome}</option>`;
            });
        }
    });
}

function caricaTipiCorso() {
    fetch('<?= BASE_URL ?>/api/api_configurazione_corsi.php?action=list&tipo=corso')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('tipo_corso_id');
        if (data.success && data.data) {
            data.data.forEach(c => {
                select.innerHTML += `<option value="${c.id}" data-durata="${c.durata_lezione}" data-costo="${c.costo_mensile}">
                    ${c.nome} - €${c.costo_mensile}/mese
                </option>`;
            });
        }
    });
}

function caricaMaterie() {
    fetch('<?= BASE_URL ?>/api/api_materie.php?action=list')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('materia_id');
        if (data.success && data.data) {
            data.data.forEach(m => {
                select.innerHTML += `<option value="${m.id}">${m.nome}</option>`;
            });
        }
    });
}

function caricaAule() {
    fetch('<?= BASE_URL ?>/api/api_aule.php?action=list')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('aula_id');
        if (data.success && data.data) {
            data.data.forEach(a => {
                select.innerHTML += `<option value="${a.id}">${a.nome}</option>`;
            });
        }
    });
}

function salvaIscrizione() {
    const form = document.getElementById('formIscrizione');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const id = document.getElementById('iscrizione_id').value;
    
    fetch('<?= BASE_URL ?>/api/api_iscrizioni.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: id ? 'update' : 'create',
            id: id,
            data: Object.fromEntries(formData)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Mostra step successo
            const allievoNome = document.getElementById('select_allievo').selectedOptions[0]?.text || 
                               (document.getElementById('new_cognome').value + ' ' + document.getElementById('new_nome').value);
            const materiaNome = document.getElementById('materia_id').selectedOptions[0]?.text;
            const docenteNome = document.getElementById('docente_id').selectedOptions[0]?.text;
            const giornoNome = document.getElementById('giorno_settimana').selectedOptions[0]?.text;
            const oraInizio = document.getElementById('ora_inizio').value;
            const aulaNome = document.getElementById('aula_id').selectedOptions[0]?.text;
            
            document.getElementById('success_details').innerHTML = `
                <h5>Riepilogo Iscrizione</h5>
                <ul class="list-unstyled mb-0">
                    <li><strong>Allievo:</strong> ${allievoNome}</li>
                    <li><strong>Materia:</strong> ${materiaNome}</li>
                    <li><strong>Docente:</strong> ${docenteNome}</li>
                    <li><strong>Orario:</strong> ${giornoNome} alle ${oraInizio}</li>
                    <li><strong>Aula:</strong> ${aulaNome}</li>
                </ul>
            `;
            currentStep = 5;
            showStep(5);
            
            // Ricarica la pagina dopo 3 secondi per mostrare la nuova iscrizione
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else if (data.conflict) {
            // Mostra step errore con conflitto
            const conf = data.conflict;
            let html = `<h6>Lezione già presente:</h6><ul class="mb-0">`;
            html += `<li><strong>Allievo:</strong> ${conf.allievo}</li>`;
            html += `<li><strong>Materia:</strong> ${conf.materia}</li>`;
            html += `<li><strong>Docente:</strong> ${conf.docente}</li>`;
            html += `<li><strong>Aula:</strong> ${conf.aula}</li>`;
            html += `<li><strong>Orario:</strong> ${conf.ora_inizio} - ${conf.ora_fine}</li></ul>`;
            document.getElementById('conflict_details').innerHTML = html;
            
            if (data.alternatives && data.alternatives.length > 0) {
                let altHtml = '<div class="alert alert-info"><h6>Sale disponibili in questo orario:</h6><div class="d-grid gap-2">';
                data.alternatives.forEach(a => {
                    altHtml += `<button class="btn btn-outline-primary" onclick="selectAulaAndRetry(${a.id}, '${a.nome}')">
                        <i class="bi bi-door-open"></i> ${a.nome}
                    </button>`;
                });
                altHtml += '</div></div>';
                document.getElementById('alternative_rooms_list').innerHTML = altHtml;
            } else {
                document.getElementById('alternative_rooms_list').innerHTML = 
                    '<p class="text-muted">Nessuna sala disponibile in questo orario</p>';
            }
            
            currentStep = 4;
            showStep(4);
        } else {
            mostraToast('Errore', data.message || 'Errore nel salvataggio', 'danger');
        }
    })
    .catch(err => {
        mostraToast('Errore', 'Errore di connessione', 'danger');
    });
}

function selectAulaAndRetry(aulaId, aulaNome) {
    document.getElementById('aula_id').value = aulaId;
    currentStep = 3;
    showStep(3);
    mostraToast('Info', `Sala cambiata in: ${aulaNome}. Clicca "Salva Iscrizione" per confermare.`, 'info');
}

function nuovaIscrizione() {
    location.reload();
}

function caricaDettagliIscrizione(id) {
    fetch(`<?= BASE_URL ?>/api/api_iscrizioni.php?action=get&id=${id}`)
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        console.log('Dettagli iscrizione ricevuti:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Errore nel caricamento');
        }
        
        if (!data.data) {
            throw new Error('Dati non disponibili');
        }
        
        const i = data.data;
        window.currentIscrizioneId = i.id;
        
        // Popola i dettagli
        document.getElementById('det_allievo').textContent = i.allievo || '-';
        
        // Stato con badge
        const statoBadgeClass = {
            'attiva': 'bg-success',
            'sospesa': 'bg-warning',
            'conclusa': 'bg-secondary',
            'annullata': 'bg-danger'
        }[i.stato] || 'bg-secondary';
        document.getElementById('det_stato').innerHTML = 
            `<span class="badge ${statoBadgeClass}">${i.stato ? i.stato.charAt(0).toUpperCase() + i.stato.slice(1) : '-'}</span>`;
        
        document.getElementById('det_tipo_corso').textContent = i.tipo_corso || '-';
        document.getElementById('det_materia').textContent = i.materia || '-';
        document.getElementById('det_docente').textContent = i.docente || '-';
        
        // Data inizio
        if (i.data_inizio) {
            const date = new Date(i.data_inizio);
            document.getElementById('det_data_inizio').textContent = 
                date.toLocaleDateString('it-IT', {year: 'numeric', month: 'long', day: 'numeric'});
        } else {
            document.getElementById('det_data_inizio').textContent = '-';
        }
        
        // Giorno settimana
        const giorni = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
        document.getElementById('det_giorno').textContent = giorni[parseInt(i.giorno_settimana)] || '-';
        
        // Orario
        document.getElementById('det_orario').textContent = i.ora_inizio ? i.ora_inizio.substring(0, 5) : '-';
        
        // Aula
        document.getElementById('det_aula').textContent = i.aula || '-';
        
        // Quote
        document.getElementById('det_quota').textContent = '€ ' + (parseFloat(i.quota_iscrizione) || 0).toFixed(2);
        document.getElementById('det_sconto').textContent = '€ ' + (parseFloat(i.sconto_fratelli) || 0).toFixed(2);
        
        // Note
        if (i.note) {
            document.getElementById('det_note').textContent = i.note;
            document.getElementById('det_note_container').style.display = 'block';
        } else {
            document.getElementById('det_note_container').style.display = 'none';
        }
        
        // Salva l'ID per la modifica
        document.getElementById('btnModificaDaDettaglio').onclick = () => apriModificaDaDettaglio(i.id);
    })
    .catch(err => {
        console.error('Errore caricamento dettagli:', err);
        mostraToast('Errore', err.message || 'Errore nel caricamento dei dettagli', 'danger');
    });
}

function apriModificaIscrizione(id) {
    console.log('Aprendo modifica iscrizione ID:', id);
    fetch(`<?= BASE_URL ?>/api/api_iscrizioni.php?action=get&id=${id}`)
    .then(r => {
        console.log('Risposta ricevuta, status:', r.status);
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        console.log('Dati ricevuti:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Errore nel caricamento');
        }
        
        if (!data.data) {
            throw new Error('Dati non disponibili');
        }
        
        const i = data.data;
        console.log('Iscrizione caricata:', i);
        
        document.getElementById('iscrizione_id').value = i.id;
        document.getElementById('selected_allievo_id').value = i.allievo_id;
        document.getElementById('tipo_corso_id').value = i.tipo_corso_config_id;
        document.getElementById('materia_id').value = i.materia_id;
        
        // Carica docenti per materia poi seleziona
        setTimeout(() => {
            document.getElementById('materia_id').dispatchEvent(new Event('change'));
            setTimeout(() => {
                document.getElementById('docente_id').value = i.docente_id;
            }, 500);
        }, 100);
        
        document.getElementById('giorno_settimana').value = i.giorno_settimana;
        document.getElementById('ora_inizio').value = i.ora_inizio;
        document.getElementById('aula_id').value = i.aula_id;
        document.querySelector('[name="anno_scolastico"]').value = i.anno_scolastico;
        document.querySelector('[name="data_inizio"]').value = i.data_inizio;
        document.querySelector('[name="data_fine"]').value = i.data_fine || '';
        document.querySelector('[name="quota_iscrizione"]').value = i.quota_iscrizione;
        document.querySelector('[name="sconto_fratelli"]').value = i.sconto_fratelli;
        document.querySelector('[name="stato"]').value = i.stato;
        document.querySelector('[name="note"]').value = i.note || '';
        
        document.getElementById('modalTitle').textContent = 'Modifica Iscrizione';
        currentStep = 1;
        showStep(1);
        new bootstrap.Modal(document.getElementById('addIscrizioneModal')).show();
    })
    .catch(err => {
        console.error('Errore fetch:', err);
        mostraToast('Errore', err.message || 'Errore nel caricamento iscrizione', 'danger');
    });
}

function apriModificaDaDettaglio(id) {
    // Chiudi modal dettaglio
    const modal = bootstrap.Modal.getInstance(document.getElementById('dettaglioIscrizioneModal'));
    if (modal) modal.hide();
    
    // Apri modifica
    setTimeout(() => {
        apriModificaIscrizione(id);
    }, 300);
}

function mostraToast(titolo, messaggio, tipo = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const bgColors = {
        'success': 'bg-success',
        'danger': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    
    const icons = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    
    const toastId = 'toast_' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header ${bgColors[tipo]} text-white">
                <i class="bi ${icons[tipo]} me-2"></i>
                <strong class="me-auto">${titolo}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${messaggio}</div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {autohide: true, delay: 3000});
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}

function cambiaFiltroMese() {
    const mese = document.getElementById('filtroMese').value;
    if (mese) {
        window.location.href = '<?= BASE_URL ?>/gestione_iscrizioni.php?mese=' + mese;
    }
}

function resetFiltroMese() {
    window.location.href = '<?= BASE_URL ?>/gestione_iscrizioni.php';
}
</script>

<?php include 'includes/footer.php'; ?>
