# 🚀 ISTRUZIONI ESECUZIONE FASE 1

## Setup Database MusicAll v3.0

### Prerequisiti
- Database backup fatto ✅
- Accesso admin al database
- SQLite / MySQL / PostgreSQL running
- File `database/migrations/001_fase1_setup.sql` pronto

---

## Opzione A: Esecuzione da CLI

### Per SQLite
```bash
cd c:\git\ct-poc\MusicAll

# Eseguire lo script SQL
sqlite3 database/musicall.db < database/migrations/001_fase1_setup.sql

# Verificare le tabelle
sqlite3 database/musicall.db ".tables"
```

### Per MySQL
```bash
cd c:\git\ct-poc\MusicAll

# Eseguire lo script SQL
mysql -h localhost -u root -p musicall_db < database/migrations/001_fase1_setup.sql

# Oppure con password in chiaro (non consigliato):
# mysql -h localhost -u root -pPASSWORD musicall_db < database/migrations/001_fase1_setup.sql
```

### Per PostgreSQL
```bash
cd c:\git\ct-poc\MusicAll

psql -U postgres -d musicall_db -f database/migrations/001_fase1_setup.sql
```

---

## Opzione B: Esecuzione da GUI

### Via phpMyAdmin (MySQL)
1. Accedere a phpMyAdmin
2. Selezionare database `musicall_db`
3. Andare a "Import"
4. Caricare il file `database/migrations/001_fase1_setup.sql`
5. Cliccare "Esegui"
6. Verificare il risultato

### Via SQLite Browser
1. Aprire SQLite Browser
2. Apri file database `database/musicall.db`
3. Vai a "File" → "Importa" o "SQL" → "Execute SQL"
4. Incolla il contenuto di `001_fase1_setup.sql`
5. Esegui

---

## Step-by-Step Verifica Dopo Migrazione

### 1. Verifica Tabelle Create
```sql
-- Dovrebbe mostrare tutte le tabelle
SHOW TABLES;  -- MySQL/PostgreSQL
.tables       -- SQLite

-- Tabelle attese:
-- - soci (rinominato da allievi)
-- - allievi_v2_backup (backup vecchio)
-- - dati_associazione (NEW)
-- - iscrizioni_annuali (NEW)
-- - modalita_pagamento (NEW)
-- - pagamenti (NEW)
-- - famiglia (NEW)
-- - sospensioni_corso (NEW)
-- - alert (NEW)
-- - batch_runs (NEW)
-- - audit_log (NEW)
-- - chiusure_attivita (NEW)
```

### 2. Verifica Dati Migrati
```sql
-- Contare righe in soci
SELECT COUNT(*) as numero_soci FROM soci;

-- Contare righe in backup
SELECT COUNT(*) as numero_backup FROM allievi_v2_backup;

-- Dovrebbero essere uguali
```

### 3. Verifica Colonne Nuove
```sql
-- Verificare che soci abbia le nuove colonne
DESCRIBE soci;
-- DOVREBBE mostrare:
-- - telefono_2
-- - cap
-- - citta
-- - created_at
-- - updated_at
```

### 4. Verifica Dati Associazione
```sql
-- Verificare dati iniziali
SELECT * FROM dati_associazione;

-- Dovrebbe mostrare 1 riga con:
-- - nome_scuola: "Scuola di Musica MusicAll"
-- - costo_iscrizione_agosto_febbraio: 150.00
-- - costo_iscrizione_marzo_luglio: 100.00
-- - sconto_familiare_percentuale: 10.00
-- - numero_recuperi_garantiti: 3
```

### 5. Verifica Modalita Pagamento
```sql
SELECT * FROM modalita_pagamento;

-- Dovrebbe mostrare 3 righe:
-- - CONTANTI
-- - BONIFICO
-- - CARTA
```

### 6. Test Indici
```sql
-- MySQL/PostgreSQL
SHOW INDEX FROM soci;
SHOW INDEX FROM pagamenti;
SHOW INDEX FROM audit_log;

-- Dovrebbero essere presenti gli indici per performance
```

---

## ⚠️ Troubleshooting

### Errore: "Table 'allievi' already renamed"
**Soluzione**: Lo script è stato eseguito due volte. Controllare se esiste `allievi_v2_backup`.
```sql
-- Verificare
SHOW TABLES LIKE 'allievi%';

-- Se allievi_v2_backup esiste, è tutto ok
-- Se allievi non esiste, lo script ha già funzionato
```

