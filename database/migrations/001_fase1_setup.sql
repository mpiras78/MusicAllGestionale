-- ================================================
-- FASE 1: Setup Database per MusicAll v3.0
-- Data: 15 Novembre 2026
-- Descrizione: Rinomina allievi → soci + nuove tabelle
-- ================================================

-- ===============================================
-- 1. RINOMINA TABELLA: allievi → soci
-- ===============================================

-- Creiamo la nuova tabella con tutte le colonne
CREATE TABLE soci AS SELECT * FROM allievi;

-- Aggiungere le nuove colonne necessarie
ALTER TABLE soci ADD COLUMN telefono_2 VARCHAR(20) AFTER telefono;
ALTER TABLE soci ADD COLUMN cap VARCHAR(5) AFTER indirizzo;
ALTER TABLE soci ADD COLUMN citta VARCHAR(100) AFTER cap;
ALTER TABLE soci ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE soci ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Se il database supporta, rendere PRIMARY KEY la colonna id
-- (normalmente già presente, ma verificare)

-- Aggiungere indici per le nuove colonne
CREATE INDEX idx_soci_citta ON soci(citta);
CREATE INDEX idx_soci_cap ON soci(cap);

-- Rinominare la tabella originale per backup
ALTER TABLE allievi RENAME TO allievi_v2_backup;

-- ===============================================
-- 2. TABELLA: dati_associazione
-- ===============================================

