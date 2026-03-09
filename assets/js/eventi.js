/**
 * Eventi Calendario - JavaScript
 * Gestione CRUD eventi con modal e validazione
 */

// State globale
let currentEventData = null;
let currentDettaglioPrenotazione = null;
let tipologieEventi = [];
let sociList = [];
let docentiList = [];
let materieList = [];
let auleList = [];

/**
 * Apri modal dettaglio prenotazione
 */
async function apriModalDettaglioPrenotazione(eventoId) {
    try {
        console.log('Apertura modal dettaglio per evento:', eventoId);
        
        // Mostra modal subito
        const modalElement = document.getElementById('modalDettaglioPrenotazione');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Mostra loading nel body
        const dettaglioTipologia = document.getElementById('dettaglioTipologia');
        if (dettaglioTipologia) {
            dettaglioTipologia.innerHTML = '<small class="text-muted"><i class="bi bi-hourglass-split"></i> Caricamento...</small>';
        }
        
        // Carica dettagli evento
        const response = await fetch(`api_eventi.php?action=get&id=${eventoId}`);
        const data = await response.json();
        
        console.log('Risposta API:', data);
        
        if (!data.success) {
            throw new Error(data.error || 'Errore caricamento prenotazione');
        }
        
        const eventoData = data.data;
        
        currentDettaglioPrenotazione = {
            id: eventoId,
            ...eventoData
        };
        
        // Popola i campi di dettaglio
        document.getElementById('dettaglioTipologia').textContent = eventoData.tipologia_nome || '-';
        document.getElementById('dettaglioData').textContent = formatDataItaliana(eventoData.data_evento || eventoData.giorno_settimana || '-');
        document.getElementById('dettaglioOrario').textContent = `${(eventoData.ora_inizio || '-').substring(0, 5)} - ${(eventoData.ora_fine || '-').substring(0, 5)}`;
        document.getElementById('dettaglioSala').textContent = eventoData.aula_nome || '-';
        document.getElementById('dettaglioDocente').textContent = eventoData.docente_nome || '-';
        document.getElementById('dettaglioMateria').textContent = eventoData.materia_nome || '-';
        document.getElementById('dettaglioSocio').textContent = eventoData.partecipante_nome || '-';
        document.getElementById('dettaglioTitolo').textContent = eventoData.titolo || '-';
        document.getElementById('dettaglioDescrizione').textContent = eventoData.descrizione || '-';
        document.getElementById('dettaglioNote').textContent = eventoData.note || '-';
        
        // Popola stato
        const statoBadge = document.getElementById('dettaglioStatoBadge');
        const stato = eventoData.confermato ? 'Confermato' : 'Non Confermato';
        const classeStato = eventoData.confermato ? 'bg-success' : 'bg-warning';
        statoBadge.textContent = stato;
        statoBadge.className = `badge ${classeStato}`;
        
    } catch (error) {
        console.error('Errore:', error);
        document.getElementById('dettaglioTipologia').textContent = 'Errore nel caricamento';
    }
}

/**
 * Modifica prenotazione attuale (apre modal modifica)
 */
function modificaPrenotazioneAttuale() {
    if (currentDettaglioPrenotazione) {
        // Chiudi modal dettaglio
        const dettaglioModal = bootstrap.Modal.getInstance(document.getElementById('modalDettaglioPrenotazione'));
        if (dettaglioModal) {
            dettaglioModal.hide();
        }
        
        // Apri modal modifica
        apriModalModificaEvento(currentDettaglioPrenotazione.id);
    }
}

/**
 * Formatta data in italiano
 */
function formatDataItaliana(data) {
    if (!data) return '-';
    
    // Se è un giorno della settimana
    if (['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'].includes(data.toLowerCase())) {
        const giorni = {
            'lunedi': 'Lunedì',
            'martedi': 'Martedì',
            'mercoledi': 'Mercoledì',
            'giovedi': 'Giovedì',
            'venerdi': 'Venerdì',
            'sabato': 'Sabato',
            'domenica': 'Domenica'
        };
        return giorni[data.toLowerCase()] || data;
    }
    
    // Se è una data yyyy-mm-dd
    if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
        const date = new Date(data + 'T00:00:00');
        const giorni = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
        const mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 
                      'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
        return `${giorni[date.getDay()]} ${date.getDate()} ${mesi[date.getMonth()]} ${date.getFullYear()}`;
    }
    
    return data;
}

