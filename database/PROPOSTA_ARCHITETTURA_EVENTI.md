# 🏗️ ARCHITETTURA COMPLETA: EVENTI CALENDARIO & PAGAMENTI

## 📊 STRUTTURA DATABASE NORMALIZZATA

### **1. Tabella Tipologie Evento (Lookup)**
```sql
CREATE TABLE tipologie_evento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria TEXT NOT NULL CHECK(categoria IN ('lezione', 'prenotazione')),
    codice TEXT UNIQUE NOT NULL,  -- es: 'LEZ_REGOLARE', 'PREN_SALA_ALLIEVI'
    nome TEXT NOT NULL,
    descrizione TEXT,
    colore_bg TEXT DEFAULT '#ffffff',
    colore_border TEXT DEFAULT '#000000',
    icona TEXT,  -- Bootstrap icon class
    attiva INTEGER DEFAULT 1,
    ordine_visualizzazione INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT (datetime('now','localtime'))
);

-- Indice per performance
CREATE INDEX idx_tipologie_categoria ON tipologie_evento(categoria, attiva);
CREATE INDEX idx_tipologie_codice ON tipologie_evento(codice);

-- Dati iniziali
INSERT INTO tipologie_evento (categoria, codice, nome, descrizione, colore_bg, colore_border, icona, ordine_visualizzazione) VALUES
-- LEZIONI
('lezione', 'LEZ_REGOLARE', 'Lezione Regolare', 'Lezione settimanale ricorrente', '#fff5f0', '#ff6b35', 'bi-music-note-beamed', 1),
('lezione', 'LEZ_CUSTOM', 'Lezione Custom', 'Lezione una tantum', '#e3f2fd', '#2196f3', 'bi-star', 2),
('lezione', 'LEZ_LABORATORIO', 'Laboratorio', 'Musica d''insieme/laboratorio', '#f3e5f5', '#9c27b0', 'bi-people', 3),
('lezione', 'LEZ_RECUPERO', 'Recupero', 'Lezione di recupero', '#e8f5e9', '#4caf50', 'bi-arrow-repeat', 4),

-- PRENOTAZIONI
('prenotazione', 'PREN_SALA_ALLIEVI', 'Prenotazione Sala Allievi', 'Prenotazione sala per allievi iscritti (gratuita)', '#e8f5e9', '#4caf50', 'bi-door-open', 11),
('prenotazione', 'PREN_DOCENTE', 'Prenotazione Docente', 'Prenotazione sala da parte di docenti', '#fff9c4', '#fdd835', 'bi-person-badge', 12),
('prenotazione', 'PREN_ESTERNO', 'Prenotazione Esterno', 'Prenotazione sala da soci occasionali/esterni', '#ffebee', '#ef5350', 'bi-calendar-event', 13);
```

---

### **2. Tabella Soci Occasionali/Esterni**
```sql
CREATE TABLE soci_occasionali (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cognome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    tipo TEXT DEFAULT 'privato' CHECK(tipo IN ('privato', 'band', 'associazione', 'altro')),
    partita_iva TEXT,
    codice_fiscale TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME
);

CREATE INDEX idx_soci_occasionali_email ON soci_occasionali(email);
CREATE INDEX idx_soci_occasionali_telefono ON soci_occasionali(telefono);
CREATE INDEX idx_soci_occasionali_nome ON soci_ocasionali(cognome, nome);
```

---

### **3. Tabella Iscrizioni (Mensilità)**
```sql
CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    mese INTEGER NOT NULL CHECK(mese BETWEEN 1 AND 12),
    anno INTEGER NOT NULL,
    data_inizio DATE NOT NULL,  -- Primo giorno del mese
    data_fine DATE NOT NULL,    -- Ultimo giorno del mese
    stato TEXT DEFAULT 'attiva' CHECK(stato IN ('attiva', 'sospesa', 'conclusa')),
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE(allievo_id, mese, anno)
);

CREATE INDEX idx_iscrizioni_allievo ON iscrizioni(allievo_id);
CREATE INDEX idx_iscrizioni_periodo ON iscrizioni(anno, mese);
CREATE INDEX idx_iscrizioni_stato ON iscrizioni(stato);
```

---

