-- ============================================
-- MIGRATION: Sistema Gestione Iscrizioni
-- Data: 2026-02-11
-- ============================================

-- Tabella tipi corso
CREATE TABLE IF NOT EXISTS tipi_corso (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codice VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    durata_lezione_individuale INTEGER NOT NULL, -- 45 o 60 minuti
    include_laboratorio_teorico BOOLEAN DEFAULT 0,
    include_musica_insieme BOOLEAN DEFAULT 0,
    include_lezione_collettiva BOOLEAN DEFAULT 0,
    is_pacchetto BOOLEAN DEFAULT 0, -- TRUE per corsi custom
    num_lezioni_pacchetto INTEGER, -- 8 o 16 per custom
    prezzo_mensile DECIMAL(10,2),
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserisci tipi corso predefiniti
INSERT INTO tipi_corso (codice, nome, descrizione, durata_lezione_individuale, include_laboratorio_teorico, include_musica_insieme, include_lezione_collettiva, is_pacchetto, num_lezioni_pacchetto, prezzo_mensile) VALUES
('BASE_45', 'Corso Base 45min', 'Lezione individuale 45min + Laboratorio teorico 45min', 45, 1, 0, 0, 0, NULL, 80.00),
('BASE_60', 'Corso Base 60min', 'Lezione individuale 60min + Laboratorio teorico 45min', 60, 1, 0, 0, 0, NULL, 100.00),
('STANDARD_45', 'Corso Standard 45min', 'Lezione individuale 45min + Musica Insieme 60min', 45, 0, 1, 0, 0, NULL, 90.00),
('STANDARD_60', 'Corso Standard 60min', 'Lezione individuale 60min + Musica Insieme 60min', 60, 0, 1, 0, 0, NULL, 110.00),
('EXTENDED_45', 'Corso Extended 45min', 'Lezione individuale 45min + Laboratorio teorico 45min + Musica Insieme 60min', 45, 1, 1, 1, 0, NULL, 120.00),
('EXTENDED_60', 'Corso Extended 60min', 'Lezione individuale 60min + Laboratorio teorico 45min + Musica Insieme 60min', 60, 1, 1, 1, 0, NULL, 140.00),
('CUSTOM_8_45', 'Pacchetto Custom 8 lezioni 45min', 'Pacchetto 8 lezioni individuali da 45min', 45, 0, 0, 0, 1, 8, 200.00),
('CUSTOM_8_60', 'Pacchetto Custom 8 lezioni 60min', 'Pacchetto 8 lezioni individuali da 60min', 60, 0, 0, 0, 1, 8, 250.00),
('CUSTOM_16_45', 'Pacchetto Custom 16 lezioni 45min', 'Pacchetto 16 lezioni individuali da 45min', 45, 0, 0, 0, 1, 16, 380.00),
('CUSTOM_16_60', 'Pacchetto Custom 16 lezioni 60min', 'Pacchetto 16 lezioni individuali da 60min', 60, 0, 0, 0, 1, 16, 480.00);

-- Tabella iscrizioni
CREATE TABLE IF NOT EXISTS iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    tipo_corso_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    anno_scolastico VARCHAR(20) NOT NULL, -- es: 2025-2026
    data_iscrizione DATE NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE,
    stato VARCHAR(20) DEFAULT 'attiva', -- attiva, sospesa, conclusa, annullata
    
    -- Campi per corsi custom (pacchetti)
    lezioni_totali INTEGER, -- NULL per corsi regolari, 8/16 per custom
    lezioni_utilizzate INTEGER DEFAULT 0,
    
    -- Pagamento
    importo_totale DECIMAL(10,2),
    importo_pagato DECIMAL(10,2) DEFAULT 0,
    modalita_pagamento VARCHAR(50), -- mensile, trimestrale, annuale, unica_soluzione
    
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (tipo_corso_id) REFERENCES tipi_corso(id),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id)
);

-- Indici per performance
CREATE INDEX IF NOT EXISTS idx_iscrizioni_allievo ON iscrizioni(allievo_id, stato);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_anno ON iscrizioni(anno_scolastico, stato);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_docente ON iscrizioni(docente_id, stato);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_stato ON iscrizioni(stato, data_inizio);

-- Tabella storico utilizzo lezioni custom
CREATE TABLE IF NOT EXISTS utilizzo_lezioni_custom (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    iscrizione_id INTEGER NOT NULL,
    data_lezione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    docente_id INTEGER NOT NULL,
    aula_id INTEGER,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id),
    FOREIGN KEY (aula_id) REFERENCES aule(id)
);

CREATE INDEX IF NOT EXISTS idx_utilizzo_iscrizione ON utilizzo_lezioni_custom(iscrizione_id, data_lezione);

-- ============================================
-- VISTE UTILI
-- ============================================

-- Vista iscrizioni complete
CREATE VIEW IF NOT EXISTS v_iscrizioni_complete AS
SELECT 
    i.id,
    i.allievo_id,
    a.cognome || ' ' || a.nome as allievo,
    i.tipo_corso_id,
    tc.nome as tipo_corso,
    tc.codice as tipo_corso_codice,
    tc.is_pacchetto,
    i.materia_id,
    m.nome as materia,
    i.docente_id,
    d.cognome || ' ' || d.nome as docente,
    i.anno_scolastico,
    i.data_iscrizione,
    i.data_inizio,
    i.data_fine,
    i.stato,
    i.lezioni_totali,
    i.lezioni_utilizzate,
    CASE 
        WHEN tc.is_pacchetto = 1 THEN i.lezioni_totali - i.lezioni_utilizzate
        ELSE NULL
    END as lezioni_rimanenti,
    i.importo_totale,
    i.importo_pagato,
    i.importo_totale - i.importo_pagato as saldo_residuo,
    i.modalita_pagamento,
    i.note,
    i.created_at
FROM iscrizioni i
JOIN allievi a ON i.allievo_id = a.id
JOIN tipi_corso tc ON i.tipo_corso_id = tc.id
JOIN materie m ON i.materia_id = m.id
JOIN docenti d ON i.docente_id = d.id;

-- Vista statistiche iscrizioni
CREATE VIEW IF NOT EXISTS v_statistiche_iscrizioni AS
SELECT 
    anno_scolastico,
    stato,
    COUNT(*) as num_iscrizioni,
    SUM(importo_totale) as importo_totale,
    SUM(importo_pagato) as importo_pagato,
    SUM(importo_totale - importo_pagato) as saldo_residuo
FROM iscrizioni
GROUP BY anno_scolastico, stato;