### Errore: "Duplicate column 'created_at'"
**Soluzione**: La colonna esiste già in corsi_soci. Rimuovere le linee di ALTER TABLE oppure usare `IF NOT EXISTS`.

### Errore: "Foreign key constraint fails"
**Soluzione**: Verificare che le tabelle di riferimento esistano. Se necessario, disabilitare FK durante migrazione:
```sql
-- MySQL
SET FOREIGN_KEY_CHECKS = 0;
-- ... esegui script ...
SET FOREIGN_KEY_CHECKS = 1;
```

### Errore: "Unknown column 'id' in corsi_soci"
**Soluzione**: Tabella corsi_soci deve avere colonna `id` come primary key. Verificare schema attuale.

---

## ✅ Validazione Finale (Smoke Test)

Eseguire questi test dopo migrazione:

```sql
-- Test 1: Inserisci nuovo socio
INSERT INTO soci (nome, cognome, email, telefono, citta)
VALUES ('Test', 'User', 'test@example.com', '3201234567', 'Genova');

-- Test 2: Verifica nuovo socio
SELECT * FROM soci WHERE cognome = 'User';

-- Test 3: Crea iscrizione annuale
INSERT INTO iscrizioni_annuali (socio_id, anno_accademico, numero_tessera, costo_iscrizione, data_iscrizione)
VALUES (1, 2026, '20261', 150.00, CURDATE());

-- Test 4: Verifica iscrizione
SELECT * FROM iscrizioni_annuali WHERE numero_tessera = '20261';

-- Test 5: Crea pagamento
INSERT INTO pagamenti (socio_id, importo, mensilita_riferimento, modalita_pagamento_id)
VALUES (1, 120.00, '2026-11', 1);

-- Test 6: Verifica pagamento
SELECT * FROM pagamenti WHERE socio_id = 1;

-- Test 7: Aggiungi audit log
INSERT INTO audit_log (azione, tabella, record_id, nuovo_valore, socio_id)
VALUES ('CREATE', 'soci', 1, JSON_OBJECT('nome', 'Test', 'cognome', 'User'), 1);

-- Test 8: Verifica audit log
SELECT * FROM audit_log WHERE tabella = 'soci' ORDER BY created_at DESC LIMIT 1;

-- Se tutti i test passano, la migrazione è OK ✅
```

---

## 🔄 Rollback (Se Necessario)

Se la migrazione non va bene, rollback:

```sql
-- ATTENZIONE: Ripristina da backup (allievi_v2_backup)

-- 1. Rinominare soci di nuovo (o delete)
DROP TABLE soci;

-- 2. Ripristinare da backup
ALTER TABLE allievi_v2_backup RENAME TO allievi;

-- 3. Eliminare tutte le nuove tabelle
DROP TABLE iscrizioni_annuali;
DROP TABLE dati_associazione;
DROP TABLE pagamenti;
DROP TABLE modalita_pagamento;
DROP TABLE famiglia;
DROP TABLE sospensioni_corso;
DROP TABLE alert;
DROP TABLE batch_runs;
DROP TABLE audit_log;
DROP TABLE chiusure_attivita;

-- Database torna alla versione v2.x
```

---

## 📝 Checklist Post-Migrazione

- [ ] Script eseguito senza errori
- [ ] Tutte le 12 tabelle nuove create ✓
- [ ] `soci` contiene gli stessi record di `allievi` ✓
- [ ] `allievi_v2_backup` esiste come backup
- [ ] Indici creati ✓
- [ ] Dati iniziali (dati_associazione, modalita_pagamento) presenti ✓
- [ ] Test CRUD passati ✓
- [ ] Applicazione PHP aggiornata per usare `soci` ✓
- [ ] Admin notificato di migrazione completata

---

## 📊 Documento di Riferimento

Consultare **SCHEMA_ER_DATABASE.md** per:
- Descrizione dettagliata di ogni tabella
- Relazioni tra tabelle (ER diagram)
- Indici di performance
- Note di design

---

## 🆘 Support

Se hai problemi:
1. Controllare i log di errore del database
2. Verificare la sintassi SQL (commenti indicano tipo DB)
3. Eseguire singoli comandi per isolare l'errore
4. Verificare backup è stato creato prima di migrazione

**Nota**: Questo script è idempotente dove possibile, ma conviene sempre avere backup prima di eseguire.
