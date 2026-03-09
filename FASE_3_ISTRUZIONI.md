# 🎯 Fase 3 - CRUD UI - Istruzioni Completamento

## ✅ Cosa È Stato Fatto

### 1. Backend Completato ✅
- `api_get_tipologie.php` - API lista tipologie eventi
- `api_get_helpers.php` - API unificata per soci/docenti/materie/aule
- `assets/js/eventi.js` - JavaScript completo per CRUD eventi

### 2. Funzionalità JavaScript Pronte ✅
```javascript
✅ loadFormData() - Carica dati select
✅ apriModalCreaEvento() - Apri modal nuovo evento
✅ apriModalModificaEvento(id) - Apri modal modifica
✅ salvaEvento() - Crea/aggiorna evento
✅ eliminaEvento(id) - Elimina evento
✅ validaFormEvento() - Validazione completa
✅ aggiornaPreviewColore() - Preview colore tipologia
✅ mostraToast() - Notifiche utente
```

---

## 📝 Task Rimanenti

### STEP 1: Aggiungere Modal HTML a `calendario_eventi.php`

Inserire prima del tag `</div>` finale (prima dell' `include footer.php`):

```html
<!-- Modal Crea/Modifica Evento -->
<div class="modal fade" id="modalCreaEvento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCreaEventoTitle">
                    <i class="bi bi-plus-circle"></i> Nuovo Evento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreaEvento">
                    <!-- Tipologia -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipologia Evento *</label>
                        <select class="form-select" id="eventoTipologia" required>
                            <option value="">-- Seleziona --</option>
                        </select>
                        <div id="previewColoreTipologia" class="mt-2 p-2 rounded" style="display:none;"></div>
                    </div>
                    
                    <!-- Ricorrente -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <select class="form-select" id="eventoRicorrente">
                            <option value="0">Evento Singolo</option>
                            <option value="1">Evento Ricorrente</option>
                        </select>
                    </div>
                    
                    <!-- Campi Singolo -->
                    <div id="campiSingolo">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data Evento *</label>
                            <input type="date" class="form-control" id="eventoDataEvento">
                        </div>
                    </div>
                    
                    <!-- Campi Ricorrente -->
                    <div id="campiRicorrente" style="display:none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Giorno Settimana *</label>
                                <select class="form-select" id="eventoGiornoSettimana">
                                    <option value="">-- Seleziona --</option>
                                    <option value="lunedi">Lunedì</option>
                                    <option value="martedi">Martedì</option>
                                    <option value="mercoledi">Mercoledì</option>
                                    <option value="giovedi">Giovedì</option>
                                    <option value="venerdi">Venerdì</option>
                                    <option value="sabato">Sabato</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Data Inizio</label>
                                <input type="date" class="form-control" id="eventoDataInizio">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Data Fine</label>
                                <input type="date" class="form-control" id="eventoDataFine">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orari -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Inizio *</label>
                            <input type="time" class="form-control" id="eventoOraInizio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ora Fine *</label>
                            <input type="time" class="form-control" id="eventoOraFine" required>
                        </div>
                    </div>
                    
                    <!-- Aula -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Aula *</label>
                        <select class="form-select" id="eventoAula" required>
                            <option value="">-- Seleziona --</option>
                        </select>
                    </div>
                    
                    <!-- Docente e Materia -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Docente</label>
                            <select class="form-select" id="eventoDocente">
                                <option value="">-- Nessuno --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Materia</label>
                            <select class="form-select" id="eventoMateria">
                                <option value="">-- Nessuna --</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Socio -->
                    <div class="mb-3">
                        <label class="form-label">Socio</label>
                        <select class="form-select" id="eventoSocio">
                            <option value="">-- Nessuno --</option>
                        </select>
                    </div>
                    
                    <!-- Titolo -->
                    <div class="mb-3">
                        <label class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="eventoTitolo">
                    </div>
                    
                    <!-- Descrizione -->
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" id="eventoDescrizione" rows="2"></textarea>
                    </div>
                    
                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" id="eventoNote" rows="2"></textarea>
                    </div>
                    
                    <!-- Confermato -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="eventoConfermato" checked>
                        <label class="form-check-label" for="eventoConfermato">
                            Evento Confermato
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvaEvento">
                    <i class="bi bi-check-circle"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>
```

### STEP 2: Aggiungere Script JS

Prima del `</body>` tag in `calendario_eventi.php`:

```html
<script src="assets/js/eventi.js"></script>
```

### STEP 3: Modificare Pulsante "Nuova Lezione"

Cambiare da:
```html
<button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addLezioneModal">
```

A:
```html
<button class="btn btn-success me-2" onclick="apriModalCreaEvento()">
```

### STEP 4: Aggiungere Click su Celle Vuote

Nel rendering delle celle del calendario, aggiungere click handler per creare evento:

```php
<td class="calendario-cell" 
    data-aula-id="<?= $aula['id'] ?>" 
    data-ora="<?= $slot['inizio'] ?>"
    onclick="apriModalCreaEvento('<?= $data_selezionata ?>', <?= $aula['id'] ?>, '<?= $slot['inizio'] ?>')">
```

### STEP 5: Aggiungere Doppio Click su Eventi

Modificare rendering eventi per permettere modifica:

```php
<div class="lezione-slot..." 
     ondblclick="apriModalModificaEvento(<?= $evento_slot['id'] ?>)">
```

---

## 🧪 Test Procedure

1. **Apri Calendario**: `http://localhost:8000/calendario_eventi.php`
2. **Test Crea**:
   - Click "Nuova Lezione"
   - Compilare form
   - Verificare preview colore
   - Salvare
3. **Test Modifica**:
   - Doppio click su evento
   - Modificare dati
   - Salvare
4. **Test Validazione**:
   - Provare salvare senza campi obbligatori
   - Verificare messaggi errore

---

## ✨ Quick Wins (Futuri)

1. **Drag & Drop Eventi** (2-3 giorni)
2. **Filtri Avanzati** (1 giorno)
3. **Export PDF** (2 giorni)
4. **Gestione Tipologie UI** (2 giorni)

---

## 📊 Progress

```
✅ Fase 1: Backend API (COMPLETATA)
✅ Fase 2: Frontend Integration (COMPLETATA)
🔄 Fase 3: CRUD UI (80% - manca solo HTML modal)
⏳ Fase 4: Prenotazioni
⏳ Fase 5: Pagamenti
⏳ Fase 6: Advanced Features
```

---

## 🎯 Next Immediate Action

**COMPLETA STEP 1-5** sopra per avere CRUD completo funzionante!

Una volta completati questi 5 step, il sistema sarà completamente funzionale con:
- ✅ Creazione eventi via modal
- ✅ Modifica eventi esistenti
- ✅ Validazione form
- ✅ Preview colori tipologia
- ✅ Gestione ricorrenti/singoli
- ✅ Notifiche toast

**Tempo stimato**: 30-45 minuti