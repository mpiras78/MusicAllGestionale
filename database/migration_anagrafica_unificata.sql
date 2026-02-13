-- ============================================
-- MIGRATION: Anagrafica Unificata
-- Data: 13/02/2026
-- Versione: 3.0.0
-- ============================================

-- FASE 1: CREAZIONE NUOVE TABELLE
-- ============================================

-- 1. Tabella Persone (Anagrafica Master)
CREATE TABLE IF NOT EXISTS persone (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cognome TEXT NOT NULL,
    nome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    cellulare TEXT,
    data_nascita DATE,
    luogo_nascita TEXT,
    codice_fiscale TEXT UNIQUE,
    indirizzo TEXT,
    cap TEXT,
    citta TEXT,
    provincia TEXT,
    nazione TEXT DEFAULT 'Italia',
    note_anagrafiche TEXT,
    attiva INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME
);

CREATE INDEX IF NOT EXISTS idx_persone_cognome_nome ON persone(cognome, nome);
CREATE INDEX IF NOT EXISTS idx_persone_email ON persone(email);
CREATE INDEX IF NOT EXISTS idx_persone_cf ON persone(codice_fiscale);
CREATE INDEX IF NOT EXISTS idx_persone_attiva ON persone(attiva);

-- 2. Tabella Soci (Raccordo Ruoli)
CREATE TABLE IF NOT EXISTS soci (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    persona_id INTEGER NOT NULL,
    tipo_socio TEXT NOT NULL CHECK(tipo_socio IN ('allievo', 'docente', 'esterno', 'admin')),
    data_inizio DATE NOT NULL,
    data_fine DATE,
    stato TEXT DEFAULT 'attivo' CHECK(stato IN ('attivo', 'sospeso', 'cessato')),
    note_ruolo TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_soci_persona ON soci(persona_id);
CREATE INDEX IF NOT EXISTS idx_soci_tipo ON soci(tipo_socio, stato);
CREATE INDEX IF NOT EXISTS idx_soci_stato ON soci(stato);
CREATE UNIQUE INDEX IF NOT EXISTS idx_soci_persona_tipo_attivo ON soci(persona_id, tipo_socio, stato) 
    WHERE stato = 'attivo';

-- 3. Tabelle Dettagli Specifici per Ruolo

-- 3a. Dettagli Allievo
CREATE TABLE IF NOT EXISTS soci_allievo_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    socio_id INTEGER NOT NULL UNIQUE,
    tutore_nome TEXT,
    tutore_cognome TEXT,
    tutore_telefono TEXT,
    tutore_email TEXT,
    tutore_relazione TEXT,
    scuola_frequentata TEXT,
    livello_iniziale TEXT,
    obiettivi TEXT,
    note_didattiche TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME,
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_soci_allievo_socio ON soci_allievo_dettagli(socio_id);

-- 3b. Dettagli Docente
CREATE TABLE IF NOT EXISTS soci_docente_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    socio_id INTEGER NOT NULL UNIQUE,
    user_id INTEGER,
    specializzazioni TEXT,
    cv TEXT,
    titoli_studio TEXT,
    esperienze TEXT,
    disponibilita TEXT,
    tariffe_orarie REAL,
    note_professionali TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME,
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_soci_docente_socio ON soci_docente_dettagli(socio_id);
CREATE INDEX IF NOT EXISTS idx_soci_docente_user ON soci_docente_dettagli(user_id);

-- 3c. Dettagli Esterno
CREATE TABLE IF NOT EXISTS soci_esterno_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    socio_id INTEGER NOT NULL UNIQUE,
    tipo_esterno TEXT DEFAULT 'privato' CHECK(tipo_esterno IN ('privato', 'band', 'associazione', 'azienda')),
    nome_band TEXT,
    partita_iva TEXT,
    codice_sdi TEXT,
    pec TEXT,
    note_commerciali TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME,
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_soci_esterno_socio ON soci_esterno_dettagli(socio_id);

-- FASE 2: VISTE DI COMPATIBILITÀ
-- ============================================

-- Vista v_allievi (emula tabella allievi)
CREATE VIEW IF NOT EXISTS v_allievi AS
SELECT 
    s.id as id,
    p.id as persona_id,
    p.cognome,
    p.nome,
    p.cognome || ' ' || p.nome as nome_completo,
    p.email,
    p.telefono,
    p.data_nascita,
    p.indirizzo,
    p.note_anagrafiche as note,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at,
    ad.tutore_nome,
    ad.tutore_cognome,
    ad.tutore_telefono,
    ad.tutore_email,
    ad.scuola_frequentata,
    ad.note_didattiche
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_allievo_dettagli ad ON s.id = ad.socio_id
WHERE s.tipo_socio = 'allievo';

-- Vista v_docenti (emula tabella docenti)
CREATE VIEW IF NOT EXISTS v_docenti AS
SELECT 
    s.id as id,
    p.id as persona_id,
    p.cognome,
    p.nome,
    p.cognome || ' ' || p.nome as nome_completo,
    p.email,
    p.telefono,
    dd.specializzazioni,
    p.note_anagrafiche as note,
    dd.user_id,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at,
    dd.cv,
    dd.titoli_studio,
    dd.tariffe_orarie
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_docente_dettagli dd ON s.id = dd.socio_id
WHERE s.tipo_socio = 'docente';

-- Vista v_soci_occasionali (emula tabella soci_occasionali)
CREATE VIEW IF NOT EXISTS v_soci_occasionali AS
SELECT 
    s.id as id,
    p.id as persona_id,
    p.cognome,
    p.nome,
    p.cognome || ' ' || p.nome as nome_completo,
    p.email,
    p.telefono,
    ed.tipo_esterno as tipo,
    ed.partita_iva,
    p.codice_fiscale,
    p.note_anagrafiche as note,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at,
    ed.nome_band,
    ed.pec,
    ed.codice_sdi
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_esterno_dettagli ed ON s.id = ed.socio_id
WHERE s.tipo_socio = 'esterno';

-- Vista v_persone_multirolo (persone con più ruoli attivi)
CREATE VIEW IF NOT EXISTS v_persone_multirolo AS
SELECT 
    p.id as persona_id,
    p.cognome,
    p.nome,
    p.email,
    GROUP_CONCAT(s.tipo_socio, ', ') as ruoli,
    COUNT(DISTINCT s.tipo_socio) as numero_ruoli
FROM persone p
JOIN soci s ON p.id = s.persona_id
WHERE s.stato = 'attivo'
GROUP BY p.id, p.cognome, p.nome, p.email
HAVING COUNT(DISTINCT s.tipo_socio) > 1;

-- FASE 3: TRIGGER PER UPDATED_AT
-- ============================================

-- Trigger per persone
CREATE TRIGGER IF NOT EXISTS trg_persone_updated_at
AFTER UPDATE ON persone
FOR EACH ROW
BEGIN
    UPDATE persone SET updated_at = datetime('now','localtime') WHERE id = NEW.id;
END;

-- Trigger per soci
CREATE TRIGGER IF NOT EXISTS trg_soci_updated_at
AFTER UPDATE ON soci
FOR EACH ROW
BEGIN
    UPDATE soci SET updated_at = datetime('now','localtime') WHERE id = NEW.id;
END;

-- Trigger per soci_allievo_dettagli
CREATE TRIGGER IF NOT EXISTS trg_soci_allievo_updated_at
AFTER UPDATE ON soci_allievo_dettagli
FOR EACH ROW
BEGIN
    UPDATE soci_allievo_dettagli SET updated_at = datetime('now','localtime') WHERE id = NEW.id;
END;

-- Trigger per soci_docente_dettagli
CREATE TRIGGER IF NOT EXISTS trg_soci_docente_updated_at
AFTER UPDATE ON soci_docente_dettagli
FOR EACH ROW
BEGIN
    UPDATE soci_docente_dettagli SET updated_at = datetime('now','localtime') WHERE id = NEW.id;
END;

-- Trigger per soci_esterno_dettagli
CREATE TRIGGER IF NOT EXISTS trg_soci_esterno_updated_at
AFTER UPDATE ON soci_esterno_dettagli
FOR EACH ROW
BEGIN
    UPDATE soci_esterno_dettagli SET updated_at = datetime('now','localtime') WHERE id = NEW.id;
END;

-- ============================================
-- MIGRATION COMPLETATA - FASE 1-3
-- ============================================
-- Prossimi step:
-- 1. Eseguire script migrazione dati (migration_dati_anagrafica.sql)
-- 2. Verificare integrità dati
-- 3. Aggiornare FK nelle tabelle esistenti
-- 4. Drop vecchie tabelle
-- ============================================