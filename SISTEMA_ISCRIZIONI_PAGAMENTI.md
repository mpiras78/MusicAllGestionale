# 💰 Sistema Iscrizioni e Pagamenti - Design Semplificato

## 📋 Overview

Sistema **interno** per gestione iscrizioni mensili e quote associative annuali. 
**NESSUN portale pubblico** - tutto gestito da segreteria/admin.

---

## 🎯 Workflow Reale

### Processo Standard
1. **Socio/Genitore contatta scuola** (telefono/email/persona)
2. **Segreteria registra iscrizione** nel sistema
3. **Sistema calcola importo**:
   - Quota mensile lezioni
   - + Quota associativa annuale (se prima iscrizione anno accademico)
4. **Socio paga** (contanti/bonifico/carta)
5. **Segreteria registra pagamento** nel sistema
6. **Sistema traccia stato pagamenti**

### Anno Accademico
- **Periodo**: Settembre → Giugno (10 mesi)
- **Quota associativa**: 1 volta/anno (es: 50€)
- **Quote mensili**: Ogni mese per lezioni frequentate

---

## 🗄️ Database Schema

### Tabella: `iscrizioni`

```sql
CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Socio
    socio_id INTEGER NOT NULL,
    
    -- Anno Accademico (es: '2025-2026')
    anno_accademico VARCHAR(20) NOT NULL,
    
    -- Corso/Lezioni
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    tipo_corso VARCHAR(50) NOT NULL, -- 'individuale', 'gruppo', 'lab'
    
    -- Date
    data_iscrizione DATE NOT NULL,
    data_inizio_corso DATE NOT NULL,
    data_fine_corso DATE, -- Se noto
    
    -- Stato
    stato VARCHAR(20) DEFAULT 'attiva', -- 'attiva', 'sospesa', 'conclusa'
    
    -- Quota Associativa
    quota_associativa_dovuta BOOLEAN DEFAULT 0,
    quota_associativa_pagata BOOLEAN DEFAULT 0,
    importo_quota_associativa DECIMAL(10,2) DEFAULT 0,
    
    -- Audit
    creato_da INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (socio_id) REFERENCES soci(id),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id),
    FOREIGN KEY (creato_da) REFERENCES users(id),
    
    -- Un socio può avere solo 1 iscrizione attiva per materia/anno
    UNIQUE(socio_id, materia_id, anno_accademico)
);

CREATE INDEX idx_iscrizioni_socio ON iscrizioni(socio_id);
CREATE INDEX idx_iscrizioni_anno ON iscrizioni(anno_accademico);
CREATE INDEX idx_iscrizioni_stato ON iscrizioni(stato);
```

### Tabella: `pagamenti`

```sql
CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Relazioni
    socio_id INTEGER NOT NULL,
    iscrizione_id INTEGER, -- NULL se quota associativa standalone
    
    -- Tipo Pagamento
    tipo VARCHAR(30) NOT NULL, -- 'mensile', 'quota_associativa', 'extra'
    
    -- Periodo (per mensili)
    anno_accademico VARCHAR(20) NOT NULL,
    mese_riferimento VARCHAR(20), -- 'settembre', 'ottobre', etc
    
    -- Importi
    importo DECIMAL(10,2) NOT NULL,
    sconto DECIMAL(10,2) DEFAULT 0,
    importo_netto DECIMAL(10,2) NOT NULL, -- importo - sconto
    
    -- Dettagli Pagamento
    metodo_pagamento VARCHAR(30) NOT NULL, -- 'contanti', 'bonifico', 'carta', 'pos'
    data_pagamento DATE NOT NULL,
    data_scadenza DATE,
    
    -- Riferimenti
    numero_ricevuta VARCHAR(50),
    riferimento_bonifico VARCHAR(100),
    note TEXT,
    
    -- Stato
    stato VARCHAR(20) DEFAULT 'pagato', -- 'pagato', 'parziale', 'in_attesa', 'annullato'
    
    -- Audit
    registrato_da INTEGER NOT NULL, -- User che ha registrato il pagamento
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (socio_id) REFERENCES soci(id),
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id),
    FOREIGN KEY (registrato_da) REFERENCES users(id)
);

CREATE INDEX idx_pagamenti_socio ON pagamenti(socio_id);
CREATE INDEX idx_pagamenti_tipo ON pagamenti(tipo);
CREATE INDEX idx_pagamenti_anno ON pagamenti(anno_accademico);
CREATE INDEX idx_pagamenti_mese ON pagamenti(mese_riferimento);
CREATE INDEX idx_pagamenti_data ON pagamenti(data_pagamento);
```

### Tabella: `configurazione_tariffe`

