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
                        <select class="form-select" id="filtroMese">
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
                        <button class="btn btn-outline-secondary" type="button" id="btnResetFiltro" title="Ripristina mese corrente">
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
                            <th>Socio</th>
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
                                    <td><?= e($i['socio']) ?></td>
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
                                        <button class="btn btn-sm btn-primary btn-dettaglio" data-bs-toggle="modal" data-bs-target="#dettaglioIscrizioneModal" data-iscrizione-id="<?= $i['id'] ?>" title="Visualizza">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning btn-modifica" data-iscrizione-id="<?= $i['id'] ?>" title="Modifica">
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
                        <h6 class="text-muted small mb-2">SOCIO</h6>
                        <p class="mb-0 fw-bold" id="det_socio">-</p>
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
                <button type="button" class="btn btn-warning" id="btnModificaDaDettaglio">
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
                        <div class="small mt-1">Socio</div>
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
                    <input type="hidden" id="selected_socio_id" name="socio_id">
                    
                    <!-- STEP 1: Selezione/Creazione Socio -->
                    <div id="step1" class="wizard-step">
                        <h5 class="mb-3">Seleziona o Crea Socio</h5>
                        
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="socio_mode" id="mode_existing" value="existing" checked>
                            <label class="btn btn-outline-primary" for="mode_existing">
                                <i class="bi bi-person-check"></i> Socio Esistente
                            </label>
                            <input type="radio" class="btn-check" name="socio_mode" id="mode_new" value="new">
                            <label class="btn btn-outline-success" for="mode_new">
                                <i class="bi bi-person-plus"></i> Nuovo Socio
                            </label>
                        </div>
                        
                        <!-- Selezione Socio Esistente -->
                        <div id="div_existing_socio">
                            <label class="form-label">Cerca Socio</label>
                            <select class="form-select form-select-lg" id="select_socio">
                                <option value="">Seleziona socio...</option>
                            </select>
                        </div>
                        
                        <!-- Form Nuovo Socio -->
                        <div id="div_new_socio" style="display:none;">
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
                <button type="button" class="btn btn-success" id="btnSave" style="display:none;">
                    <i class="bi bi-check-circle"></i> Salva Iscrizione
                </button>
                <button type="button" class="btn btn-primary" id="btnNewEnrollment" style="display:none;">
                    <i class="bi bi-plus-circle"></i> Nuova Iscrizione
                </button>
                <button type="button" class="btn btn-secondary" id="btnClose" style="display:none;" data-bs-dismiss="modal">
                    Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/gestione_iscrizioni.js"></script>

<?php include 'includes/footer.php'; ?>