CREATE TABLE dati_associazione (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome_scuola VARCHAR(255) NOT NULL,
  indirizzo VARCHAR(255),
  cap VARCHAR(5),
  citta VARCHAR(100),
  provincia VARCHAR(2),
  telefono_principale VARCHAR(20),
  telefono_secondario VARCHAR(20),
  email_amministrativa VARCHAR(255),
  email_pagamenti VARCHAR(255),
  iban VARCHAR(34),
  intestatario_conto VARCHAR(255),
  costo_iscrizione_agosto_febbraio DECIMAL(10, 2) DEFAULT 150.00,
  costo_iscrizione_marzo_luglio DECIMAL(10, 2) DEFAULT 100.00,
  sconto_familiare_percentuale DECIMAL(5, 2) DEFAULT 10.00,
  sconto_compleanno_percentuale DECIMAL(5, 2) DEFAULT 5.00,
  numero_recuperi_garantiti INT DEFAULT 3,
  partita_iva VARCHAR(20),
  codice_fiscale VARCHAR(16),
  descrizione_ricevute VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Dati iniziali
INSERT INTO dati_associazione (
  nome_scuola, 
  indirizzo, 
  cap, 
  citta, 
  provincia,
  costo_iscrizione_agosto_febbraio,
  costo_iscrizione_marzo_luglio,
  sconto_familiare_percentuale,
  numero_recuperi_garantiti
) VALUES (
  'Scuola di Musica MusicAll',
  'Via Roma 123',
  '16100',
  'Genova',
  'GE',
  150.00,
  100.00,
  10.00,
  3
);

-- ===============================================
-- 3. TABELLA: iscrizioni_annuali (NEW)
-- ===============================================

CREATE TABLE iscrizioni_annuali (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  anno_accademico INT NOT NULL, -- es: 2026 per settembre 2026 - luglio 2027
  numero_tessera VARCHAR(20) NOT NULL UNIQUE, -- es: 20261, 20262, ...
  costo_iscrizione DECIMAL(10, 2) NOT NULL,
  stato_pagamento ENUM('PAGAMENTO_PENDENTE', 'PAGATO', 'ANNULLATO') DEFAULT 'PAGAMENTO_PENDENTE',
  data_iscrizione DATE NOT NULL,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  UNIQUE KEY unique_socio_anno (socio_id, anno_accademico),
  INDEX idx_numero_tessera (numero_tessera),
  INDEX idx_anno_accademico (anno_accademico),
  INDEX idx_stato_pagamento (stato_pagamento)
);

-- ===============================================
-- 4. TABELLA: modalita_pagamento (NEW)
-- ===============================================

CREATE TABLE modalita_pagamento (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(50) NOT NULL UNIQUE,
  descrizione VARCHAR(255),
  attivo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dati iniziali
INSERT INTO modalita_pagamento (nome, descrizione) VALUES
('CONTANTI', 'Pagamento in contanti presso la scuola'),
('BONIFICO', 'Trasferimento bancario'),
('CARTA', 'Carta di credito (da implementare)');

-- ===============================================
-- 5. TABELLA: pagamenti (NEW)
-- ================================================

CREATE TABLE pagamenti (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  importo DECIMAL(10, 2) NOT NULL,
  mensilita_riferimento VARCHAR(7) NOT NULL, -- formato: YYYY-MM (es: 2026-11)
  modalita_pagamento_id INT NOT NULL,
  stato ENUM('PAGATO', 'IN_ATTESA', 'ANNULLATO') DEFAULT 'PAGATO',
  data_pagamento DATETIME,
  admin_id INT, -- NULL se pagato da socio direttamente, ID se verificato da admin
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  FOREIGN KEY (modalita_pagamento_id) REFERENCES modalita_pagamento(id),
  INDEX idx_socio_mese (socio_id, mensilita_riferimento),
  INDEX idx_stato (stato),
  INDEX idx_data_pagamento (data_pagamento)
);

-- ================================================
-- 6. TABELLA: famiglia (NEW)
-- ================================================

CREATE TABLE famiglia (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  familiare_id INT NOT NULL,
  data_collegamento DATE NOT NULL,
  stato ENUM('ATTIVO', 'REVOCATO') DEFAULT 'ATTIVO',
  data_revoca DATE,
  motivo_revoca VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  FOREIGN KEY (familiare_id) REFERENCES soci(id) ON DELETE CASCADE,
  UNIQUE KEY unique_coppia (socio_id, familiare_id),
  INDEX idx_stato (stato)
);

-- ================================================
-- 7. TABELLA: sospensioni_corso (NEW)
-- ================================================

CREATE TABLE sospensioni_corso (
  id INT PRIMARY KEY AUTO_INCREMENT,
  corso_id INT NOT NULL,
  tipo ENUM('TEMPORANEA', 'DEFINITIVA') NOT NULL,
  data_inizio DATE NOT NULL,
  data_fine DATE, -- NULL se DEFINITIVA
  motivo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (corso_id) REFERENCES corsi_soci(id) ON DELETE CASCADE,
  INDEX idx_tipo (tipo),
  INDEX idx_date_range (data_inizio, data_fine)
);

-- ================================================
-- 8. TABELLA: alert (NEW)
-- ================================================

CREATE TABLE alert (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  corso1_id INT,
  corso2_id INT,
  tipo ENUM('SOVRAPPOSIZIONE', 'ALTRO') DEFAULT 'SOVRAPPOSIZIONE',
  descrizione TEXT NOT NULL,
  visibile BOOLEAN DEFAULT TRUE, -- 1=da vedere, 0=nascosto (non eliminato)
  data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  FOREIGN KEY (corso1_id) REFERENCES corsi_soci(id) ON DELETE SET NULL,
  FOREIGN KEY (corso2_id) REFERENCES corsi_soci(id) ON DELETE SET NULL,
  INDEX idx_socio_visibile (socio_id, visibile),
  INDEX idx_tipo (tipo)
);

-- ================================================
-- 9. TABELLA: batch_runs (NEW)
-- ================================================

CREATE TABLE batch_runs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  batch_tipo VARCHAR(100) NOT NULL,
  data_esecuzione DATETIME NOT NULL,
  numero_email_inviate INT DEFAULT 0,
  numero_email_fallite INT DEFAULT 0,
  status ENUM('SUCCESSO', 'ERRORE', 'PARZIALE') DEFAULT 'SUCCESSO',
  log_dettagli TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_batch_tipo (batch_tipo),
  INDEX idx_data_esecuzione (data_esecuzione)
);

-- ================================================
-- 10. TABELLA: audit_log (NEW)
-- ================================================

CREATE TABLE audit_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  azione VARCHAR(100) NOT NULL, -- es: CREATE, UPDATE, DELETE, MODIFICA_CORSO_PRO_RATA
  tabella VARCHAR(100) NOT NULL, -- es: soci, pagamenti, corsi_soci
  record_id INT,
  vecchio_valore JSON, -- per UPDATE
  nuovo_valore JSON,
  admin_id INT,
  socio_id INT, -- chi ha effettuato l'azione se non admin
  ip_address VARCHAR(45),
  user_agent TEXT,
  dettagli TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_azione (azione),
  INDEX idx_tabella (tabella),
  INDEX idx_record (tabella, record_id),
  INDEX idx_created_at (created_at),
  INDEX idx_socio_id (socio_id)
);

