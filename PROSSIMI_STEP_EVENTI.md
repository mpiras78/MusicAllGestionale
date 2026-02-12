# 🎯 PROSSIMI STEP - Sistema Eventi/Calendario/Pagamenti

## ✅ Completato
- [x] Migration database eseguita con successo
- [x] 35 lezioni migrate in `eventi_calendario`
- [x] Schema `schema_sqlite.sql` aggiornato per nuovi deployment
- [x] Password admin aggiornata: `P@ssw0rd1`

---

## 📋 FASE 1: Backend - Models & Controllers (Priorità ALTA)

### 1.1 Creare Models
**Percorso**: `app/Models/`

```php
// Da creare:
- EventoCalendario.php
- TipologiaEvento.php
- SocioOccasionale.php
- Iscrizione.php
- IscrizioneDettaglio.php
- Pagamento.php
- ListinoPrezzo.php
```

**Esempio struttura EventoCalendario.php**:
```php
<?php
class EventoCalendario extends Model {
    protected $table = 'eventi_calendario';
    protected $fillable = ['tipologia_id', 'ricorrente', 'giorno_settimana', ...];
    
    // Relations
    public function tipologia() { ... }
    public function allievo() { ... }
    public function docente() { ... }
    public function aula() { ... }
    public function iscrizione() { ... }
}
```

### 1.2 Creare Controllers
**Percorso**: `includes/controllers/`

```php
// Da creare:
- EventiController.php          // CRUD eventi_calendario
- TipologieEventoController.php // Gestione tipologie
- IscrizioniController.php      // Sistema iscrizioni mensili
- PagamentiController.php       // Sistema pagamenti
- PrenotazioniController.php    // Prenotazioni sale
```

---

## 📋 FASE 2: API Endpoints (Priorità ALTA)

### 2.1 API Eventi Calendario
**File da creare**:

```
api_eventi_calendario.php
├── GET    /api/eventi              # Lista eventi (filtri: data, aula, docente, allievo)
├── POST   /api/eventi              # Crea evento
├── PUT    /api/eventi/{id}         # Modifica evento
└── DELETE /api/eventi/{id}         # Elimina evento

api_eventi_ricorrenti.php
├── POST   /api/eventi/ricorrenti   # Genera eventi ricorrenti per periodo
└── PUT    /api/eventi/eccezioni    # Gestione eccezioni (festività, chiusure)
```

### 2.2 API Prenotazioni
**File da creare**:

```
api_prenotazioni.php
├── GET    /api/prenotazioni              # Lista prenotazioni
├── POST   /api/prenotazioni              # Crea prenotazione
├── PUT    /api/prenotazioni/{id}/stato   # Conferma/Annulla
└── GET    /api/prenotazioni/disponibilita # Slot disponibili
```

### 2.3 API Iscrizioni
**File da creare**:

```
api_iscrizioni.php
├── GET    /api/iscrizioni                    # Lista iscrizioni
├── POST   /api/iscrizioni                    # Crea iscrizione mensile
├── GET    /api/iscrizioni/{id}/dettagli      # Dettagli lezioni incluse
└── POST   /api/iscrizioni/{id}/pagamento     # Genera pagamento mensile
```

### 2.4 API Pagamenti
**File da creare**:

```
api_pagamenti.php
├── GET    /api/pagamenti                # Lista pagamenti (filtri: stato, scadenza)
├── POST   /api/pagamenti/{id}/registra  # Registra pagamento
├── GET    /api/pagamenti/scadenze       # Pagamenti in scadenza
└── GET    /api/pagamenti/report         # Report incassi
```

---

## 📋 FASE 3: Frontend - Interfacce (Priorità MEDIA)

### 3.1 Calendario Unificato
**File da creare**: `calendario_eventi.php`

Features:
- Vista settimanale/mensile con FullCalendar.js
- Codice colore per tipologie evento
- Filtri: aula, docente, allievo, tipologia
- Drag & drop per spostare eventi
- Click per dettagli/modifica
- Legenda tipologie

### 3.2 Gestione Prenotazioni Sale
**File da creare**: `gestione_prenotazioni.php`

Features:
- Lista prenotazioni con filtri
- Modal creazione prenotazione (allievi/docenti/esterni)
- Calcolo automatico costo (da listino)
- Conferma/Rifiuta prenotazioni
- Invio notifiche email

### 3.3 Gestione Iscrizioni Mensili
**File da creare**: `gestione_iscrizioni.php`

Features:
- Crea iscrizione mensile per allievo
- Selezione lezioni ricorrenti da includere
- Calcolo costo mensile totale
- Genera pagamento automatico
- Stampa ricevuta/fattura

### 3.4 Gestione Pagamenti
**File da creare**: `gestione_pagamenti.php`

