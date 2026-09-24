/**
 * JavaScript per Gestione Recuperi
 * Controlla conflitti di sovrapposizione nella stessa sala
 */

document.addEventListener('DOMContentLoaded', function() {
    // Seleziona tutti i form di creazione recupero (hanno attributi data-assenza-id)
    const forms = document.querySelectorAll('form[action="gestione_recuperi.php"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });
});

async function handleFormSubmit(e) {
    // Non bloccare se è un reset o altre azioni
    if (e.submitter && e.submitter.name === 'action' && !['crea_recupero', 'modifica_recupero'].includes(e.submitter.value)) {
        return true;
    }
    
    e.preventDefault();
    
    // Raccogli dati dal form
    const formData = new FormData(this);
    const data_recupero = formData.get('data_recupero');
    const ora_inizio = formData.get('ora_inizio');
    const ora_fine = formData.get('ora_fine');
    const aula_id = formData.get('aula_id');
    const recupero_id = formData.get('recupero_id'); // Se presente, è una modifica
    
    // Se non c'è aula selezionata, consenti il submit
    if (!aula_id) {
        this.submit();
        return;
    }
    
    try {
        // Prepara i parametri della richiesta
        const params = {
            data_recupero,
            ora_inizio,
            ora_fine,
            aula_id
        };
        
        // Se è una modifica, passa il recupero_id per escluderlo dal check
        if (recupero_id) {
            params.recupero_id = recupero_id;
        }
        
        // Chiama API per controllare conflitti
        const response = await fetch(`${BASE_URL}/api/api_check_recupero_conflicts.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(params)
        });
        
        const result = await response.json();
        
        if (!result.success) {
            alert('Errore nel controllo dei conflitti: ' + result.error);
            return;
        }
        
        // Se non ci sono conflitti, procedi col submit
        if (!result.ha_conflitti) {
            this.submit();
            return;
        }
        
        // Se ci sono conflitti, mostra il dialogo
        mostraDialogoConflitti(result.conflitti, this);
        
    } catch (error) {
        console.error('Errore:', error);
        alert('Errore nel controllo dei conflitti: ' + error.message);
    }
}

function mostraDialogoConflitti(conflitti, formElement) {
    // Crea il modal HTML
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'conflittiModal';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Conflitto di Orario Rilevato
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <strong>Attenzione:</strong> È stato rilevato un conflitto nella stessa sala. 
                        Un altro evento è programmato nello stesso orario.
                    </p>
                    
                    <div class="alert alert-warning mb-3">
                        <h6 class="mb-2"><i class="bi bi-calendar-event"></i> Eventi in conflitto:</h6>
                        <div style="max-height: 300px; overflow-y: auto;">
                            ${conflitti.map(c => `
                                <div class="mb-2 pb-2 border-bottom">
                                    <strong>${c.tipo}:</strong> ${c.orario}<br>
                                    <small class="text-muted">${c.dettagli}</small><br>
                                    <small class="text-muted">Socio: ${c.socio}</small>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-0">
                        Desideri comunque procedere con la creazione del recupero? 
                        Assicurati che sia stata data l'autorizzazione per l'utilizzo della sala in questo orario.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-warning" id="btnProcediRecupero">
                        <i class="bi bi-check-lg"></i> Procedi comunque
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Aggiungi il modal al body
    document.body.appendChild(modal);
    
    // Salva il form come globale per usarlo nel bottone di conferma
    window.formRecuperoPendente = formElement;
    
    // Aggiungi event listener al bottone di conferma
    const btnProcedi = modal.querySelector('#btnProcediRecupero');
    btnProcedi.addEventListener('click', procediConRecupero);
    
    // Mostra il modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Rimuovi il modal dal DOM quando viene nascosto
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

function procediConRecupero() {
    // Nascondi il modal
    const modal = document.getElementById('conflittiModal');
    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
    
    // Procedi col submit del form
    if (window.formRecuperoPendente) {
        window.formRecuperoPendente.submit();
    }
}
