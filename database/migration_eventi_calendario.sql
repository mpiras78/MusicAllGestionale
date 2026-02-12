-- ============================================
-- MIGRATION: Sistema Eventi Calendario & Pagamenti
-- Versione: 1.0.0
-- Data: 2026-02-11
-- ============================================

-- STEP 1: Crea tabella tipologie_evento
CREATE TABLE IF NOT EXISTS tipologie_evento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria TEXT NOT NULL CHECK(categoria IN ('lezione', 'prenotazione')),
    codice TEXT UNIQUE NOT NULL,
    nome TEXT NOT NULL,
    descrizione TEXT,
    colore_bg TEXT DEFAULT '#ffffff',
    colore_border TEXT DEFAULT '#000000',
    icona TEXT,
    attiva INTEGER DEFAULT 1,
    ordine_visualizzazione INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_tipologie_categoria ON tipologie_evento(categoria, attiva);
CREATE INDEX IF NOT EXISTS idx_tipologie_codice ON tipologie_evento(codice);

-- Inserisci tipologie iniziali
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

-- STEP 2: Crea tabella soci_occasionali
CREATE TABLE IF NOT EXISTS soci_occasionali (
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

CREATE INDEX IF NOT EXISTS idx_soci_occasionali_email ON soci_occasionali(email);
CREATE INDEX IF NOT EXISTS idx_soci_occasionali_telefono ON soci_occasionali(telefono);
CREATE INDEX IF NOT EXISTS idx_soci_occasionali_nome ON soci_occasionali(cognome, nome);

-- STEP 3: Crea tabella iscrizioni
CREATE TABLE IF NOT EXISTS iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    mese INTEGER NOT NULL CHECK(mese BETWEEN 1 AND 12),
    anno INTEGER NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    stato TEXT DEFAULT 'attiva' CHECK(stato IN ('attiva', 'sospesa', 'conclusa')),
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE(allievo_id, mese, anno)
);

CREATE INDEX IF NOT EXISTS idx_iscrizioni_allievo ON iscrizioni(allievo_id);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_periodo ON iscrizioni(anno, mese);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_stato ON iscrizioni(stato);

-- STEP 4: Crea tabella eventi_calendario
CREATE TABLE IF NOT EXISTS eventi_calendario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Tipologia
    tipologia_id INTEGER NOT NULL,
    
    -- Periodicità
    ricorrente INTEGER DEFAULT 0,
    giorno_settimana TEXT CHECK(giorno_settimana IN (
        'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'
    )),
    
    -- Date
    data_evento DATE,
    data_inizio DATE,
    data_fine DATE,
    
    -- Orario
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    
    -- Risorse
    aula_id INTEGER NOT NULL,
    docente_id INTEGER,
    materia_id INTEGER,
    
    -- Partecipanti
    allievo_id INTEGER,
    socio_occasionale_id INTEGER,
    
    -- Relazione iscrizione
    iscrizione_id INTEGER,
    
    -- Metadata
    titolo TEXT,
    descrizione TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    confermato INTEGER DEFAULT 1,
    
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
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_occasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    CHECK (
        (ricorrente = 0 AND data_evento IS NOT NULL AND giorno_settimana IS NULL) OR
        (ricorrente = 1 AND data_evento IS NULL AND giorno_settimana IS NOT NULL)
    ),
    CHECK (
        (allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NULL)
    )
);

