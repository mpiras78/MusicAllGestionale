-- =====================================================
-- MIGRATION: Sistema Configurazione Corsi e Laboratori (SQLite)
-- =====================================================

-- 1. Tabella Tipi Corso (Configurazione)
CREATE TABLE IF NOT EXISTS tipi_corso_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    durata_lezione INTEGER NOT NULL,
    costo_mensile DECIMAL(10,2) NOT NULL,
    include_laboratorio INTEGER DEFAULT 0,
    tipo_laboratorio_id INTEGER,
    descrizione TEXT,
    attivo INTEGER DEFAULT 1,
    ordine_visualizzazione INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_tipi_corso_attivo ON tipi_corso_config(attivo);
CREATE INDEX IF NOT EXISTS idx_tipi_corso_ordine ON tipi_corso_config(ordine_visualizzazione);

-- 2. Tabella Tipi Laboratorio (Configurazione)
CREATE TABLE IF NOT EXISTS tipi_laboratorio (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    docente_id INTEGER,
    giorno_settimana INTEGER NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INTEGER,
    max_partecipanti INTEGER,
    attivo INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE SET NULL,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_tipi_lab_giorno ON tipi_laboratorio(giorno_settimana);
CREATE INDEX IF NOT EXISTS idx_tipi_lab_docente ON tipi_laboratorio(docente_id);
CREATE INDEX IF NOT EXISTS idx_tipi_lab_attivo ON tipi_laboratorio(attivo);

-- 3. Tabella Pagamenti Mensili
CREATE TABLE IF NOT EXISTS pagamenti_mensili (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    iscrizione_id INTEGER NOT NULL,
    mese_riferimento CHAR(7) NOT NULL,
    importo DECIMAL(10,2) NOT NULL,
    data_pagamento DATE,
    stato VARCHAR(20) DEFAULT 'non_pagato',
    metodo_pagamento VARCHAR(20),
    lezioni_generate INTEGER DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS uk_iscrizione_mese ON pagamenti_mensili(iscrizione_id, mese_riferimento);
CREATE INDEX IF NOT EXISTS idx_pag_mese ON pagamenti_mensili(mese_riferimento);
CREATE INDEX IF NOT EXISTS idx_pag_stato ON pagamenti_mensili(stato);
CREATE INDEX IF NOT EXISTS idx_pag_data ON pagamenti_mensili(data_pagamento);

-- 4. Tabella Partecipanti Laboratorio
CREATE TABLE IF NOT EXISTS laboratorio_partecipanti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo_laboratorio_id INTEGER NOT NULL,
    allievo_id INTEGER NOT NULL,
    data_iscrizione DATE NOT NULL,
    attivo INTEGER DEFAULT 1,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_laboratorio_id) REFERENCES tipi_laboratorio(id) ON DELETE CASCADE,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS uk_lab_allievo ON laboratorio_partecipanti(tipo_laboratorio_id, allievo_id);
CREATE INDEX IF NOT EXISTS idx_lab_part_allievo ON laboratorio_partecipanti(allievo_id);
CREATE INDEX IF NOT EXISTS idx_lab_part_attivo ON laboratorio_partecipanti(attivo);

-- 5. Dati iniziali Tipi Laboratorio
INSERT OR IGNORE INTO tipi_laboratorio (id, nome, descrizione, giorno_settimana, ora_inizio, ora_fine, attivo) VALUES
(1, 'Musica d''insieme', 'Laboratorio di musica d''insieme', 3, '18:00:00', '19:30:00', 1),
(2, 'Teoria e Solfeggio', 'Corso di teoria musicale e solfeggio', 2, '17:00:00', '18:00:00', 1),
(3, 'Pianoforte per Cantanti', 'Corso di pianoforte complementare per cantanti', 4, '16:00:00', '17:00:00', 1);

-- 6. Dati iniziali Tipi Corso
INSERT OR IGNORE INTO tipi_corso_config (id, nome, durata_lezione, costo_mensile, include_laboratorio, descrizione, ordine_visualizzazione) VALUES
(1, 'Base 45min', 45, 80.00, 0, 'Lezione individuale 45 minuti', 1),
(2, 'Base 60min', 60, 100.00, 0, 'Lezione individuale 60 minuti', 2),
(3, 'Standard 45min + Teoria', 45, 95.00, 1, 'Lezione 45min + Laboratorio Teoria', 3),
(4, 'Standard 60min + Teoria', 60, 115.00, 1, 'Lezione 60min + Laboratorio Teoria', 4),
(5, 'Extended 45min + Musica Insieme', 45, 105.00, 1, 'Lezione 45min + Musica d''insieme', 5),
(6, 'Extended 60min + Musica Insieme', 60, 125.00, 1, 'Lezione 60min + Musica d''insieme', 6);
