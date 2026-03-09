<?php
/**
 * Componente: Modal Crea Assenza
 * Da includere in gestione_assenze.php
 * Richiede: $soci (array)
 */
if (!isset($soci)) {
    die('Errore: variabile $soci non definita');
}
?>

<div class="modal fade" id="creaAssenzaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formCreaAssenza">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Registra Nuova Assenza
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crea_assenza">
                    
                    <!-- Alert per errori caricamento lezioni -->
                    <div id="lezioniAlert" class="alert alert-warning alert-dismissible fade" role="alert" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span id="lezioniAlertMessage"></span>
                        <button type="button" class="btn-close" onclick="document.getElementById('lezioniAlert').style.display='none'"></button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Socio *</label>
                            <select name="socio_id_helper" id="socioSelectHelper" class="form-select" required>
                                <option value="">Seleziona socio...</option>
                                <?php foreach ($soci as $all): ?>
                                    <option value="<?= $all['id'] ?>"><?= e($all['nome_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data Assenza *</label>
                            <input type="date" name="data" id="dataAssenza" class="form-control" required value="<?= date('Y-m-d') ?>" disabled>
                            <small class="text-muted">Seleziona prima una lezione</small>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="lezioniContainer" style="display: none;">
                        <label class="form-label">Lezione *</label>
                        <select name="lezione_id" id="lezioneSelectFinal" class="form-select" required>
                            <option value="">Caricamento...</option>
                        </select>
                        <small class="text-muted">Lezioni settimanali del socio selezionato</small>
                    </div>
                    
                    <!-- Contatori Anno Scolastico -->
                    <div id="contatoriAnnoScolastico" style="display: none;"></div>
                    
                    <div class="mb-3">
                        <label class="form-label">Causata da *</label>
                        <select name="causata_da" class="form-select" required>
                            <option value="socio">Socio</option>
                            <option value="docente">Docente</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Note (opzionale)</label>
                        <textarea name="note_annullamento" class="form-control" rows="2" 
                                  placeholder="Es: Socio malato, Docente impegnato, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Registra Assenza
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>