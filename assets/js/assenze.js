/**
 * JavaScript per Gestione Assenze
 * Caricamento dinamico lezioni da API
 */

// Funzione per mostrare alert inline nel modal
function showLezioniAlert(message, type = 'warning') {
    const alertDiv = document.getElementById('lezioniAlert');
    const alertMessage = document.getElementById('lezioniAlertMessage');
    
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertMessage.textContent = message;
    alertDiv.style.display = 'block';
    
    // Auto-hide dopo 5 secondi
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 150);
    }, 5000);
}

// Funzione per nascondere alert
function hideLezioniAlert() {
    const alertDiv = document.getElementById('lezioniAlert');
    alertDiv.classList.remove('show');
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 150);
}

// Mappa giorni settimana italiano -> numero (0=domenica, 1=lunedì, etc.)
const giorniSettimana = {
    'domenica': 0,
    'lunedi': 1,
    'lunedì': 1,
    'martedi': 2,
    'martedì': 2,
    'mercoledi': 3,
    'mercoledì': 3,
    'giovedi': 4,
    'giovedì': 4,
    'venerdi': 5,
    'venerdì': 5,
    'sabato': 6
};

// Variabile globale per tracciare giorno lezione selezionata
let giornoLezioneSelezionata = null;

// Carica lezioni quando si seleziona un socio
document.getElementById('socioSelectHelper').addEventListener('change', function() {
    const socioId = this.value;
    const lezioniContainer = document.getElementById('lezioniContainer');
    const lezioneSelect = document.getElementById('lezioneSelectFinal');
    
    if (socioId) {
        lezioniContainer.style.display = 'block';
        lezioneSelect.innerHTML = '<option value="">Caricamento...</option>';
        lezioneSelect.disabled = true;
        
        // Chiamata API
        fetch(`${BASE_URL}/api/api_get_lezioni_allievo.php?socio_id=${socioId}`)
            .then(r => r.json())
            .then(response => {
                lezioneSelect.innerHTML = '<option value="">Seleziona lezione...</option>';
                
                // Gestione risposta strutturata
                if (response.error) {
                    showLezioniAlert('Errore: ' + response.error, 'danger');
                    lezioneSelect.disabled = false;
                    return;
                }
                
                const lezioni = response.lezioni || [];
                
                if (lezioni.length > 0) {
                    lezioni.forEach(lez => {
                        const docente = lez.docente ? ` - ${lez.docente}` : '';
                        const giornoLower = lez.giorno_settimana_lower || lez.giorno_settimana.toLowerCase();
                        lezioneSelect.innerHTML += `<option value="${lez.id}" data-giorno="${giornoLower}">${lez.giorno_settimana} ${lez.ora_inizio}-${lez.ora_fine} - ${lez.materia}${docente}</option>`;
                    });
                    lezioneSelect.disabled = false;
                } else {
                    lezioneSelect.innerHTML = '<option value="">Nessuna lezione trovata per questo socio</option>';
                    lezioneSelect.disabled = true;
                    showLezioniAlert('Questo socio non ha lezioni programmate nel calendario settimanale.', 'info');
                }
            })
            .catch((err) => {
                console.error('Errore:', err);
                showLezioniAlert('Impossibile caricare le lezioni. Riprova o contatta il supporto tecnico.', 'danger');
                lezioneSelect.innerHTML = '<option value="">Errore caricamento</option>';
                lezioneSelect.disabled = true;
            });
    } else {
        lezioniContainer.style.display = 'none';
        lezioneSelect.innerHTML = '<option value="">Prima seleziona un socio</option>';
        hideLezioniAlert();
    }
});

// Listener per selezione lezione - salva giorno e configura date picker
document.getElementById('lezioneSelectFinal').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const dataInput = document.getElementById('dataAssenza');
    const lezioneId = selectedOption.value;
    const socioId = document.getElementById('socioSelectHelper').value;
    
    if (lezioneId) {
        // Salva giorno lezione
        giornoLezioneSelezionata = selectedOption.getAttribute('data-giorno');
        
        // Abilita date picker
        dataInput.disabled = false;
        
        // Mostra info helper
        const giornoCapitalized = giornoLezioneSelezionata.charAt(0).toUpperCase() + giornoLezioneSelezionata.slice(1);
        showLezioniAlert(`Seleziona una data che cada di ${giornoCapitalized} per questa lezione`, 'info');
        
        // Carica contatori assenze/recuperi
        fetch(`${BASE_URL}/api/api_get_contatori_assenze.php?socio_id=${socioId}&lezione_id=${lezioneId}`)
            .then(r => r.json())
            .then(response => {
                if (response.success && response.contatori) {
                    const cont = response.contatori;
                    const contatoriDiv = document.getElementById('contatoriAnnoScolastico');
                    contatoriDiv.innerHTML = `
                        <div class="alert alert-info mb-3">
                            <strong>Anno Scolastico ${cont.anno_scolastico}:</strong><br>
                            <i class="bi bi-calendar-x"></i> Assenze: <strong>${cont.assenze}</strong> | 
                            <i class="bi bi-arrow-repeat"></i> Recuperi: <strong>${cont.recuperi}</strong>
                        </div>
                    `;
                    contatoriDiv.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Errore caricamento contatori:', err);
            });
    } else {
        giornoLezioneSelezionata = null;
        dataInput.disabled = true;
        dataInput.value = '';
        document.getElementById('contatoriAnnoScolastico').style.display = 'none';
    }
});

// Validazione data al cambio
document.getElementById('dataAssenza').addEventListener('change', function() {
    if (!giornoLezioneSelezionata) return;
    
    const selectedDate = new Date(this.value);
    const dayOfWeek = selectedDate.getDay(); // 0=domenica, 1=lunedì, etc.
    const expectedDay = giorniSettimana[giornoLezioneSelezionata];
    
    if (dayOfWeek !== expectedDay) {
        const giornoSelezionato = Object.keys(giorniSettimana).find(k => giorniSettimana[k] === dayOfWeek);
        const giornoAtteso = giornoLezioneSelezionata.charAt(0).toUpperCase() + giornoLezioneSelezionata.slice(1);
        
        showLezioniAlert(
            `Attenzione! Hai selezionato un ${giornoSelezionato} ma la lezione è di ${giornoAtteso}. Seleziona una data corretta.`,
            'danger'
        );
        
        // Resetta il campo
        this.value = '';
        this.focus();
    } else {
        hideLezioniAlert();
    }
});

// Init tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
