# 🎫 Sistema Prenotazioni - Architettura e Design

## 📋 Overview

Sistema per gestire **iscrizioni/prenotazioni** degli soci agli eventi (lezioni, saggi, workshop, etc.) con conferma admin, pagamenti e notifiche.

---

## 🎯 Casi d'Uso

### 1. Socio/Genitore (Portale Pubblico)
- Visualizza calendario eventi disponibili
- Prenota posto per evento singolo
- Iscrive a corso ricorrente (es: 10 lezioni piano)
- Vede stato prenotazioni (pending/confermato/rifiutato)
- Riceve email conferma/rifiuto
- Paga online prenotazione

### 2. Admin/Segreteria
- Vede tutte le prenotazioni
- Conferma/Rifiuta prenotazioni
- Gestisce liste d'attesa
- Monitora pagamenti
- Genera report iscrizioni

### 3. Docente
- Vede chi è iscritto alle sue lezioni
- Segna presenze
- Vede storico soci

---

## 🗄️ Database Schema

### Tabella: `prenotazioni`

```sql
CREATE TABLE prenotazioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Relazioni
    evento_id INTEGER NOT NULL,
    socio_id INTEGER NOT NULL,
    
    -- Dati prenotazione
    tipo_prenotazione VARCHAR(20) NOT NULL, -- 'singola', 'pacchetto', 'abbonamento'
    numero_eventi INTEGER DEFAULT 1, -- Per pacchetti (es: 10 lezioni)
    eventi_utilizzati INTEGER DEFAULT 0,
    
    -- Stato workflow
    stato VARCHAR(20) NOT NULL DEFAULT 'pending', 
    -- 'pending' -> in attesa conferma
    -- 'confermato' -> accettato da admin
    -- 'rifiutato' -> rifiutato da admin
    -- 'cancellato' -> cancellato da utente
    -- 'completato' -> evento terminato
    -- 'lista_attesa' -> in lista d'attesa
    
    -- Pagamento
    importo_totale DECIMAL(10,2) NOT NULL,
    importo_pagato DECIMAL(10,2) DEFAULT 0,
    stato_pagamento VARCHAR(20) DEFAULT 'non_pagato',
    -- 'non_pagato', 'parziale', 'pagato', 'rimborsato'
    
    metodo_pagamento VARCHAR(50), -- 'carta', 'bonifico', 'contanti', 'paypal'
    riferimento_pagamento VARCHAR(255), -- Transaction ID, numero bonifico, etc
    data_pagamento DATETIME,
    
    -- Note e motivazioni
    note_utente TEXT, -- Note dell'utente alla prenotazione
    note_admin TEXT, -- Note admin (motivazione rifiuto, etc)
    motivo_cancellazione TEXT,
    
    -- Notifiche
    email_inviata BOOLEAN DEFAULT 0,
    data_email DATETIME,
    
    -- Audit
    creato_da INTEGER, -- user_id
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confermato_da INTEGER, -- admin user_id
    confermato_at DATETIME,
    
    -- Constraints
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id),
    FOREIGN KEY (socio_id) REFERENCES soci(id),
    FOREIGN KEY (creato_da) REFERENCES users(id),
    FOREIGN KEY (confermato_da) REFERENCES users(id)
);

-- Indici per performance
CREATE INDEX idx_prenotazioni_evento ON prenotazioni(evento_id);
CREATE INDEX idx_prenotazioni_socio ON prenotazioni(socio_id);
CREATE INDEX idx_prenotazioni_stato ON prenotazioni(stato);
CREATE INDEX idx_prenotazioni_pagamento ON prenotazioni(stato_pagamento);
```

### Tabella: `prenotazioni_storia`

```sql
CREATE TABLE prenotazioni_storia (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prenotazione_id INTEGER NOT NULL,
    
    -- Cambio stato
    stato_precedente VARCHAR(20),
    stato_nuovo VARCHAR(20),
    
    -- Chi ha fatto il cambio
    modificato_da INTEGER,
    motivo TEXT,
    
    -- Timestamp
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (prenotazione_id) REFERENCES prenotazioni(id),
    FOREIGN KEY (modificato_da) REFERENCES users(id)
);
```