```sql
CREATE TABLE configurazione_tariffe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    anno_accademico VARCHAR(20) NOT NULL,
    
    -- Quota Associativa
    quota_associativa DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    
    -- Tariffe Lezioni (default, poi override per materia)
    tariffa_individuale DECIMAL(10,2) DEFAULT 120.00, -- al mese
    tariffa_gruppo DECIMAL(10,2) DEFAULT 80.00,
    tariffa_lab DECIMAL(10,2) DEFAULT 60.00,
    
    -- Note
    note TEXT,
    
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(anno_accademico)
);

-- Tariffe per materia specifica (override)
CREATE TABLE tariffe_materie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    anno_accademico VARCHAR(20) NOT NULL,
    materia_id INTEGER NOT NULL,
    
    tariffa_individuale DECIMAL(10,2),
    tariffa_gruppo DECIMAL(10,2),
    
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    UNIQUE(anno_accademico, materia_id)
);
```

---

## 🎨 UI Admin - Gestione Iscrizioni

### Pagina: `gestione_iscrizioni.php`

```
┌────────────────────────────────────────────────┐
│  📝 Gestione Iscrizioni                        │
├────────────────────────────────────────────────┤
│                                                │
│  [➕ Nuova Iscrizione]                         │
│                                                │
│  Filtri:                                       │
│  Anno: [2025-2026 ▼] Stato: [Tutte ▼]        │
│  [Cerca socio...]                           │
│                                                │
│  ┌──────────────────────────────────────────┐ │
│  │ Marco Rossi                               │ │
│  │ 🎹 Piano | Prof. Bianchi                 │ │
│  │ Anno: 2025-2026 | ✅ Attiva              │ │
│  │                                           │ │
│  │ 💰 Quota Associativa: ✅ Pagata (50€)    │ │
│  │ 📅 Pagamenti Mensili:                    │ │
│  │   • Settembre ✅ 120€                    │ │
│  │   • Ottobre ✅ 120€                      │ │
│  │   • Novembre ⏳ Non pagato               │ │
│  │                                           │ │
│  │ [💰 Registra Pagamento] [✏️ Modifica]    │ │
│  │ [📊 Storico] [🗑️ Sospendi]               │ │
│  └──────────────────────────────────────────┘ │
│                                                │
└────────────────────────────────────────────────┘
```

### Modal: Nuova Iscrizione

```
┌─────────────────────────────────────────┐
│  ➕ Nuova Iscrizione                    │
├─────────────────────────────────────────┤
│                                         │
│  Socio:                               │
│  [Seleziona socio ▼] o [➕ Nuovo]    │
│                                         │
│  Corso:                                 │
│  Materia: [Piano ▼]                    │
│  Docente: [Prof. Bianchi ▼]           │
│  Tipo: ● Individuale ○ Gruppo         │
│                                         │
│  Anno Accademico: [2025-2026 ▼]       │
│  Data Inizio: [__/__/____]             │
│                                         │
│  ─────────────────────────────────────  │
│  Calcolo Quota Prima Iscrizione:       │
│                                         │
│  ✅ Prima iscrizione anno accademico   │
│     Quota Associativa: 50€             │
│  📅 Quota Mensile: 120€                │
│                                         │
│  💰 TOTALE DA PAGARE: 170€             │
│  ─────────────────────────────────────  │
│                                         │
│  [Annulla]  [Salva Iscrizione]         │
└─────────────────────────────────────────┘
```

### Modal: Registra Pagamento

```
┌─────────────────────────────────────────┐
│  💰 Registra Pagamento                  │
├─────────────────────────────────────────┤
│                                         │
│  Socio: Marco Rossi                  │
│  Corso: Piano - Prof. Bianchi          │
│                                         │
│  Tipo Pagamento:                        │
│  ● Quota Mensile                       │
│  ○ Quota Associativa                   │
│  ○ Extra (recupero, materiale, etc)    │
│                                         │
│  Mese: [Novembre 2025 ▼]               │
│                                         │
│  Importo: [120.00] €                   │
│  Sconto: [0.00] €                      │
│  ─────────────────                      │
│  Netto: 120.00 €                       │
│                                         │
│  Metodo:                                │
│  ● Contanti  ○ Bonifico                │
│  ○ Carta     ○ POS                     │
│                                         │
│  Data Pagamento: [__/__/____]          │
│  N° Ricevuta: [___________]            │
│                                         │
│  Note: [____________________]          │
│                                         │
│  [Annulla]  [Registra Pagamento]       │
└─────────────────────────────────────────┘
```

---

## 💡 Funzionalità Chiave

### 1. Check Quota Associativa Automatico