### **4. Tabella Eventi Calendario (Rinominata e Migliorata)**
```sql
CREATE TABLE eventi_calendario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Tipologia (FK a lookup table)
    tipologia_id INTEGER NOT NULL,
    
    -- Periodicità
    ricorrente INTEGER DEFAULT 0,
    giorno_settimana TEXT CHECK(giorno_settimana IN (
        'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'
    )),
    
    -- Date (eventi singoli o range validità per ricorrenti)
    data_evento DATE,              -- Per eventi singoli
    data_inizio DATE,              -- Per eventi ricorrenti (inizio validità)
    data_fine DATE,                -- Per eventi ricorrenti (fine validità)
    
    -- Orario
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    
    -- Risorse
    aula_id INTEGER NOT NULL,
    docente_id INTEGER,
    materia_id INTEGER,
    
    -- Partecipanti (mutually exclusive)
    allievo_id INTEGER,            -- Per lezioni allievi
    socio_occasionale_id INTEGER,  -- Per prenotazioni esterni
    
    -- Relazione con iscrizione (per lezioni ricorrenti)
    iscrizione_id INTEGER,
    
    -- Metadata
    titolo TEXT,
    descrizione TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    confermato INTEGER DEFAULT 1,  -- 0 per prenotazioni esterni da confermare
    
    -- Auditing
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    updated_at DATETIME,
    updated_by INTEGER,
    
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE SET NULL,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_ocasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Constraints
    CHECK (
        -- Eventi singoli: deve esserci data_evento
        (ricorrente = 0 AND data_evento IS NOT NULL AND giorno_settimana IS NULL) OR
        -- Eventi ricorrenti: deve esserci giorno_settimana
        (ricorrente = 1 AND data_evento IS NULL AND giorno_settimana IS NOT NULL)
    ),
    CHECK (
        -- Solo uno tra allievo_id e socio_occasionale_id può essere valorizzato
        (allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NULL)
    )
);

-- Indici
CREATE INDEX idx_eventi_tipologia ON eventi_calendario(tipologia_id, attivo);
CREATE INDEX idx_eventi_data ON eventi_calendario(data_evento);
CREATE INDEX idx_eventi_giorno ON eventi_calendario(giorno_settimana, ricorrente);
CREATE INDEX idx_eventi_aula ON eventi_calendario(aula_id);
CREATE INDEX idx_eventi_docente ON eventi_calendario(docente_id);
CREATE INDEX idx_eventi_allievo ON eventi_calendario(allievo_id);
CREATE INDEX idx_eventi_socio ON eventi_calendario(socio_occasionale_id);
CREATE INDEX idx_eventi_iscrizione ON eventi_calendario(iscrizione_id);
CREATE INDEX idx_eventi_periodo ON eventi_calendario(data_inizio, data_fine);
```

---

### **5. Tabella Listini Prezzi**
```sql
CREATE TABLE listini_prezzi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipologia_id INTEGER NOT NULL,
    
    -- Destinatario
    destinatario TEXT NOT NULL CHECK(destinatario IN (
        'allievo_iscritto',  -- Allievi con iscrizione attiva
        'docente',           -- Docenti interni
        'socio_occasionale', -- Esterni/soci occasionali
        'altro'
    )),
    
    -- Prezzi
    prezzo_orario REAL,
    prezzo_forfait REAL,  -- Per pacchetti lezioni
    
    -- Validità
    data_inizio_validita DATE NOT NULL,
    data_fine_validita DATE,
    
    attivo INTEGER DEFAULT 1,
    note TEXT,
    
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE CASCADE
);

CREATE INDEX idx_listini_tipologia ON listini_prezzi(tipologia_id, attivo);
CREATE INDEX idx_listini_destinatario ON listini_prezzi(destinatario);
CREATE INDEX idx_listini_validita ON listini_prezzi(data_inizio_validita, data_fine_validita);

-- Dati iniziali
INSERT INTO listini_prezzi (tipologia_id, destinatario, prezzo_orario, data_inizio_validita) VALUES
-- Prenotazioni sale allievi: GRATUITO
((SELECT id FROM tipologie_evento WHERE codice='PREN_SALA_ALLIEVI'), 'allievo_iscritto', 0.00, '2026-01-01'),

-- Prenotazioni docenti: €15/ora
((SELECT id FROM tipologie_evento WHERE codice='PREN_DOCENTE'), 'docente', 15.00, '2026-01-01'),

-- Prenotazioni esterni: €30/ora
((SELECT id FROM tipologie_evento WHERE codice='PREN_ESTERNO'), 'socio_occasionale', 30.00, '2026-01-01');
```

---