### Tabella: `pacchetti_lezioni` (Opzionale)

```sql
CREATE TABLE pacchetti_lezioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    nome VARCHAR(100) NOT NULL, -- "Pacchetto 10 Lezioni Piano"
    descrizione TEXT,
    
    numero_lezioni INTEGER NOT NULL,
    validita_giorni INTEGER, -- Validità pacchetto (es: 90 giorni)
    
    prezzo DECIMAL(10,2) NOT NULL,
    prezzo_scontato DECIMAL(10,2),
    
    -- Vincoli
    materia_id INTEGER, -- Solo per questa materia
    docente_id INTEGER, -- Solo con questo docente
    
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id)
);
```

---

## 🔄 Workflow Stati Prenotazione

```
┌─────────────┐
│   PENDING   │ ← Utente prenota
└──────┬──────┘
       │
       ├──→ Admin CONFERMA ──→ ┌─────────────┐
       │                        │ CONFERMATO  │
       │                        └──────┬──────┘
       │                               │
       │                        Evento termina
       │                               │
       │                               ▼
       │                        ┌─────────────┐
       │                        │ COMPLETATO  │
       │                        └─────────────┘
       │
       ├──→ Admin RIFIUTA ───→ ┌─────────────┐
       │                        │  RIFIUTATO  │
       │                        └─────────────┘
       │
       ├──→ Posti esauriti ──→ ┌──────────────┐
       │                        │ LISTA_ATTESA │
       │                        └──────────────┘
       │
       └──→ Utente cancella ──→ ┌─────────────┐
                                 │ CANCELLATO  │
                                 └─────────────┘
```

---

## 🎨 UI Components

### 1. Calendario Pubblico (Frontend Soci)

**Pagina:** `prenotazioni_pubblico.php`

```
┌─────────────────────────────────────────┐
│  🎵 MusicAll - Prenota la tua lezione  │
├─────────────────────────────────────────┤
│                                         │
│  [Filtri: Materia ▼] [Docente ▼]      │
│                                         │
│  📅 Lunedì 10/02                       │
│  ┌──────────────────────────────────┐  │
│  │ 09:00-10:00 | 🎹 Piano            │  │
│  │ Prof. Rossi | Aula Piano          │  │
│  │ Posti: 1/1 disponibili            │  │
│  │ Prezzo: 35€                       │  │
│  │         [🎫 Prenota]   [ℹ️ Info]  │  │
│  └──────────────────────────────────┘  │
│                                         │
│  ┌──────────────────────────────────┐  │
│  │ 10:30-11:30 | 🎸 Chitarra        │  │
│  │ Prof. Bianchi | Aula Jazz         │  │
│  │ Posti: 3/5 disponibili            │  │
│  │ Prezzo: 30€                       │  │
│  │         [🎫 Prenota]   [ℹ️ Info]  │  │
│  └──────────────────────────────────┘  │
│                                         │
│  📦 Pacchetti disponibili:             │
│  • 10 Lezioni Piano - 300€ (sconto 50€)│
│  • 5 Lezioni Chitarra - 140€          │
│                                         │
└─────────────────────────────────────────┘
```

### 2. Modal Prenotazione

```
┌──────────────────────────────────────┐
│  Prenota Lezione                     │
├──────────────────────────────────────┤
│                                      │
│  🎹 Lezione Piano                    │
│  📅 Lunedì 10/02 - 09:00-10:00      │
│  👨‍🏫 Prof. Rossi                      │
│  🚪 Aula Piano                       │
│                                      │
│  Tipo Prenotazione:                  │
│  ○ Lezione Singola - 35€            │
│  ● Usa Pacchetto (hai 8 lezioni)   │
│                                      │
│  Dati Socio:                       │
│  Nome: [____________]                │
│  Email: [____________]               │
│  Telefono: [____________]            │
│                                      │
│  Note (opzionali):                   │
│  [_____________________]             │
│                                      │
│  ☐ Accetto termini e condizioni     │
│                                      │
│  [Annulla]  [Conferma Prenotazione] │
└──────────────────────────────────────┘
```