CREATE INDEX IF NOT EXISTS idx_eventi_tipologia ON eventi_calendario(tipologia_id, attivo);
CREATE INDEX IF NOT EXISTS idx_eventi_data ON eventi_calendario(data_evento);
CREATE INDEX IF NOT EXISTS idx_eventi_giorno ON eventi_calendario(giorno_settimana, ricorrente);
CREATE INDEX IF NOT EXISTS idx_eventi_aula ON eventi_calendario(aula_id);
CREATE INDEX IF NOT EXISTS idx_eventi_docente ON eventi_calendario(docente_id);
CREATE INDEX IF NOT EXISTS idx_eventi_allievo ON eventi_calendario(allievo_id);
CREATE INDEX IF NOT EXISTS idx_eventi_socio ON eventi_calendario(socio_occasionale_id);
CREATE INDEX IF NOT EXISTS idx_eventi_iscrizione ON eventi_calendario(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_eventi_periodo ON eventi_calendario(data_inizio, data_fine);

-- STEP 5: Crea tabella listini_prezzi
CREATE TABLE IF NOT EXISTS listini_prezzi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipologia_id INTEGER NOT NULL,
    
    destinatario TEXT NOT NULL CHECK(destinatario IN (
        'allievo_iscritto',
        'docente',
        'socio_occasionale',
        'altro'
    )),
    
    prezzo_orario REAL,
    prezzo_forfait REAL,
    
    data_inizio_validita DATE NOT NULL,
    data_fine_validita DATE,
    
    attivo INTEGER DEFAULT 1,
    note TEXT,
    
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_listini_tipologia ON listini_prezzi(tipologia_id, attivo);
CREATE INDEX IF NOT EXISTS idx_listini_destinatario ON listini_prezzi(destinatario);
CREATE INDEX IF NOT EXISTS idx_listini_validita ON listini_prezzi(data_inizio_validita, data_fine_validita);

-- Inserisci listini iniziali
INSERT INTO listini_prezzi (tipologia_id, destinatario, prezzo_orario, data_inizio_validita) VALUES
((SELECT id FROM tipologie_evento WHERE codice='PREN_SALA_ALLIEVI'), 'allievo_iscritto', 0.00, '2026-01-01'),
((SELECT id FROM tipologie_evento WHERE codice='PREN_DOCENTE'), 'docente', 15.00, '2026-01-01'),
((SELECT id FROM tipologie_evento WHERE codice='PREN_ESTERNO'), 'socio_occasionale', 30.00, '2026-01-01');

-- STEP 6: Crea tabella pagamenti
CREATE TABLE IF NOT EXISTS pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    tipo TEXT NOT NULL CHECK(tipo IN ('iscrizione_mensile', 'prenotazione_sala', 'altro')),
    
    iscrizione_id INTEGER,
    evento_id INTEGER,
    allievo_id INTEGER,
    socio_occasionale_id INTEGER,
    
    importo_totale REAL NOT NULL,
    importo_pagato REAL DEFAULT 0,
    importo_residuo REAL,
    
    stato TEXT DEFAULT 'da_pagare' CHECK(stato IN (
        'da_pagare', 'parzialmente_pagato', 'pagato', 'annullato'
    )),
    
    data_emissione DATE NOT NULL DEFAULT (date('now')),
    data_scadenza DATE,
    data_pagamento DATE,
    
    modalita_pagamento TEXT CHECK(modalita_pagamento IN (
        'contanti', 'bonifico', 'carta', 'paypal', 'altro'
    )),
    
    numero_fattura TEXT,
    numero_ricevuta TEXT,
    
    note TEXT,
    
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    updated_at DATETIME,
    
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE SET NULL,
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_occasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    CHECK (
        iscrizione_id IS NOT NULL OR evento_id IS NOT NULL
    ),
    CHECK (
        (allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR
        (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL)
    )
);

CREATE INDEX IF NOT EXISTS idx_pagamenti_tipo ON pagamenti(tipo, stato);
CREATE INDEX IF NOT EXISTS idx_pagamenti_iscrizione ON pagamenti(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_evento ON pagamenti(evento_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_allievo ON pagamenti(allievo_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_socio ON pagamenti(socio_occasionale_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_scadenza ON pagamenti(data_scadenza, stato);

-- STEP 7: Crea tabella iscrizioni_dettagli
CREATE TABLE IF NOT EXISTS iscrizioni_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    iscrizione_id INTEGER NOT NULL,
    evento_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    
    costo_mensile REAL,
    
    note TEXT,
    
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    
    UNIQUE(iscrizione_id, evento_id)
);

CREATE INDEX IF NOT EXISTS idx_iscrizioni_dettagli_iscrizione ON iscrizioni_dettagli(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_dettagli_evento ON iscrizioni_dettagli(evento_id);

-- STEP 8: Crea VIEW calendario unificato
CREATE VIEW IF NOT EXISTS v_calendario_unificato AS
SELECT 
    e.id,
    e.ricorrente,
    e.giorno_settimana,
    e.data_evento,
    e.data_inizio,
    e.data_fine,
    e.ora_inizio,
    e.ora_fine,
    
    t.categoria as tipo_evento,
    t.codice as tipologia_codice,
    t.nome as tipologia_nome,
    t.colore_bg,
    t.colore_border,
    t.icona,
    
    a.nome as aula,
    a.id as aula_id,
    
    d.cognome || ' ' || d.nome as docente,
    d.id as docente_id,
    
    COALESCE(
        al.cognome || ' ' || al.nome,
        so.cognome || ' ' || so.nome
    ) as partecipante,
    
    CASE 
        WHEN e.allievo_id IS NOT NULL THEN 'allievo'
        WHEN e.socio_occasionale_id IS NOT NULL THEN 'esterno'
        ELSE NULL
    END as tipo_partecipante,
    
    e.allievo_id,
    e.socio_occasionale_id,
    
    m.nome as materia,
    m.id as materia_id,
    
    e.confermato,
    e.attivo,
    
    i.id as iscrizione_id,
    i.mese as iscrizione_mese,
    i.anno as iscrizione_anno,
    
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

-- STEP 9: Migra lezioni esistenti a eventi_calendario
-- Ottieni ID tipologia LEZ_REGOLARE
INSERT INTO eventi_calendario (
    tipologia_id,
    ricorrente,
    giorno_settimana,
    ora_inizio,
    ora_fine,
    aula_id,
    docente_id,
    materia_id,
    allievo_id,
    data_inizio,
    data_fine,
    attivo,
    note,
    created_at
)
SELECT 
    (SELECT id FROM tipologie_evento WHERE codice='LEZ_REGOLARE'),
    1,
    l.giorno_settimana,
    l.ora_inizio,
    l.ora_fine,
    l.aula_id,
    l.docente_id,
    l.materia_id,
    l.allievo_id,
    COALESCE(l.data_inizio, date('now', 'start of month')),
    l.data_fine,
    l.attiva,
    l.note,
    l.created_at
FROM lezioni l
WHERE l.tipo = 'regolare';

-- ============================================
-- FINE MIGRATION
-- ============================================

SELECT 'Migration completata con successo!' as message;