Features:
- Lista pagamenti con stato (da pagare, pagato, scaduto)
- Filtri: periodo, stato, allievo, tipo
- Registra pagamento (modalità, data, importo)
- Genera ricevuta/fattura PDF
- Dashboard incassi con grafici

---

## 📋 FASE 4: Migrazione Logica Esistente (Priorità MEDIA)

### 4.1 Adattare Calendario Attuale
**File**: `calendario.php`

- Sostituire query su `lezioni` con `v_calendario_unificato`
- Aggiungere visualizzazione prenotazioni
- Differenziare colori per tipologia evento

### 4.2 Adattare Gestione Assenze
**File**: `gestione_assenze.php`, `assenze_docente.php`

- Query su `eventi_calendario` invece di `lezioni`
- Join con `tipologie_evento` per filtrare solo lezioni

### 4.3 Adattare Recuperi
**File**: `gestione_recuperi.php`, `recuperi.php`

- Creare eventi con tipologia `LEZ_RECUPERO`
- Link a assenza originale

---

## 📋 FASE 5: Automazioni (Priorità BASSA)

### 5.1 Cron Job - Generazione Iscrizioni
**File da creare**: `cron/genera_iscrizioni_mensili.php`

```php
// Eseguire il 1° di ogni mese:
// - Crea iscrizioni per allievi con lezioni attive
// - Genera pagamenti relativi
// - Invia email riepilogo
```

### 5.2 Cron Job - Solleciti Pagamenti
**File da creare**: `cron/solleciti_pagamenti.php`

```php
// Eseguire giornalmente:
// - Trova pagamenti in scadenza/scaduti
// - Invia email sollecito
// - Log tentativi
```

### 5.3 Cron Job - Pulizia Dati
**File da creare**: `cron/pulizia_eventi_passati.php`

```php
// Eseguire settimanalmente:
// - Archivia eventi vecchi (> 1 anno)
// - Cleanup prenotazioni rifiutate/cancellate
```

---

## 📋 FASE 6: Testing & Documentazione (Priorità ALTA)

### 6.1 Test Funzionali
**Directory**: `tests/eventi/`

```
- test_crea_evento_ricorrente.php
- test_prenotazione_sala.php
- test_iscrizione_mensile.php
- test_calcolo_pagamento.php
- test_conflitti_orari.php
```

### 6.2 Documentazione API
**File da creare**: `docs/API_EVENTI.md`

- Endpoint disponibili
- Parametri richiesti/opzionali
- Esempi request/response
- Codici errore

### 6.3 Manuale Utente
**File da creare**: `docs/MANUALE_EVENTI.md`

- Come creare prenotazioni
- Come gestire iscrizioni mensili
- Come registrare pagamenti
- FAQ

---

## 🎯 PRIORITÀ SUGGERITA

### Sprint 1 (1-2 settimane)
1. ✅ Models base (EventoCalendario, TipologiaEvento)
2. ✅ EventiController CRUD base
3. ✅ API eventi_calendario (GET, POST, PUT, DELETE)
4. ✅ Adattare calendario.php per usare nuova struttura

### Sprint 2 (1-2 settimane)
1. ✅ Models iscrizioni e pagamenti
2. ✅ IscrizioniController & PagamentiController
3. ✅ API iscrizioni e pagamenti
4. ✅ UI gestione_iscrizioni.php base

### Sprint 3 (1-2 settimane)
1. ✅ PrenotazioniController
2. ✅ API prenotazioni
3. ✅ UI gestione_prenotazioni.php
4. ✅ Sistema calcolo prezzi da listini

### Sprint 4 (1 settimana)
1. ✅ Testing completo
2. ✅ Documentazione API
3. ✅ Manuale utente
4. ✅ Bug fixing

---

## 📝 Note Importanti

### Backward Compatibility
- ⚠️ Mantenere tabella `lezioni` per compatibilità
- ⚠️ Queries esistenti continueranno a funzionare
- ✅ Nuove features useranno `eventi_calendario`
- 📅 Deprecazione graduale di `lezioni` in v2.0

### Sicurezza
- 🔒 Validare sempre `tipologia_id` (deve esistere in DB)
- 🔒 Controllare conflitti orari (aula/docente occupati)
- 🔒 Permessi: solo admin può modificare listini prezzi
- 🔒 Log tutte le operazioni su pagamenti (auditing)

### Performance
- ⚡ Indici già creati su colonne principali
- ⚡ VIEW `v_calendario_unificato` per query ottimizzate
- ⚡ Considerare cache per calendario (Redis/Memcached)

---

## 🚀 Quick Start - Primo Task

```bash
# 1. Crea primo Model
touch app/Models/EventoCalendario.php

# 2. Crea primo Controller
touch includes/controllers/EventiController.php

# 3. Crea prima API
touch api_eventi_calendario.php

# 4. Test
php tests/test_api_eventi.php
```

---

**Data ultimo aggiornamento**: 2026-02-12  
**Versione sistema**: v1.1.0 + Eventi