-- ========================================
-- MIGRATION: Sistema Iscrizioni e Pagamenti
-- Data: 2026-02-12
-- Descrizione: Crea tabelle per gestione iscrizioni,
--              pagamenti mensili e quote associative
-- ========================================

-- ========================================
-- STEP 1: TABELLE TIPOLOGICHE
-- ========================================

-- Tipi di Pagamento
CREATE TABLE IF NOT EXISTS tipi_pagamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codice VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    richiede_mese BOOLEAN DEFAULT 0,
    richiede_iscrizione BOOLEAN DEFAULT 0,
    categoria_contabile VARCHAR(50),
    attivo BOOLEAN DEFAULT 1,
    ordinamento INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed tipi pagamento
INSERT OR IGNORE INTO tipi_pagamento (codice, nome, descrizione, richiede_mese, richiede_iscrizione, categoria_contabile, ordinamento) VALUES
('QUOTA_ASSOCIATIVA', 'Quota Associativa Annuale', 'Quota associativa per anno accademico', 0, 0, 'TASSA', 1),
('MENSILE', 'Quota Mensile Lezioni', 'Pagamento mensile per frequenza lezioni', 1, 1, 'RETTA', 2),
('EXTRA', 'Pagamento Extra', 'Pagamento non ricorrente (recuperi, lezioni extra)', 0, 1, 'RETTA', 3),
('MATERIALE', 'Materiale Didattico', 'Acquisto libri, spartiti, metronomi, etc', 0, 0, 'VENDITA', 4),
('SAGGIO', 'Partecipazione Saggio', 'Quota partecipazione a saggi/concerti', 0, 0, 'ALTRO', 5);

-- Metodi di Pagamento
CREATE TABLE IF NOT EXISTS metodi_pagamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codice VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    richiede_riferimento BOOLEAN DEFAULT 0,
    attivo BOOLEAN DEFAULT 1,
    ordinamento INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed metodi pagamento
INSERT OR IGNORE INTO metodi_pagamento (codice, nome, richiede_riferimento, ordinamento) VALUES
('CONTANTI', 'Contanti', 0, 1),
('BONIFICO', 'Bonifico Bancario', 1, 2),
('CARTA', 'Carta di Credito', 0, 3),
('POS', 'POS Bancomat', 0, 4),
('PAYPAL', 'PayPal', 1, 5),
('ASSEGNO', 'Assegno', 1, 6);

-- ========================================
-- STEP 2: CONFIGURAZIONE TARIFFE
-- ========================================

CREATE TABLE IF NOT EXISTS configurazione_tariffe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anno_accademico VARCHAR(20) NOT NULL,
    quota_associativa DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    tariffa_individuale DECIMAL(10,2) DEFAULT 120.00,
    tariffa_gruppo DECIMAL(10,2) DEFAULT 80.00,
    tariffa_lab DECIMAL(10,2) DEFAULT 60.00,
    note TEXT,
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(anno_accademico)
);

-- Seed configurazione anno corrente
INSERT OR IGNORE INTO configurazione_tariffe (anno_accademico, quota_associativa, tariffa_individuale, tariffa_gruppo, tariffa_lab) VALUES
('2025-2026', 50.00, 120.00, 80.00, 60.00);

-- Tariffe per materia specifica
CREATE TABLE IF NOT EXISTS tariffe_materie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anno_accademico VARCHAR(20) NOT NULL,
    materia_id INTEGER NOT NULL,
    tariffa_individuale DECIMAL(10,2),
    tariffa_gruppo DECIMAL(10,2),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    UNIQUE(anno_accademico, materia_id)
);

-- ========================================
-- STEP 3: ISCRIZIONI
-- ========================================

-- Drop vecchie tabelle se esistono
DROP TABLE IF EXISTS iscrizioni;
DROP TABLE IF EXISTS pagamenti;

CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    anno_accademico VARCHAR(20) NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    tipo_corso VARCHAR(50) NOT NULL,
    data_iscrizione DATE NOT NULL,
    data_inizio_corso DATE NOT NULL,
    data_fine_corso DATE,
    stato VARCHAR(20) DEFAULT 'attiva',
    creato_da INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id),
    FOREIGN KEY (creato_da) REFERENCES users(id),
    UNIQUE(allievo_id, materia_id, anno_accademico)
);

CREATE INDEX IF NOT EXISTS idx_iscrizioni_allievo ON iscrizioni(allievo_id);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_anno ON iscrizioni(anno_accademico);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_stato ON iscrizioni(stato);

-- ========================================
-- STEP 4: PAGAMENTI
-- ========================================

CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    iscrizione_id INTEGER,
    tipo_pagamento_id INTEGER NOT NULL,
    anno_accademico VARCHAR(20) NOT NULL,
    mese_riferimento VARCHAR(20),
    importo DECIMAL(10,2) NOT NULL,
    sconto DECIMAL(10,2) DEFAULT 0,
    importo_netto DECIMAL(10,2) NOT NULL,
    include_quota_associativa BOOLEAN DEFAULT 0,
    importo_quota_associativa DECIMAL(10,2) DEFAULT 0,
    metodo_pagamento_id INTEGER NOT NULL,
    data_pagamento DATE NOT NULL,
    data_scadenza DATE,
    numero_ricevuta VARCHAR(50),
    riferimento_transazione VARCHAR(100),
    note TEXT,
    stato VARCHAR(20) DEFAULT 'pagato',
    registrato_da INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id),
    FOREIGN KEY (tipo_pagamento_id) REFERENCES tipi_pagamento(id),
    FOREIGN KEY (metodo_pagamento_id) REFERENCES metodi_pagamento(id),
    FOREIGN KEY (registrato_da) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_pagamenti_allievo ON pagamenti(allievo_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_tipo ON pagamenti(tipo_pagamento_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_anno ON pagamenti(anno_accademico);
CREATE INDEX IF NOT EXISTS idx_pagamenti_mese ON pagamenti(mese_riferimento);
CREATE INDEX IF NOT EXISTS idx_pagamenti_data ON pagamenti(data_pagamento);
CREATE INDEX IF NOT EXISTS idx_pagamenti_metodo ON pagamenti(metodo_pagamento_id);

-- ========================================
-- STEP 5: VIEW UTILITIES
-- ========================================

-- Drop view se esiste già
DROP VIEW IF EXISTS v_pagamenti_dettagliati;

-- View pagamenti con dettagli
CREATE VIEW v_pagamenti_dettagliati AS
SELECT 
    p.*,
    tp.codice AS tipo_codice,
    tp.nome AS tipo_nome,
    tp.categoria_contabile,
    mp.codice AS metodo_codice,
    mp.nome AS metodo_nome,
    a.nome || ' ' || a.cognome AS allievo_nome,
    a.email AS allievo_email,
    a.telefono AS allievo_telefono,
    m.nome AS materia_nome,
    d.cognome || ' ' || d.nome AS docente_nome
FROM pagamenti p
LEFT JOIN tipi_pagamento tp ON p.tipo_pagamento_id = tp.id
LEFT JOIN metodi_pagamento mp ON p.metodo_pagamento_id = mp.id
LEFT JOIN allievi a ON p.allievo_id = a.id
LEFT JOIN iscrizioni i ON p.iscrizione_id = i.id
LEFT JOIN materie m ON i.materia_id = m.id
LEFT JOIN docenti d ON i.docente_id = d.id;

-- ========================================
-- MIGRATION COMPLETED
-- ========================================