### **6. Tabella Pagamenti**
```sql
CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Tipo pagamento
    tipo TEXT NOT NULL CHECK(tipo IN ('iscrizione_mensile', 'prenotazione_sala', 'altro')),
    
    -- Riferimenti
    iscrizione_id INTEGER,         -- Per iscrizioni mensili
    evento_id INTEGER,             -- Per prenotazioni singole
    allievo_id INTEGER,
    socio_occasionale_id INTEGER,
    
    -- Importi
    importo_totale REAL NOT NULL,
    importo_pagato REAL DEFAULT 0,
    importo_residuo REAL,
    
    -- Stati
    stato TEXT DEFAULT 'da_pagare' CHECK(stato IN (
        'da_pagare', 'parzialmente_pagato', 'pagato', 'annullato'
    )),
    
    -- Date
    data_emissione DATE NOT NULL DEFAULT (date('now')),
    data_scadenza DATE,
    data_pagamento DATE,
    
    -- Modalità pagamento
    modalita_pagamento TEXT CHECK(modalita_pagamento IN (
        'contanti', 'bonifico', 'carta', 'paypal', 'altro'
    )),
    
    -- Riferimenti esterni
    numero_fattura TEXT,
    numero_ricevuta TEXT,
    
    note TEXT,
    
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    updated_at DATETIME,
    
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE SET NULL,
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_ocasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    CHECK (
        -- Deve esserci almeno un riferimento
        iscrizione_id IS NOT NULL OR evento_id IS NOT NULL
    ),
    CHECK (
        -- Solo uno tra allievo e socio occasionale
        (allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL)
    )
);

CREATE INDEX idx_pagamenti_tipo ON pagamenti(tipo, stato);
CREATE INDEX idx_pagamenti_iscrizione ON pagamenti(iscrizione_id);
CREATE INDEX idx_pagamenti_evento ON pagamenti(evento_id);
CREATE INDEX idx_pagamenti_allievo ON pagamenti(allievo_id);
CREATE INDEX idx_pagamenti_socio ON pagamenti(socio_ocasionale_id);
CREATE INDEX idx_pagamenti_scadenza ON pagamenti(data_scadenza, stato);
```

---

### **7. Tabella Dettagli Iscrizione (Corsi compresi nell'iscrizione)**
```sql
CREATE TABLE iscrizioni_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    iscrizione_id INTEGER NOT NULL,
    evento_id INTEGER NOT NULL,  -- Riferimento all'evento/lezione ricorrente
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    
    -- Costo specifico (può essere sovrascritto rispetto al listino)
    costo_mensile REAL,
    
    note TEXT,
    
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    
    UNIQUE(iscrizione_id, evento_id)
);

CREATE INDEX idx_iscrizioni_dettagli_iscrizione ON iscrizioni_dettagli(iscrizione_id);
CREATE INDEX idx_iscrizioni_dettagli_evento ON iscrizioni_dettagli(evento_id);
```

---

## 🎯 ESEMPI PRATICI

### **Scenario 1: Allievo iscritto a 2 corsi per Febbraio 2026**

```sql
-- 1. Creo iscrizione mensile
INSERT INTO iscrizioni (allievo_id, mese, anno, data_inizio, data_fine, stato) 
VALUES (25, 2, 2026, '2026-02-01', '2026-02-28', 'attiva');

SET @iscrizione_id = last_insert_rowid();

-- 2. Creo evento ricorrente: Chitarra ogni Lunedì
INSERT INTO eventi_calendario (
    tipologia_id, ricorrente, giorno_settimana,
    data_inizio, data_fine, ora_inizio, ora_fine,
    aula_id, docente_id, materia_id, allievo_id,
    iscrizione_id
) VALUES (
    (SELECT id FROM tipologie_evento WHERE codice='LEZ_REGOLARE'),
    1, 'lunedi',
    '2026-02-01', '2026-02-28', '10:00:00', '10:45:00',
    2, 5, 1, 25,
    @iscrizione_id
);

SET @evento_chitarra_id = last_insert_rowid();

-- 3. Creo evento ricorrente: Canto ogni Mercoledì
INSERT INTO eventi_calendario (
    tipologia_id, ricorrente, giorno_settimana,
    data_inizio, data_fine, ora_inizio, ora_fine,
    aula_id, docente_id, materia_id, allievo_id,
    iscrizione_id
) VALUES (
    (SELECT id FROM tipologie_evento WHERE codice='LEZ_REGOLARE'),
    1, 'mercoledi',
    '2026-02-01', '2026-02-28', '15:00:00', '15:45:00',
    3, 8, 7, 25,
    @iscrizione_id
);

SET @evento_canto_id = last_insert_rowid();

-- 4. Registro dettagli iscrizione
INSERT INTO iscrizioni_dettagli (iscrizione_id, evento_id, materia_id, docente_id, costo_mensile)
VALUES 
    (@iscrizione_id, @evento_chitarra_id, 1, 5, 80.00),
    (@iscrizione_id, @evento_canto_id, 7, 8, 90.00);

-- 5. Genero pagamento mensile (totale 170€)
INSERT INTO pagamenti (
    tipo, iscrizione_id, allievo_id,
    importo_totale, importo_residuo,
    stato, data_emissione, data_scadenza
) VALUES (
    'iscrizione_mensile', @iscrizione_id, 25,
    170.00, 170.00,
    'da_pagare', '2026-02-01', '2026-02-10'
);
```

