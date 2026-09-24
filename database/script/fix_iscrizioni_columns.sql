-- Fix colonne tabella iscrizioni
-- Allinea struttura con il codice

-- Verifica se mancano colonne e aggiungile
ALTER TABLE iscrizioni ADD COLUMN tipo_corso_config_id INTEGER;
ALTER TABLE iscrizioni ADD COLUMN quota_iscrizione DECIMAL(10,2) DEFAULT 30.00;
ALTER TABLE iscrizioni ADD COLUMN sconto_fratelli DECIMAL(10,2) DEFAULT 0;
ALTER TABLE iscrizioni ADD COLUMN sconto_meta_anno DECIMAL(10,2) DEFAULT 0;

-- Se la tabella usa data_inizio_corso invece di data_inizio, rinomina
-- SQLite non supporta RENAME COLUMN direttamente, quindi usiamo una tabella temporanea

-- Backup dati esistenti
CREATE TABLE iscrizioni_backup AS SELECT * FROM iscrizioni;

-- Ricrea tabella con struttura corretta
DROP TABLE IF EXISTS iscrizioni;

CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Allievo e Corso
    allievo_id INTEGER NOT NULL,
    tipo_corso_config_id INTEGER,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    
    -- Anno Accademico
    anno_accademico VARCHAR(20) NOT NULL,
    
    -- Date
    data_inizio DATE NOT NULL,
    data_fine DATE,
    
    -- Stato
    stato VARCHAR(20) DEFAULT 'attiva',
    
    -- Pagamenti
    quota_iscrizione DECIMAL(10,2) DEFAULT 30.00,
    sconto_fratelli DECIMAL(10,2) DEFAULT 0,
    sconto_meta_anno DECIMAL(10,2) DEFAULT 0,
    
    -- Note
    note TEXT,
    
    -- Audit
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (tipo_corso_config_id) REFERENCES tipi_corso_config(id),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id)
);

-- Ripristina dati (adatta i nomi colonne se necessario)
INSERT INTO iscrizioni (
    id, allievo_id, tipo_corso_config_id, materia_id, docente_id,
    anno_accademico, data_inizio, data_fine, stato, 
    quota_iscrizione, sconto_fratelli, note, created_at
)
SELECT 
    id, 
    allievo_id,
    COALESCE(tipo_corso_config_id, tipo_corso_id) as tipo_corso_config_id,
    materia_id,
    docente_id,
    anno_accademico,
    COALESCE(data_inizio, data_inizio_corso, data_iscrizione) as data_inizio,
    COALESCE(data_fine, data_fine_corso) as data_fine,
    stato,
    COALESCE(quota_iscrizione, 30.00) as quota_iscrizione,
    COALESCE(sconto_fratelli, 0) as sconto_fratelli,
    note,
    created_at
FROM iscrizioni_backup;

-- Ricrea indici
CREATE INDEX idx_iscrizioni_allievo ON iscrizioni(allievo_id, stato);
CREATE INDEX idx_iscrizioni_anno ON iscrizioni(anno_accademico, stato);
CREATE INDEX idx_iscrizioni_docente ON iscrizioni(docente_id, stato);
CREATE INDEX idx_iscrizioni_stato ON iscrizioni(stato, data_inizio);

-- Rimuovi backup
DROP TABLE iscrizioni_backup;