### 3. Dashboard Admin Prenotazioni

**Pagina:** `gestione_prenotazioni.php`

```
┌──────────────────────────────────────────────┐
│  🎫 Gestione Prenotazioni                    │
├──────────────────────────────────────────────┤
│                                              │
│  Filtri:                                     │
│  [Stato ▼] [Data ▼] [Materia ▼] [Cerca...] │
│                                              │
│  📊 Statistiche:                             │
│  🕐 Pending: 15  ✅ Confermate: 142         │
│  ❌ Rifiutate: 8  📋 Lista Attesa: 3        │
│                                              │
│  ┌──────────────────────────────────────┐   │
│  │ #123 | Marco Rossi                   │   │
│  │ 🎹 Piano | 10/02 09:00              │   │
│  │ 💶 35€ | ⏳ Pending                 │   │
│  │ [✅ Conferma] [❌ Rifiuta] [👁️ Info] │   │
│  └──────────────────────────────────────┘   │
│                                              │
│  ┌──────────────────────────────────────┐   │
│  │ #124 | Sara Bianchi                  │   │
│  │ 🎸 Chitarra | 10/02 10:30           │   │
│  │ 💶 Pacchetto (3/10) | ✅ Confermato │   │
│  │ [📧 Email] [🗑️ Cancella] [👁️ Info]   │   │
│  └──────────────────────────────────────┘   │
│                                              │
└──────────────────────────────────────────────┘
```

---

## 🔌 API Endpoints

### REST API: `api_prenotazioni.php`

```php
// GET - Lista prenotazioni
?action=list&stato=pending&socio_id=5

// GET - Dettaglio prenotazione
?action=get&id=123

// POST - Nuova prenotazione (da portale pubblico)
{
    "evento_id": 45,
    "socio": {
        "nome": "Marco",
        "cognome": "Rossi",
        "email": "marco@example.com"
    },
    "tipo_prenotazione": "singola",
    "note": "Prima lezione"
}

// PUT - Conferma prenotazione (admin)
?action=conferma&id=123
{
    "note_admin": "Confermato per lunedì"
}

// PUT - Rifiuta prenotazione (admin)
?action=rifiuta&id=123
{
    "motivo": "Posto non disponibile"
}

// PUT - Cancella prenotazione (utente)
?action=cancella&id=123
{
    "motivo": "Impegno improvviso"
}

// PUT - Registra pagamento
?action=pagamento&id=123
{
    "importo": 35.00,
    "metodo": "carta",
    "riferimento": "TXN_123456"
}
```

---

## 📧 Sistema Notifiche Email

### Template Email

#### 1. Conferma Prenotazione (Admin → Socio)
```
Oggetto: ✅ Prenotazione Confermata - Lezione Piano

Ciao Marco,

La tua prenotazione è stata CONFERMATA!

📅 Data: Lunedì 10 Febbraio 2026
🕐 Orario: 09:00 - 10:00
🎹 Materia: Piano
👨‍🏫 Docente: Prof. Rossi
🚪 Aula: Piano

💶 Importo: 35€
💳 Stato Pagamento: Da pagare

[Paga Ora] [Annulla Prenotazione]

A presto!
Team MusicAll
```

#### 2. Rifiuto Prenotazione
```
Oggetto: ❌ Prenotazione Non Disponibile

Ciao Marco,

Siamo spiacenti ma la lezione richiesta non è disponibile.

Motivo: Posto già occupato

Ti suggeriamo di:
- Scegliere un altro orario
- Iscriverti alla lista d'attesa

[Vedi Altri Orari] [Lista Attesa]
```