-- ================================================
-- 11. TABELLA: chiusure_attivita (NEW)
-- ================================================

CREATE TABLE chiusure_attivita (
  id INT PRIMARY KEY AUTO_INCREMENT,
  data_chiusura DATE NOT NULL,
  tipo ENUM('FESTIVIDTA_NAZIONALE', 'FESTIVIDITA_REGIONALE', 'CHIUSURA_SCUOLA', 'EVENTO') DEFAULT 'CHIUSURA_SCUOLA',
  descrizione VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_data (data_chiusura),
  INDEX idx_tipo (tipo)
);

-- ================================================
-- 12. AGGIORNAMENTO: Modifiche a tabelle existing
-- ================================================

-- Se la tabella corsi_soci esiste, aggiungere colonne di timestamp
ALTER TABLE corsi_soci ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE corsi_soci ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ================================================
-- 13. VERIFICA E VALIDAZIONE
-- ================================================

-- Query verifica: numero righe migrate
SELECT 
  'soci' as tabella, 
  COUNT(*) as numero_righe 
FROM soci
UNION ALL
SELECT 
  'allievi_v2_backup', 
  COUNT(*) 
FROM allievi_v2_backup;

-- Query verifica: numero tessera generato
SELECT 
  COUNT(*) as numero_iscrizioni,
  MAX(numero_tessera) as ultimo_tessera
FROM iscrizioni_annuali;

-- ================================================
-- 14. INDICI DI PERFORMANCE (aggiuntivi)
-- ================================================

CREATE INDEX idx_soci_email ON soci(email);
CREATE INDEX idx_soci_cognome_nome ON soci(cognome, nome);
CREATE INDEX idx_pagamenti_socio ON pagamenti(socio_id);
CREATE INDEX idx_iscrizioni_socio ON iscrizioni_annuali(socio_id);
CREATE INDEX idx_famiglia_socio ON famiglia(socio_id);
CREATE INDEX idx_famiglia_familiare ON famiglia(familiare_id);

-- ================================================
-- NOTE IMPORTANTI
-- ================================================
/*
1. BACKUP: Assicurarsi di aver fatto backup di 'allievi' prima di eseguire
2. TEST: Eseguire le query di verifica per validare la migrazione
3. APPLICAZIONE: Aggiornare tutti i file PHP che referenziano 'allievi' → 'soci'
4. STORICO: La tabella 'allievi_v2_backup' rimane per storico, può essere eliminata dopo test
5. DATA: I timestamps utilizzano DEFAULT CURRENT_TIMESTAMP per automatizzare la traccia
6. TRANSAZIONI: Se il DB supporta, eseguire in transazione (BEGIN; ... COMMIT;)
*/

-- ================================================
-- CONVALIDA FINALE
-- ================================================

-- Verifica che tutte le tabelle siano state create
SHOW TABLES;

-- Verifica schema della tabella soci
DESCRIBE soci;

-- Verifica schema dati_associazione
DESCRIBE dati_associazione;