### **Scenario 2: Prenotazione Sala da Socio Esterno**

```sql
-- 1. Registra socio occasionale (se non esiste)
INSERT INTO soci_occasionali (nome, cognome, email, telefono, tipo)
VALUES ('The', 'Band XYZ', 'band@example.com', '333-1234567', 'band');

SET @socio_id = last_insert_rowid();

-- 2. Crea prenotazione singola
INSERT INTO eventi_calendario (
    tipologia_id, ricorrente, data_evento,
    ora_inizio, ora_fine, aula_id,
    socio_occasionale_id, titolo, confermato
) VALUES (
    (SELECT id FROM tipologie_evento WHERE codice='PREN_ESTERNO'),
    0, '2026-02-20',
    '19:00:00', '22:00:00', 5,
    @socio_id, 'Prova serale Band XYZ', 0  -- Da confermare
);

SET @evento_id = last_insert_rowid();

-- 3. Calcola costo (3 ore * €30/ora = €90)
INSERT INTO pagamenti (
    tipo, evento_id, socio_occasionale_id,
    importo_totale, importo_residuo,
    stato, data_emissione
) VALUES (
    'prenotazione_sala', @evento_id, @socio_id,
    90.00, 90.00,
    'da_pagare', '2026-02-15'
);
```

---

## 📊 VIEW RIEPILOGATIVA

```sql
CREATE VIEW v_calendario_unificato AS
SELECT 
    e.id,
    e.ricorrente,
    e.giorno_settimana,
    e.data_evento,
    e.data_inizio,
    e.data_fine,
    e.ora_inizio,
    e.ora_fine,
    
    -- Tipologia
    t.categoria as tipo_evento,
    t.codice as tipologia_codice,
    t.nome as tipologia_nome,
    t.colore_bg,
    t.colore_border,
    t.icona,
    
    -- Aula
    a.nome as aula,
    
    -- Docente
    d.cognome || ' ' || d.nome as docente,
    
    -- Partecipante (allievo O socio esterno)
    COALESCE(
        al.cognome || ' ' || al.nome,
        so.cognome || ' ' || so.nome
    ) as partecipante,
    
    CASE 
        WHEN e.allievo_id IS NOT NULL THEN 'allievo'
        WHEN e.socio_occasionale_id IS NOT NULL THEN 'esterno'
        ELSE NULL
    END as tipo_partecipante,
    
    -- Materia
    m.nome as materia,
    
    -- Status
    e.confermato,
    e.attivo,
    
    -- Iscrizione
    i.id as iscrizione_id,
    i.mese as iscrizione_mese,
    i.anno as iscrizione_anno,
    
    -- Pagamento
    p.id as pagamento_id,
    p.stato as stato_pagamento,
    p.importo_totale,
    p.importo_pagato
    
FROM eventi_calendario e
INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
LEFT JOIN aule a ON e.aula_id = a.id
LEFT JOIN docenti d ON e.docente_id = d.id
LEFT JOIN allievi al ON e.allievo_id = al.id
LEFT JOIN soci_occasionali so ON e.socio_occasionale_id = so.id
LEFT JOIN materie m ON e.materia_id = m.id
LEFT JOIN iscrizioni i ON e.iscrizione_id = i.id
LEFT JOIN pagamenti p ON (p.evento_id = e.id OR p.iscrizione_id = e.iscrizione_id)
WHERE e.attivo = 1;
```

---

## ✅ VANTAGGI ARCHITETTURA

1. ✅ **Tipologie normalizzate** → Facile aggiungere/modificare tipi
2. ✅ **Soci occasionali tracciati** → Riuso dati, statistiche accurate
3. ✅ **Iscrizioni mensili** → Gestione chiara periodo + pagamenti
4. ✅ **Pagamenti centralizzati** → Unica tabella per tutti i pagamenti
5. ✅ **Listini flessibili** → Prezzi diversi per categoria destinatario
6. ✅ **Report statistici** → Query JOIN su tabelle normalizzate
7. ✅ **Audit completo** → Tracciamento created_by/updated_by

Procedo con la creazione del file migration SQL completo?