#### 3. Reminder Pre-Lezione (24h prima)
```
Oggetto: 🔔 Promemoria Lezione Domani

Ciao Marco,

Ti ricordiamo la tua lezione di domani:

📅 Martedì 11/02 alle 09:00
🎹 Piano con Prof. Rossi

Ci vediamo!
```

---

## 💳 Integrazione Pagamenti

### Gateway Supportati

1. **Stripe** (Consigliato)
   - Carte di credito/debito
   - Apple Pay / Google Pay
   - SEPA Direct Debit

2. **PayPal**
   - Pagamento PayPal
   - Carte tramite PayPal

3. **Bonifico Bancario**
   - Manuale con conferma admin

4. **Contanti**
   - Registrazione manuale

### Flow Pagamento Stripe

```javascript
// Frontend
const stripe = Stripe('pk_live_...');

async function pagaPrenotazione(prenotazioneId) {
    const response = await fetch('/api_pagamento.php', {
        method: 'POST',
        body: JSON.stringify({
            prenotazione_id: prenotazioneId,
            metodo: 'stripe'
        })
    });
    
    const {clientSecret} = await response.json();
    
    const {error} = await stripe.confirmPayment({
        clientSecret,
        confirmParams: {
            return_url: '/conferma_pagamento.php'
        }
    });
}
```

---

## 🔒 Regole Business

### 1. Limite Posti
- Evento ha `posti_disponibili` (default da aula)
- Count prenotazioni `confermate` < posti disponibili
- Excess → Lista d'attesa automatica

### 2. Cancellazione
- Utente può cancellare fino a 24h prima
- < 24h → Rimborso parziale o no-show

### 3. Pacchetti
- Validità temporale (es: 90 giorni)
- Numero lezioni prestabilito
- Sconto su prezzo singola

### 4. Priorità Conferma
1. Soci esistenti con storico
2. Pagamento anticipato
3. Ordine cronologico prenotazione

---

## 📊 Report e Analytics

### Dashboard Metrics
- Tasso occupazione aule (%)
- Revenue per materia
- Top docenti per prenotazioni
- Trend iscrizioni mensili
- Tasso cancellazione
- Conversion rate prenotazione → pagamento

---

## 🚀 Implementazione Fasi

### Fase 4.1 - Core (3-4 giorni)
- [x] Migration tabella prenotazioni
- [ ] Model `Prenotazione`
- [ ] API CRUD prenotazioni
- [ ] UI Admin gestione prenotazioni
- [ ] Workflow stati base

### Fase 4.2 - Portale Pubblico (3 giorni)
- [ ] Calendario pubblico eventi
- [ ] Modal prenotazione
- [ ] Form dati socio
- [ ] Conferma email base

### Fase 4.3 - Pagamenti (4-5 giorni)
- [ ] Integrazione Stripe
- [ ] Flow pagamento completo
- [ ] Webhook Stripe
- [ ] Registrazione pagamenti manuali

### Fase 4.4 - Advanced (2-3 giorni)
- [ ] Sistema pacchetti
- [ ] Lista d'attesa
- [ ] Email templates professionali
- [ ] Reminder automatici

---

## 💡 Future Enhancements

1. **App Mobile** - React Native per prenotazioni
2. **QR Code Check-in** - Scansione all'ingresso lezione
3. **Zoom Integration** - Lezioni online
4. **Referral Program** - Sconto porta un amico
5. **Membership Plans** - Abbonamenti mensili illimitati
6. **AI Suggestions** - Orari consigliati basati su storico

---

## 🎯 Metriche Successo

- **Tasso adozione**: 70% soci usano sistema prenotazioni
- **Riduzione no-show**: Da 15% a 5%
- **Tempo gestione**: Da 2h/giorno a 30min/giorno
- **Soddisfazione utenti**: >4.5/5 stelle
- **Revenue online**: 80% pagamenti via sistema

---

**Tempo implementazione totale stimato:** 12-15 giorni lavorativi

Vuoi che proceda con l'implementazione? Da quale fase preferisci partire?