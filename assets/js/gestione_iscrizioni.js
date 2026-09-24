/**
 * Gestione Iscrizioni - JavaScript Module
 * Estratto da gestione_iscrizioni.php per CSP compliance
 * Tutti gli script inline spostati in questo file esterno
 */

let currentStep = 1;
let newSocioId = null;
const BASE_URL = window.location.origin;

// Carica dati iniziali
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners per inline handlers rimossi dal HTML
    const btnResetFiltro = document.getElementById('btnResetFiltro');
    if (btnResetFiltro) {
        btnResetFiltro.addEventListener('click', resetFiltroMese);
    }
    
    const filtroMese = document.getElementById('filtroMese');
    if (filtroMese) {
        filtroMese.addEventListener('change', cambiaFiltroMese);
    }
    
    const btnSave = document.getElementById('btnSave');
    if (btnSave) {
        btnSave.addEventListener('click', salvaIscrizione);
    }
    
    const btnNewEnrollment = document.getElementById('btnNewEnrollment');
    if (btnNewEnrollment) {
        btnNewEnrollment.addEventListener('click', nuovaIscrizione);
    }
    
    const btnModificaDaDettaglio = document.getElementById('btnModificaDaDettaglio');
    if (btnModificaDaDettaglio) {
        btnModificaDaDettaglio.addEventListener('click', apriModificaDaDettaglio);
    }
    
    // Event listeners per bottoni dinamici (dettaglio e modifica dalla tabella)
    document.querySelectorAll('.btn-dettaglio').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-iscrizione-id');
            caricaDettagliIscrizione(id);
        });
    });
    
    document.querySelectorAll('.btn-modifica').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-iscrizione-id');
            apriModificaIscrizione(id);
        });
    });
    
    caricaSoci();
    caricaTipiCorso();
    caricaMaterie();
    caricaAule();
    
    // Toggle socio mode
    document.querySelectorAll('[name="socio_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                document.getElementById('div_existing_socio').style.display = 'block';
                document.getElementById('div_new_socio').style.display = 'none';
            } else {
                document.getElementById('div_existing_socio').style.display = 'none';
                document.getElementById('div_new_socio').style.display = 'block';
            }
        });
    });
    
    // Navigation buttons
    document.getElementById('btnNext').addEventListener('click', nextStep);
    document.getElementById('btnPrev').addEventListener('click', prevStep);
    
    // Check conflicts on change
    ['giorno_settimana', 'ora_inizio', 'aula_id'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', checkConflicts);
    });
    
    // Carica docenti quando cambia materia
    const materiaSelect = document.getElementById('materia_id');
    if (materiaSelect) {
        materiaSelect.addEventListener('change', function() {
            const materiaId = this.value;
            const selectDocente = document.getElementById('docente_id');
            selectDocente.innerHTML = '<option value="">Caricamento...</option>';
            
            if (!materiaId) {
                selectDocente.innerHTML = '<option value="">Seleziona prima una materia</option>';
                return;
            }
            
            console.log('Caricamento docenti per materia:', materiaId);
            
            fetch(`${BASE_URL}/api/api_docenti_per_materia.php?materia_id=${materiaId}`)
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
    }
    
    // Mostra info corso
    const tipoCorsoSelect = document.getElementById('tipo_corso_id');
    if (tipoCorsoSelect) {
        tipoCorsoSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const durata = option.dataset.durata;
            const costo = option.dataset.costo;
            const infoCorsEl = document.getElementById('info_corso');
            
            if (durata && costo && infoCorsEl) {
                infoCorsEl.innerHTML = 
                    `<i class="bi bi-info-circle"></i> Durata: ${durata} min | Costo base: €${costo}/mese (4 lezioni)`;
            }
        });
    }
});

