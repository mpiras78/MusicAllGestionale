<!-- Modal Nuova Lezione di Prova -->
<div class="modal fade" id="modalLezioneProva" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #9C27B0; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-star"></i> Nuova Lezione di Prova
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Le lezioni di prova sono lezioni singole non legate a iscrizioni
                </div>
                <form id="formLezioneProva">
                    <h6 class="mb-3">Dati Socio</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nome *</label>
                            <input type="text" class="form-control" name="nome_socio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cognome *</label>
                            <input type="text" class="form-control" name="cognome_socio" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_socio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefono</label>
                            <input type="tel" class="form-control" name="telefono_socio">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Dettagli Lezione</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data Lezione *</label>
                            <input type="date" class="form-control" name="data_lezione" id="dataLezioneProva" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Materia *</label>
                            <select class="form-select" name="materia_id" required>
                                <option value="">Seleziona materia</option>
                                <?php
                                $db = Database::getInstance();
                                $materie = $db->query("SELECT * FROM materie WHERE attiva = 1 ORDER BY nome");
                                foreach ($materie as $m):
                                ?>
                                    <option value="<?= $m['id'] ?>"><?= e($m['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Docente *</label>
                            <select class="form-select" name="docente_id" required>
                                <option value="">Seleziona docente</option>
                                <?php
                                $docenti = $db->query("SELECT * FROM docenti WHERE attivo = 1 ORDER BY cognome, nome");
                                foreach ($docenti as $d):
                                ?>
                                    <option value="<?= $d['id'] ?>"><?= e($d['cognome'] . ' ' . $d['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Aula *</label>
                            <select class="form-select" name="aula_id" required>
                                <option value="">Seleziona aula</option>
                                <?php
                                $aule = $db->query("SELECT * FROM aule WHERE attiva = 1 ORDER BY ordine_visualizzazione");
                                foreach ($aule as $au):
                                ?>
                                    <option value="<?= $au['id'] ?>"><?= e($au['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" name="ora_inizio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" name="ora_fine" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn" style="background-color: #9C27B0; color: white;" onclick="salvaLezioneProva()">
                    <i class="bi bi-star"></i> Salva Lezione di Prova
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function salvaLezioneProva() {
    const form = document.getElementById('formLezioneProva');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('<?= BASE_URL ?>/api/api_lezioni_prova.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', data: data})
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            mostraToast('Successo', 'Lezione di prova creata correttamente', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalLezioneProva')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => mostraToast('Errore', error.message, 'danger'));
}

document.getElementById('modalLezioneProva')?.addEventListener('show.bs.modal', function() {
    const dataSelezionata = document.getElementById('data_selezionata')?.value;
    if (dataSelezionata) {
        document.getElementById('dataLezioneProva').value = dataSelezionata;
    }
});
</script>
