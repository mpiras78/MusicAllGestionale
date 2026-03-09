<!-- Modal Modifica Evento -->
<div class="modal fade" id="modificaEventoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Modifica Prenotazione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formModificaEvento">
                    <input type="hidden" id="modEvento_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Aula *</label>
                        <select class="form-select" id="modEvento_aulaId" required>
                            <option value="">Caricamento...</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" id="modEvento_oraInizio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" id="modEvento_oraFine" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary" onclick="salvaModificaEvento()">
                    <i class="bi bi-check-circle"></i> Salva Modifiche
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function apriModalModificaEvento(eventoId) {
    fetch(`<?= BASE_URL ?>/api/api_eventi.php?id=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const evt = data.data;
                document.getElementById('modEvento_id').value = eventoId;
                document.getElementById('modEvento_oraInizio').value = evt.ora_inizio.substr(0, 5);
                document.getElementById('modEvento_oraFine').value = evt.ora_fine.substr(0, 5);
                
                // Carica aule
                fetch('<?= BASE_URL ?>/api/api_aule.php')
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            let html = '';
                            d.data.forEach(a => {
                                html += `<option value="${a.id}" ${a.id == evt.aula_id ? 'selected' : ''}>${a.nome}</option>`;
                            });
                            document.getElementById('modEvento_aulaId').innerHTML = html;
                        }
                    });
                
                const modal = new bootstrap.Modal(document.getElementById('modificaEventoModal'));
                modal.show();
            }
        })
        .catch(error => mostraToast('Errore', error.message, 'danger'));
}

function salvaModificaEvento() {
    const eventoId = document.getElementById('modEvento_id').value;
    const oraInizio = document.getElementById('modEvento_oraInizio').value + ':00';
    const oraFine = document.getElementById('modEvento_oraFine').value + ':00';
    const aulaId = document.getElementById('modEvento_aulaId').value;
    
    const formData = new FormData();
    formData.append('evento_id', eventoId);
    formData.append('ora_inizio', oraInizio);
    formData.append('ora_fine', oraFine);
    formData.append('aula_id', aulaId);
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvataggio...';
    
    fetch('<?= BASE_URL ?>/api/api_modifica_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modificaEventoModal')).hide();
            mostraToast('Successo', 'Prenotazione modificata', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            throw new Error(result.error);
        }
    })
    .catch(error => {
        mostraToast('Errore', error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Salva Modifiche';
    });
}
</script>
