<?php
/**
 * Componente: Tabella Assenze
 * Da includere in gestione_assenze.php
 * Richiede: $assenze (array), $canCreate (bool), $isDocente (bool)
 */
if (!isset($assenze) || !isset($canCreate) || !isset($isDocente)) {
    die('Errore: variabili necessarie non definite');
}
?>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-list"></i> Elenco Assenze
            <span class="badge bg-secondary ms-2"><?= count($assenze) ?></span>
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($assenze)): ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Nessuna assenza registrata</h5>
                <p class="text-muted">
                    <?= $isDocente ? 'Non ci sono assenze per i tuoi soci' : 'Non ci sono assenze corrispondenti ai filtri selezionati' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Socio</th>
                            <th>Data Assenza</th>
                            <th>Orario Orig.</th>
                            <th>Docente</th>
                            <th>Materia</th>
                            <th>Causata da</th>
                            <th>Recupero</th>
                            <th>Note</th>
                            <?php if ($canCreate): ?>
                            <th>Azioni</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assenze as $ass): ?>
                            <?php
                            // Calcola stato recupero
                            $minuti_da_recuperare = $ass['minuti_da_recuperare'] ?? 0;
                            $minuti_recuperati = $ass['minuti_recuperati'] ?? 0;
                            $recupero_completo = ($minuti_da_recuperare > 0 && $minuti_recuperati >= $minuti_da_recuperare);
                            $recupero_parziale = ($minuti_da_recuperare > 0 && $minuti_recuperati > 0 && $minuti_recuperati < $minuti_da_recuperare);
                            ?>
                            <tr>
                                <td><strong><?= e($ass['socio']) ?></strong></td>
                                <td><?= formatDate($ass['data']) ?></td>
                                <td class="small">
                                    <?php if ($ass['giorno_settimana']): ?>
                                        <?= e($ass['giorno_settimana']) ?><br>
                                        <?= formatTime($ass['ora_inizio']) ?>-<?= formatTime($ass['ora_fine']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($ass['docente']) ?></td>
                                <td><?= e($ass['materia']) ?></td>
                                <td>
                                    <?php if ($ass['causata_da'] == 'socio'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-person"></i> Socio
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-person-badge"></i> Docente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$ass['necessita_recupero']): ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-dash-circle"></i> Non necessario
                                        </span>
                                    <?php elseif ($ass['ha_recupero'] == 0): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-circle"></i> Da programmare
                                        </span>
                                    <?php elseif ($recupero_completo): ?>
                                        <span class="badge bg-success" data-bs-toggle="tooltip" 
                                              title="Recupero completo: <?= $minuti_recuperati ?>' su <?= $minuti_da_recuperare ?>'">
                                            <i class="bi bi-calendar-check"></i> <?= e($ass['date_recuperi']) ?>
                                        </span>
                                    <?php elseif ($recupero_parziale): ?>
                                        <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" 
                                              title="Recupero parziale: <?= $minuti_recuperati ?>' su <?= $minuti_da_recuperare ?>' (mancano <?= ($minuti_da_recuperare - $minuti_recuperati) ?>')">
                                            <i class="bi bi-hourglass-split"></i> <?= e($ass['date_recuperi']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success" 
                                              <?php if (!empty($ass['date_recuperi'])): ?>
                                              data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="Recupero programmato: <?= e($ass['date_recuperi']) ?>"
                                              <?php endif; ?>>
                                            <i class="bi bi-check-circle"></i> Programmato
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ass['note_annullamento']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="tooltip" 
                                                title="<?= e($ass['note_annullamento']) ?>">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canCreate): ?>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#editAssenzaModal<?= $ass['id'] ?>"
                                                title="Modifica data assenza">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($ass['necessita_recupero'] && !$recupero_completo): ?>
                                        <button type="button" class="btn btn-outline-success" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#creaRecuperoModal<?= $ass['id'] ?>"
                                                title="Programma recupero">
                                            <i class="bi bi-calendar-plus"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($ass['ha_recupero'] == 0): ?>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Confermi l\'eliminazione?');">
                                            <input type="hidden" name="action" value="elimina">
                                            <input type="hidden" name="assenza_id" value="<?= $ass['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Elimina assenza">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled 
                                                    data-bs-toggle="tooltip" 
                                                    title="Impossibile eliminare: ha recuperi associati">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals Edit Assenza e Programma Recupero -->
<?php if ($canCreate && !empty($assenze)): ?>
    <?php foreach ($assenze as $ass): ?>
        <?php
        $minuti_da_recuperare = $ass['minuti_da_recuperare'] ?? 0;
        $minuti_recuperati = $ass['minuti_recuperati'] ?? 0;
        $recupero_completo = ($minuti_da_recuperare > 0 && $minuti_recuperati >= $minuti_da_recuperare);
        ?>
        
        <!-- Modal Edit Assenza -->
        <div class="modal fade" id="editAssenzaModal<?= $ass['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-primary bg-opacity-10">
                            <h5 class="modal-title">
                                <i class="bi bi-pencil"></i> Modifica Assenza
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="modifica_assenza">
                            <input type="hidden" name="assenza_id" value="<?= $ass['id'] ?>">
                            
                            <div class="alert alert-info">
                                <strong>Socio:</strong> <?= e($ass['socio']) ?><br>
                                <strong>Docente:</strong> <?= e($ass['docente']) ?><br>
                                <strong>Materia:</strong> <?= e($ass['materia']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Data Assenza *</label>
                                <input type="date" name="data_assenza" class="form-control" 
                                       value="<?= $ass['data'] ?>" required>
                                <small class="text-muted">Modifica solo la data dell'assenza</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salva
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Programma Recupero -->
        <?php if ($ass['necessita_recupero'] && !$recupero_completo): ?>
        <div class="modal fade" id="creaRecuperoModal<?= $ass['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="gestione_recuperi.php">
                        <div class="modal-header bg-success bg-opacity-10">
                            <h5 class="modal-title">
                                <i class="bi bi-calendar-plus"></i> Programma Recupero
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="crea_recupero">
                            <input type="hidden" name="assenza_id" value="<?= $ass['id'] ?>">
                            
                            <div class="alert alert-info">
                                <strong>Assenza da recuperare:</strong><br>
                                <?= e($ass['socio']) ?> - <?= e($ass['materia']) ?><br>
                                <?= formatDate($ass['data']) ?>, <?= e($ass['giorno_settimana']) ?> 
                                <?= formatTime($ass['ora_inizio']) ?>-<?= formatTime($ass['ora_fine']) ?>
                                <?php if ($minuti_da_recuperare > 0 && $minuti_recuperati > 0): ?>
                                    <hr class="my-2">
                                    <strong class="text-warning">Recupero parziale:</strong> 
                                    <?= $minuti_recuperati ?>' già recuperati su <?= $minuti_da_recuperare ?>' 
                                    (mancano <?= ($minuti_da_recuperare - $minuti_recuperati) ?>')
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Data Recupero *</label>
                                    <input type="date" name="data_recupero" class="form-control" 
                                           min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ora Inizio *</label>
                                    <input type="time" name="ora_inizio" class="form-control" 
                                           value="<?= $ass['ora_inizio'] ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ora Fine *</label>
                                    <input type="time" name="ora_fine" class="form-control" 
                                           value="<?= $ass['ora_fine'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Aula</label>
                                <select name="aula_id" class="form-select">
                                    <option value="">Da definire</option>
                                    <?php if (isset($tutte_aule)): ?>
                                        <?php foreach ($tutte_aule as $aula): ?>
                                            <option value="<?= $aula['id'] ?>"><?= e($aula['nome']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <textarea name="note_segreteria" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send"></i> Crea Recupero
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script>
// Inizializza tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