async function loadFormData() {
    try {
        // Carica tipologie eventi
        const tipResponse = await fetch('api_get_tipologie.php');
        const tipData = await tipResponse.json();
        if (tipData.success) {
            tipologieEventi = tipData.data;
        }
        
        // Carica soci
        const allResponse = await fetch('api_get_helpers.php?type=soci');
        const allData = await allResponse.json();
        if (allData.success) {
            sociList = allData.data;
        }
        
        // Carica docenti
        const docResponse = await fetch('api_get_helpers.php?type=docenti');
        const docData = await docResponse.json();
        if (docData.success) {
            docentiList = docData.data;
        }
        
        // Carica materie
        const matResponse = await fetch('api_get_helpers.php?type=materie');
        const matData = await matResponse.json();
        if (matData.success) {
            materieList = matData.data;
        }
        
        // Carica aule
        const auleResponse = await fetch('api_get_helpers.php?type=aule');
        const auleData = await auleResponse.json();
        if (auleData.success) {
            auleList = auleData.data;
        }
        
    } catch (error) {
        console.error('Errore caricamento dati form:', error);
    }
}

/**
 * Apri modal crea evento
 */
async function apriModalCreaEvento(dataPreselezionata = null, aulaId = null, oraInizio = null) {
    currentEventData = {
        mode: 'create',
        data: dataPreselezionata || new Date().toISOString().split('T')[0],
        aula_id: aulaId,
        ora_inizio: oraInizio
    };
    
    // Ricarica dati se necessario
    if (tipologieEventi.length === 0 || sociList.length === 0 || docentiList.length === 0) {
        await loadFormData();
    }
    
    // Popola form
    popolaFormCreaEvento();
    
    // Apri modal
    const modal = new bootstrap.Modal(document.getElementById('modalCreaEvento'));
    modal.show();
}

/**
 * Apri modal modifica evento
 */
