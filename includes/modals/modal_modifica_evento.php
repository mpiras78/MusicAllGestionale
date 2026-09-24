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
                    <input type="hidden" id="modEvento_dataEvento">
                    <input type="hidden" id="modEvento_tipoologiaCategoria">
                    <input type="hidden" id="modEvento_socioId">
                    
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
                <button type="button" class="btn btn-primary" id="btnSalvaModificaEvento">
                    <i class="bi bi-check-circle"></i> Salva Modifiche
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $_SESSION['csp_nonce'] ?>">
function apriModalModificaEvento(eventoId) {
    fetch(`<?= BASE_URL ?>/api/api_eventi.php?id=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const evt = data.data;
                document.getElementById('modEvento_id').value = eventoId;
                document.getElementById('modEvento_dataEvento').value = evt.data_evento || '';
                document.getElementById('modEvento_tipoologiaCategoria').value = evt.tipologia_categoria || '';
                document.getElementById('modEvento_socioId').value = evt.socio_id || '';
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

function salvaModificaEvento(btn) {
    const eventoId = document.getElementById('modEvento_id').value;
    const oraInizio = document.getElementById('modEvento_oraInizio').value + ':00';
    const oraFine = document.getElementById('modEvento_oraFine').value + ':00';
    const aulaId = document.getElementById('modEvento_aulaId').value;
    const tipoologiaCategoria = document.getElementById('modEvento_tipoologiaCategoria').value;
    const dataEvento = document.getElementById('modEvento_dataEvento').value;
    
    // Se è un recupero, controlla i conflitti prima di salvare
    if (tipoologiaCategoria === 'recupero' && dataEvento) {
        const formData = new FormData();
        formData.append('data_recupero', dataEvento);
        formData.append('ora_inizio', oraInizio);
        formData.append('ora_fine', oraFine);
        formData.append('aula_id', aulaId);
        formData.append('evento_id', eventoId);
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifica conflitti...';
        
        fetch('<?= BASE_URL ?>/api/api_check_recupero_conflicts.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(result => {
            if (result.ha_conflitti && result.conflitti && result.conflitti.length > 0) {
                // Mostra dialogo di conferma con i conflitti
                mostraDialogoConflittiRecupero(result.conflitti, function() {
                    // Se confermato, salva
                    salvaModificaEventoFinal(btn, eventoId, oraInizio, oraFine, aulaId);
                }, function() {
                    // Se annullato, ripristina il bottone
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Salva Modifiche';
                });
            } else {
                // Nessun conflitto, salva direttamente
                salvaModificaEventoFinal(btn, eventoId, oraInizio, oraFine, aulaId);
            }
        })
        .catch(error => {
            mostraToast('Errore', 'Errore durante la verifica conflitti: ' + error.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Salva Modifiche';
        });
    } else {
        // Non è un recupero, salva direttamente
        salvaModificaEventoFinal(btn, eventoId, oraInizio, oraFine, aulaId);
    }
}

function salvaModificaEventoFinal(btn, eventoId, oraInizio, oraFine, aulaId) {
    const formData = new FormData();
    formData.append('evento_id', eventoId);
    formData.append('ora_inizio', oraInizio);
    formData.append('ora_fine', oraFine);
    formData.append('aula_id', aulaId);
    
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

function mostraDialogoConflittiRecupero(conflitti, onConfirm, onCancel) {
    let html = '<div class="alert alert-warning"><strong>⚠️ Conflitti rilevati!</strong><br>La sala è già occupata nei seguenti orari:</div>';
    html += '<div class="list-group" style="max-height: 300px; overflow-y: auto;">';
    
    conflitti.forEach(c => {
        html += `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">${c.tipo}</h6>
                        <p class="mb-1 small text-muted">${c.orario}</p>
                        <p class="mb-0 small">${c.dettagli}</p>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Usa modale Bootstrap per la conferma
    let modal = document.getElementById('conflittiRecuperoModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'conflittiRecuperoModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        document.body.appendChild(modal);
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Conflitti Orario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${html}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnAnnullaConflitto">
                        <i class="bi bi-x-circle"></i> Annulla
                    </button>
                    <button type="button" class="btn btn-warning" id="btnConfermaConflitto">
                        <i class="bi bi-check-circle"></i> Procedi comunque
                    </button>
                </div>
            </div>
        </div>
    `;
    
    const bsModal = new bootstrap.Modal(modal);
    
    document.getElementById('btnConfermaConflitto').onclick = () => {
        bsModal.hide();
        onConfirm();
    };
    
    document.getElementById('btnAnnullaConflitto').onclick = () => {
        bsModal.hide();
        onCancel();
    };
    
    modal.addEventListener('hidden.bs.modal', onCancel, { once: true });
    bsModal.show();
}

// Event listener per pulsante Salva Modifiche
document.addEventListener('DOMContentLoaded', function() {
    const btnSalva = document.getElementById('btnSalvaModificaEvento');
    if (btnSalva) {
        btnSalva.addEventListener('click', function() {
            salvaModificaEvento(this);
        });
    }
});
</script>
