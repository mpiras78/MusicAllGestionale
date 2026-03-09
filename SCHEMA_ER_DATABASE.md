# 📊 SCHEMA ER - DATABASE MUSICALL v3.0

## Tabelle Principali

### 1. **dati_associazione** (Nuova)
```sql
CREATE TABLE dati_associazione (
    id INTEGER PRIMARY KEY,
    ragione_sociale VARCHAR(255) NOT NULL,
    indirizzo VARCHAR(255) NOT NULL,
    cap VARCHAR(5) NOT NULL,
    citta VARCHAR(100) NOT NULL,
    codice_fiscale VARCHAR(16) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    email VARCHAR(255),
    pec VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. **soci** (Rinominato da soci)
```sql
CREATE TABLE soci (
    id INTEGER PRIMARY KEY,
    cognome VARCHAR(100) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_nascita DATE,
    email VARCHAR(255),
    telefono_1 VARCHAR(20),
    telefono_2 VARCHAR(20),
    indirizzo VARCHAR(255),
    cap VARCHAR(5),
    citta VARCHAR(100),
    attivo INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. **iscrizioni_annuali** (Nuova)
```sql
CREATE TABLE iscrizioni_annuali (
    id INTEGER PRIMARY KEY,
    socio_id INTEGER NOT NULL,
    numero_tessera VARCHAR(20) NOT NULL UNIQUE,
    anno_accademico VARCHAR(9) NOT NULL, -- es: 2025-2026
    data_iscrizione DATE NOT NULL,
    mese_iscrizione INTEGER NOT NULL, -- 1-12
    costo_iscrizione DECIMAL(10,2) NOT NULL,
    sconto_familiare INTEGER DEFAULT 0, -- 1=si, 0=no
    data_fine DATE DEFAULT NULL, -- 31 luglio anno accademico
    stato VARCHAR(20) DEFAULT 'attiva', -- attiva, sospesa, conclusa
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(socio_id) REFERENCES soci(id)
);
```

### 4. **corsi_soci** (Modificato da iscrizioni)
```sql
CREATE TABLE corsi_soci (
    id INTEGER PRIMARY KEY,
    socio_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    aula_id INTEGER NOT NULL,
    tipo_corso_id INTEGER NOT NULL,
    giorno_settimana INTEGER NOT NULL, -- 0=lunedì, 6=domenica
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE DEFAULT NULL, -- NULL=ancora attivo
    costo_mensile DECIMAL(10,2) NOT NULL,
    stato VARCHAR(20) DEFAULT 'attivo', -- attivo, sospeso, concluso
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(socio_id) REFERENCES soci(id),
    FOREIGN KEY(materia_id) REFERENCES materie(id),
    FOREIGN KEY(docente_id) REFERENCES docenti(id),
    FOREIGN KEY(aula_id) REFERENCES aule(id),
    FOREIGN KEY(tipo_corso_id) REFERENCES tipi_corso_config(id)
);
```

### 5. **pagamenti** (Nuova)
```sql
CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY,
    socio_id INTEGER NOT NULL,
    corso_socio_id INTEGER DEFAULT NULL, -- NULL se pagamento iscrizione
    tipo_pagamento VARCHAR(20) NOT NULL, -- 'iscrizione' o 'corso'
    mensilita_riferimento VARCHAR(7) NOT NULL, -- es: 2026-03 (anno-mese)
    importo DECIMAL(10,2) NOT NULL,
    modalita_pagamento_id INTEGER NOT NULL,
    stato VARCHAR(20) DEFAULT 'pagato', -- pagato, parziale, arretrato
    nota TEXT,
    data_pagamento DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(socio_id) REFERENCES soci(id),
    FOREIGN KEY(corso_socio_id) REFERENCES corsi_soci(id),
    FOREIGN KEY(modalita_pagamento_id) REFERENCES modalita_pagamento(id)
);
```

### 6. **famiglia** (Nuova)
```sql
CREATE TABLE famiglia (
    id INTEGER PRIMARY KEY,
    socio_id INTEGER NOT NULL,
    socio_familiare_id INTEGER NOT NULL,
    relazione VARCHAR(50), -- es: 'fratello', 'sorella', 'genitore'
    data_collegamento DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(socio_id) REFERENCES soci(id),
    FOREIGN KEY(socio_familiare_id) REFERENCES soci(id)
);
```

### 7. **modalita_pagamento** (Nuova)
```sql
CREATE TABLE modalita_pagamento (
    id INTEGER PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE, -- 'Contanti', 'Bonifico'
    descrizione TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 8. **chiusure_attivita** (Nuova)
```sql
CREATE TABLE chiusure_attivita (
    id INTEGER PRIMARY KEY,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    motivo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 9. **alert** (Nuova)
```sql
CREATE TABLE alert (
    id INTEGER PRIMARY KEY,
    socio_id INTEGER DEFAULT NULL,
    numero_tessera VARCHAR(20) DEFAULT NULL,
    tipo_alert VARCHAR(50) NOT NULL, -- 'sovrapposizione_corso', 'sospensione', etc
    messaggio TEXT NOT NULL,
    giorno_settimana INTEGER,
    ora_inizio TIME,
    ora_fine TIME,
    aula_id INTEGER DEFAULT NULL,
    visibile INTEGER DEFAULT 1, -- 1=visibile, 0=nascosto
    created_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(socio_id) REFERENCES soci(id),
    FOREIGN KEY(aula_id) REFERENCES aule(id),
    FOREIGN KEY(created_by) REFERENCES utenti(id)
);
```

### 10. **batch_runs** (Nuova)
```sql
CREATE TABLE batch_runs (
    id INTEGER PRIMARY KEY,
    tipo_batch VARCHAR(50) NOT NULL, -- 'email_promemoria_pagamento'
    data_esecuzione DATE NOT NULL,
    ora_esecuzione TIME NOT NULL,
    stato VARCHAR(20) DEFAULT 'completato', -- 'in_esecuzione', 'completato', 'errore'
    numero_email_inviate INTEGER DEFAULT 0,
    errori TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 11. **audit_log** (Nuova)
```sql
CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY,
    utente_id INTEGER NOT NULL,
    tipo_modifica VARCHAR(100) NOT NULL, -- 'inserimento_nuovo_socio', 'modifica_corso', etc
    tabella_interessata VARCHAR(100),
    record_id INTEGER,
    vecchia_informazione TEXT,
    nuova_informazione TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(utente_id) REFERENCES utenti(id)
);
```

### 12. **sospensioni_corso** (Nuova)
```sql
CREATE TABLE sospensioni_corso (
    id INTEGER PRIMARY KEY,
    corso_socio_id INTEGER NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE DEFAULT NULL, -- NULL se sospensione temporanea senza fine
    tipo VARCHAR(20) NOT NULL, -- 'temporanea', 'definitiva'
    motivo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(corso_socio_id) REFERENCES corsi_soci(id)
);
```

---

## Relazioni ER (Diagramma Testuale)

```
dati_associazione (1) ---- (0..N) utenti

soci (1) ---- (0..N) iscrizioni_annuali
soci (1) ---- (0..N) corsi_soci
soci (1) ---- (0..N) pagamenti
soci (1) ---- (0..N) famiglia
soci (1) ---- (0..N) alert

iscrizioni_annuali (1) ---- (0..N) pagamenti

corsi_soci (1) ---- (0..N) pagamenti
corsi_soci (1) ---- (0..N) sospensioni_corso

materie (1) ---- (0..N) corsi_soci
docenti (1) ---- (0..N) corsi_soci
aule (1) ---- (0..N) corsi_soci
tipi_corso_config (1) ---- (0..N) corsi_soci

modalita_pagamento (1) ---- (0..N) pagamenti

famiglia (N) --> (1) soci (riferimento bidirezionale)

alert --> soci (opzionale)
alert --> aule (opzionale)

audit_log (0..N) <-- utenti (chi ha fatto la modifica)
```

---

## Indici Consigliati

```sql
CREATE INDEX idx_soci_cognome_nome ON soci(cognome, nome);
CREATE INDEX idx_iscrizioni_socio ON iscrizioni_annuali(socio_id);
CREATE INDEX idx_iscrizioni_anno ON iscrizioni_annuali(anno_accademico);
CREATE INDEX idx_corsi_socio ON corsi_soci(socio_id);
CREATE INDEX idx_corsi_data_fine ON corsi_soci(data_fine);
CREATE INDEX idx_pagamenti_socio ON pagamenti(socio_id);
CREATE INDEX idx_pagamenti_mensilita ON pagamenti(mensilita_riferimento);
CREATE INDEX idx_alert_socio ON alert(socio_id);
CREATE INDEX idx_alert_visibile ON alert(visibile);
CREATE INDEX idx_audit_utente ON audit_log(utente_id);
CREATE INDEX idx_audit_timestamp ON audit_log(created_at);
```

---

## Migrazione da Soci → Soci

```sql
-- Backup tabella originale
CREATE TABLE soci_backup AS SELECT * FROM soci;

-- Rinomina tabella
ALTER TABLE soci RENAME TO soci;

-- Aggiungi nuove colonne
ALTER TABLE soci ADD COLUMN telefono_2 VARCHAR(20);
ALTER TABLE soci ADD COLUMN cap VARCHAR(5);
ALTER TABLE soci ADD COLUMN citta VARCHAR(100);
ALTER TABLE soci ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Aggiorna colonne esistenti se necessario
UPDATE soci SET attivo = 1 WHERE attivo IS NULL;
```

---

## Note Importanti

1. **Numero Tessera**: Contatore unico per anno accademico (es: 20261, 20262, ...)
2. **Mensilità di Riferimento**: Formato YYYY-MM (es: 2026-03) per tracciare il mese del pagamento
3. **Data Fine NULL**: Indica un corso/iscrizione ancora attivo
4. **Flag Visibile in Alert**: 1=visibile, 0=nascosto (non cancellare, solo marcare)
5. **Audit Trail**: Traccia TUTTE le modifiche tranne le cancellazioni di pagamenti
6. **Famiglia Bidirezionale**: Se Mario è fratello di Anna, si creano 2 record nella tabella