```php
function verificaQuotaAssociativa($socioId, $annoAccademico) {
    // Check SPECIFICO se socio ha già pagato quota associativa quest'anno
    $pagamentoQuotaAssociativa = Pagamento::where('socio_id', $socioId)
        ->where('anno_accademico', $annoAccademico)
        ->where('tipo', 'quota_associativa') // IMPORTANTE: Solo questo tipo
        ->where('stato', 'pagato')
        ->first();
    
    // Ritorna true se deve pagare (nessun pagamento quota trovato)
    return !$pagamentoQuotaAssociativa;
}
```

### 2. Calcolo Importo Iscrizione

```php
function calcolaImportoIscrizione($socioId, $materiaId, $tipoCorso, $annoAccademico) {
    $importo = 0;
    
    // 1. Check SPECIFICO Quota Associativa
    // Cerca solo pagamenti tipo 'quota_associativa' per questo anno
    $haQuotaAssociativa = Pagamento::where('socio_id', $socioId)
        ->where('anno_accademico', $annoAccademico)
        ->where('tipo', 'quota_associativa')
        ->where('stato', 'pagato')
        ->exists();
    
    if (!$haQuotaAssociativa) {
        // Prima iscrizione anno accademico -> Aggiunge quota associativa
        $config = getConfigTariffe($annoAccademico);
        $importo += $config->quota_associativa;
    }
    
    // 2. Quota Mensile Prima Rata
    $tariffa = getTariffaMateria($materiaId, $tipoCorso, $annoAccademico);
    $importo += $tariffa;
    
    return [
        'importo_totale' => $importo,
        'include_quota_associativa' => !$haQuotaAssociativa,
        'quota_associativa' => !$haQuotaAssociativa ? $config->quota_associativa : 0,
        'quota_mensile' => $tariffa
    ];
}
```

### 3. Dashboard Scadenze

```
┌──────────────────────────────────────┐
│  ⚠️ Pagamenti in Scadenza           │
├──────────────────────────────────────┤
│                                      │
│  🔴 SCADUTI (5)                     │
│  • Marco Rossi - Novembre (120€)    │
│  • Sara Bianchi - Novembre (120€)   │
│                                      │
│  🟡 IN SCADENZA (12)                │
│  • Luca Verdi - Dicembre (80€)     │
│  • ...                              │
│                                      │
│  [Invia Solleciti Email]            │
└──────────────────────────────────────┘
```

---

## 📊 Report Essenziali

### 1. Report Incassi Mensili
```
Mese: Novembre 2025

Quote Associative:  5 × 50€  = 250€
Quote Mensili:     45 × 120€ = 5,400€
Extra:                        = 150€
                        ─────────────
TOTALE:                       5,800€

Metodi Pagamento:
• Contanti:  2,100€ (36%)
• Bonifico:  2,800€ (48%)
• Carta/POS:   900€ (16%)
```

### 2. Report Morosità
```
Soci con Pagamenti in Ritardo:

🔴 Oltre 30 giorni (3 soci)
🟡 15-30 giorni (7 soci)
🟢 Entro 15 giorni (12 soci)

Importo Totale Non Incassato: 2,640€
```

### 3. Report Anno Accademico
```
Anno 2025-2026

Soci Iscritti:      85
Quote Associate Pagate: 82/85 (96%)

Revenue Totale: 102,000€
• Quote Associative:  4,250€
• Lezioni Mensili:   97,750€

Media per Socio: 1,200€/anno
```

---

## 🚀 Implementazione Fasi

### Fase 1: Database (1 giorno)
- [x] Migration tabelle
- [ ] Seed configurazione tariffe
- [ ] Model Iscrizione
- [ ] Model Pagamento

### Fase 2: CRUD Iscrizioni (2 giorni)
- [ ] UI gestione iscrizioni
- [ ] Form nuova iscrizione
- [ ] Check quota associativa automatico
- [ ] Calcolo importi

### Fase 3: Gestione Pagamenti (2 giorni)
- [ ] UI registrazione pagamenti
- [ ] Ricevute/Fatture
- [ ] Dashboard scadenze
- [ ] Alert morosità

### Fase 4: Report (1 giorno)
- [ ] Report incassi
- [ ] Report morosità
- [ ] Export Excel
- [ ] Grafici revenue

**Totale: 6 giorni lavorativi**

---

## 📧 Email Automatiche (Opzionali)

1. **Conferma Iscrizione**
   - Benvenuto
   - Riepilogo corso
   - Dettagli pagamento

2. **Sollecito Pagamento**
   - 7 giorni prima scadenza
   - Giorno scadenza
   - 15 giorni dopo scadenza

3. **Ricevuta Pagamento**
   - Conferma pagamento ricevuto
   - Dettagli (importo, metodo, ricevuta)

---

**Tempo implementazione: 6-8 giorni**

Molto più semplice del sistema prenotazioni! Vuoi che inizi?