function nextStep() {
    if (currentStep === 1) {
        const mode = document.querySelector('[name="socio_mode"]:checked').value;
        if (mode === 'existing') {
            const socioId = document.getElementById('select_socio').value;
            if (!socioId) {
                mostraToast('Attenzione', 'Seleziona un socio', 'warning');
                return;
            }
            document.getElementById('selected_socio_id').value = socioId;
        } else {
            // Nuovo socio
            if (newSocioId) {
                // Già creato, vai avanti
                document.getElementById('selected_socio_id').value = newSocioId;
            } else {
                // Crea nuovo
                if (!document.getElementById('new_cognome').value || !document.getElementById('new_nome').value) {
                    mostraToast('Attenzione', 'Inserisci cognome e nome', 'warning');
                    return;
                }
                creaSocio();
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

function creaSocio() {
    const data = {
        cognome: document.getElementById('new_cognome').value,
        nome: document.getElementById('new_nome').value,
        data_nascita: document.getElementById('new_data_nascita').value,
        email: document.getElementById('new_email').value,
        telefono: document.getElementById('new_telefono').value,
        indirizzo: document.getElementById('new_indirizzo').value,
        attivo: 1
    };
    
    fetch(`${BASE_URL}/api/api_soci_crud.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', data: data})
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            newSocioId = result.id;
            document.getElementById('selected_socio_id').value = newSocioId;
            mostraToast('Successo', 'Socio creato', 'success');
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
    
    fetch(`${BASE_URL}/api/api_check_conflicts.php?giorno=${giorno}&ora_inizio=${oraInizio}&aula_id=${aulaId}&tipo_corso_id=${tipoCorsoId}`)
    .then(r => r.json())
    .then(data => {
        const alert = document.getElementById('conflitti_alert');
        if (data.conflict) {
            alert.classList.remove('d-none');
            
            let html = '<p><strong>Lezione esistente:</strong></p>';
            html += `<ul><li>${data.conflict.socio} - ${data.conflict.materia}</li>`;
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

function caricaSoci() {
    console.log('📡 caricaSoci() - Fetching soci...');
    fetch(`${BASE_URL}/api/api_get_helpers.php?type=soci`)
    .then(r => {
        console.log('📥 Response status:', r.status);
        return r.json();
    })
    .then(data => {
        console.log('📦 Data ricevuti:', data);
        const select = document.getElementById('select_socio');
        select.innerHTML = '<option value="">Seleziona socio...</option>';
        
        if (data.success && data.data) {
            console.log('✅ Success! Soci ricevuti:', data.data.length);
            data.data.forEach((a) => {
                select.innerHTML += `<option value="${a.id}">${a.cognome} ${a.nome}</option>`;
            });
            console.log('✅ Select popolata con', data.data.length, 'soci');
        } else {
            console.warn('❌ Data non valid! success:', data.success, 'data:', data.data);
            select.innerHTML = '<option value="">Errore nel caricamento soci</option>';
        }
    })
    .catch(err => {
        console.error('❌ Fetch error:', err);
        const select = document.getElementById('select_socio');
        select.innerHTML = '<option value="">Errore di connessione</option>';
    });
}

function caricaTipiCorso() {
    fetch(`${BASE_URL}/api/api_configurazione_corsi.php?action=list&tipo=corso`)
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
    fetch(`${BASE_URL}/api/api_materie.php?action=list`)
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
    fetch(`${BASE_URL}/api/api_aule.php?action=list`)
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
    
    fetch(`${BASE_URL}/api/api_iscrizioni.php`, {
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
            const socioNome = document.getElementById('select_socio').selectedOptions[0]?.text || 
                               (document.getElementById('new_cognome').value + ' ' + document.getElementById('new_nome').value);
            const materiaNome = document.getElementById('materia_id').selectedOptions[0]?.text;
            const docenteNome = document.getElementById('docente_id').selectedOptions[0]?.text;
            const giornoNome = document.getElementById('giorno_settimana').selectedOptions[0]?.text;
            const oraInizio = document.getElementById('ora_inizio').value;
            const aulaNome = document.getElementById('aula_id').selectedOptions[0]?.text;
            
            document.getElementById('success_details').innerHTML = `
                <h5>Riepilogo Iscrizione</h5>
                <ul class="list-unstyled mb-0">
                    <li><strong>Socio:</strong> ${socioNome}</li>
                    <li><strong>Materia:</strong> ${materiaNome}</li>
                    <li><strong>Docente:</strong> ${docenteNome}</li>
                    <li><strong>Orario:</strong> ${giornoNome} alle ${oraInizio}</li>
                    <li><strong>Aula:</strong> ${aulaNome}</li>
                </ul>
            `;
            currentStep = 5;
            showStep(5);
            
            // Ricarica la pagina dopo 3 secondi
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else if (data.conflict) {
            // Mostra step errore
            const conf = data.conflict;
            let html = `<h6>Lezione già presente:</h6><ul class="mb-0">`;
            html += `<li><strong>Socio:</strong> ${conf.socio}</li>`;
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
    fetch(`${BASE_URL}/api/api_iscrizioni.php?action=get&id=${id}`)
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
        document.getElementById('det_socio').textContent = i.socio || '-';
        
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
    fetch(`${BASE_URL}/api/api_iscrizioni.php?action=get&id=${id}`)
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
        document.getElementById('selected_socio_id').value = i.socio_id;
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
        window.location.href = `${BASE_URL}/gestione_iscrizioni.php?mese=${mese}`;
    }
}

function resetFiltroMese() {
    window.location.href = `${BASE_URL}/gestione_iscrizioni.php`;
}