async function apriModalModificaEvento(eventoId) {
    try {
        // Carica dettagli evento
        const response = await fetch(`api_eventi.php?action=get&id=${eventoId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore caricamento evento');
        }
        
        currentEventData = {
            mode: 'edit',
            id: eventoId,
            ...data.data
        };
        
        // Popola form
        popolaFormModificaEvento();
        
        // Apri modal
        const modal = new bootstrap.Modal(document.getElementById('modalCreaEvento'));
        modal.show();
        
    } catch (error) {
        mostraToast('Errore', error.message, 'danger');
    }
}

/**
 * Popola form crea evento
 */
function popolaFormCreaEvento() {
    // Reset form
    document.getElementById('formCreaEvento').reset();
    
    // Titolo modal
    document.getElementById('modalCreaEventoTitle').innerHTML = 
        '<i class="bi bi-plus-circle"></i> Nuovo Evento';
    
    // Popola select tipologie
    const selectTipologia = document.getElementById('eventoTipologia');
    selectTipologia.innerHTML = '<option value="">-- Seleziona Tipologia --</option>';
    tipologieEventi.forEach(tip => {
        const option = document.createElement('option');
        option.value = tip.id;
        option.textContent = `${tip.icona} ${tip.nome}`;
        option.dataset.coloreBg = tip.colore_bg;
        option.dataset.coloreBorder = tip.colore_border;
        selectTipologia.appendChild(option);
    });
    
    // Popola select soci
    const selectSocio = document.getElementById('eventoSocio');
    selectSocio.innerHTML = '<option value="">-- Nessun Socio --</option>';
    sociList.forEach(all => {
        const option = document.createElement('option');
        option.value = all.id;
        option.textContent = `${all.cognome} ${all.nome}`;
        selectSocio.appendChild(option);
    });
    
    // Popola select docenti
    const selectDocente = document.getElementById('eventoDocente');
    selectDocente.innerHTML = '<option value="">-- Seleziona Docente --</option>';
    docentiList.forEach(doc => {
        const option = document.createElement('option');
        option.value = doc.id;
        option.textContent = `${doc.cognome} ${doc.nome}`;
        selectDocente.appendChild(option);
    });
    
    // Popola select materie
    const selectMateria = document.getElementById('eventoMateria');
    selectMateria.innerHTML = '<option value="">-- Seleziona Materia --</option>';
    materieList.forEach(mat => {
        const option = document.createElement('option');
        option.value = mat.id;
        option.textContent = mat.nome;
        selectMateria.appendChild(option);
    });
    
    // Popola select aule
    const selectAula = document.getElementById('eventoAula');
    selectAula.innerHTML = '<option value="">-- Seleziona Aula --</option>';
    auleList.forEach(aula => {
        const option = document.createElement('option');
        option.value = aula.id;
        option.textContent = aula.nome;
        if (currentEventData.aula_id && aula.id == currentEventData.aula_id) {
            option.selected = true;
        }
        selectAula.appendChild(option);
    });
    
    // Pre-compila data e ora se disponibili
    if (currentEventData.data) {
        document.getElementById('eventoDataEvento').value = currentEventData.data;
    }
    if (currentEventData.ora_inizio) {
        document.getElementById('eventoOraInizio').value = currentEventData.ora_inizio;
    }
    
    // Default: evento singolo
    document.getElementById('eventoRicorrente').value = '0';
    toggleRicorrenteFields();
    
    // Pulsante salva
    document.getElementById('btnSalvaEvento').innerHTML = 
        '<i class="bi bi-check-circle"></i> Crea Evento';
}

/**
 * Popola form modifica evento
 */
function popolaFormModificaEvento() {
    // Usa stessa funzione crea per popolare select
    popolaFormCreaEvento();
    
    // Titolo modal
    document.getElementById('modalCreaEventoTitle').innerHTML = 
        '<i class="bi bi-pencil"></i> Modifica Evento';
    
    // Compila campi con dati evento
    document.getElementById('eventoTipologia').value = currentEventData.tipologia_id || '';
    document.getElementById('eventoRicorrente').value = currentEventData.ricorrente ? '1' : '0';
    
    if (currentEventData.ricorrente) {
        document.getElementById('eventoGiornoSettimana').value = currentEventData.giorno_settimana || '';
        document.getElementById('eventoDataInizio').value = currentEventData.data_inizio || '';
        document.getElementById('eventoDataFine').value = currentEventData.data_fine || '';
    } else {
        document.getElementById('eventoDataEvento').value = currentEventData.data_evento || '';
    }
    
    document.getElementById('eventoOraInizio').value = currentEventData.ora_inizio || '';
    document.getElementById('eventoOraFine').value = currentEventData.ora_fine || '';
    document.getElementById('eventoAula').value = currentEventData.aula_id || '';
    document.getElementById('eventoDocente').value = currentEventData.docente_id || '';
    document.getElementById('eventoMateria').value = currentEventData.materia_id || '';
    document.getElementById('eventoSocio').value = currentEventData.socio_id || '';
    document.getElementById('eventoTitolo').value = currentEventData.titolo || '';
    document.getElementById('eventoDescrizione').value = currentEventData.descrizione || '';
    document.getElementById('eventoNote').value = currentEventData.note || '';
    document.getElementById('eventoConfermato').checked = currentEventData.confermato || false;
    
    toggleRicorrenteFields();
    aggiornaPreviewColore();
    
    // Pulsante salva
    document.getElementById('btnSalvaEvento').innerHTML = 
        '<i class="bi bi-check-circle"></i> Salva Modifiche';
}

/**
 * Toggle campi ricorrente/singolo
 */
function toggleRicorrenteFields() {
    const ricorrente = document.getElementById('eventoRicorrente').value === '1';
    
    document.getElementById('campiSingolo').style.display = ricorrente ? 'none' : 'block';
    document.getElementById('campiRicorrente').style.display = ricorrente ? 'block' : 'none';
}

/**
 * Aggiorna preview colore tipologia
 */
function aggiornaPreviewColore() {
    const select = document.getElementById('eventoTipologia');
    const option = select.options[select.selectedIndex];
    const preview = document.getElementById('previewColoreTipologia');
    
    if (option && option.dataset.coloreBg) {
        preview.style.backgroundColor = option.dataset.coloreBg;
        preview.style.borderLeft = `4px solid ${option.dataset.coloreBorder}`;
        preview.style.display = 'block';
        preview.textContent = option.textContent;
    } else {
        preview.style.display = 'none';
    }
}

/**
 * Valida form evento
 */
function validaFormEvento() {
    const errors = [];
    
    // Campi obbligatori
    if (!document.getElementById('eventoTipologia').value) {
        errors.push('Tipologia evento è obbligatoria');
    }
    if (!document.getElementById('eventoAula').value) {
        errors.push('Aula è obbligatoria');
    }
    if (!document.getElementById('eventoOraInizio').value) {
        errors.push('Ora inizio è obbligatoria');
    }
    if (!document.getElementById('eventoOraFine').value) {
        errors.push('Ora fine è obbligatoria');
    }
    
    const ricorrente = document.getElementById('eventoRicorrente').value === '1';
    if (ricorrente) {
        if (!document.getElementById('eventoGiornoSettimana').value) {
            errors.push('Giorno settimana è obbligatorio per eventi ricorrenti');
        }
    } else {
        if (!document.getElementById('eventoDataEvento').value) {
            errors.push('Data evento è obbligatoria per eventi singoli');
        }
    }
    
    // Validazione orari
    const oraInizio = document.getElementById('eventoOraInizio').value;
    const oraFine = document.getElementById('eventoOraFine').value;
    if (oraInizio && oraFine && oraInizio >= oraFine) {
        errors.push('Ora fine deve essere successiva a ora inizio');
    }
    
    return errors;
}

/**
 * Salva evento (create o update)
 */
async function salvaEvento() {
    const errors = validaFormEvento();
    if (errors.length > 0) {
        mostraToast('Validazione Fallita', errors.join('<br>'), 'warning');
        return;
    }
    
    // Raccogli dati form
    const formData = {
        tipologia_id: parseInt(document.getElementById('eventoTipologia').value),
        ricorrente: document.getElementById('eventoRicorrente').value === '1',
        ora_inizio: document.getElementById('eventoOraInizio').value,
        ora_fine: document.getElementById('eventoOraFine').value,
        aula_id: parseInt(document.getElementById('eventoAula').value),
        docente_id: document.getElementById('eventoDocente').value ? parseInt(document.getElementById('eventoDocente').value) : null,
        materia_id: document.getElementById('eventoMateria').value ? parseInt(document.getElementById('eventoMateria').value) : null,
        socio_id: document.getElementById('eventoSocio').value ? parseInt(document.getElementById('eventoSocio').value) : null,
        titolo: document.getElementById('eventoTitolo').value || null,
        descrizione: document.getElementById('eventoDescrizione').value || null,
        note: document.getElementById('eventoNote').value || null,
        confermato: document.getElementById('eventoConfermato').checked
    };
    
    if (formData.ricorrente) {
        formData.giorno_settimana = document.getElementById('eventoGiornoSettimana').value;
        formData.data_inizio = document.getElementById('eventoDataInizio').value || null;
        formData.data_fine = document.getElementById('eventoDataFine').value || null;
    } else {
        formData.data_evento = document.getElementById('eventoDataEvento').value;
    }
    
    // Disabilita pulsante
    const btn = document.getElementById('btnSalvaEvento');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvataggio...';
    
    try {
        let url, method;
        
        if (currentEventData.mode === 'edit') {
            url = `api_eventi.php?action=update&id=${currentEventData.id}`;
            method = 'PUT';
        } else {
            url = 'api_eventi.php?action=create';
            method = 'POST';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore durante il salvataggio');
        }
        
        // Chiudi modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalCreaEvento'));
        modal.hide();
        
        // Mostra successo
        mostraToast(
            'Successo',
            currentEventData.mode === 'edit' ? 'Evento modificato con successo' : 'Evento creato con successo',
            'success'
        );
        
        // Ricarica pagina dopo breve pausa
        setTimeout(() => location.reload(), 1500);
        
    } catch (error) {
        mostraToast('Errore', error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = currentEventData.mode === 'edit' ? 
            '<i class="bi bi-check-circle"></i> Salva Modifiche' : 
            '<i class="bi bi-check-circle"></i> Crea Evento';
    }
}

/**
 * Elimina evento
 */
async function eliminaEvento(eventoId) {
    if (!confirm('Sei sicuro di voler eliminare questo evento?')) {
        return;
    }
    
    try {
        const response = await fetch(`api_eventi.php?action=delete&id=${eventoId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore durante l\'eliminazione');
        }
        
        mostraToast('Successo', 'Evento eliminato con successo', 'success');
        setTimeout(() => location.reload(), 1500);
        
    } catch (error) {
        mostraToast('Errore', error.message, 'danger');
    }
}

/**
 * Mostra toast
 */
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
    const toast = new bootstrap.Toast(toastElement, { autohide: false });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    loadFormData();
    
    // Event listeners
    document.getElementById('eventoTipologia')?.addEventListener('change', aggiornaPreviewColore);
    document.getElementById('eventoRicorrente')?.addEventListener('change', toggleRicorrenteFields);
    document.getElementById('btnSalvaEvento')?.addEventListener('click', salvaEvento);
});