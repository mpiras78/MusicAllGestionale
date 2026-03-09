# Proposta Refactoring: Anagrafica Unificata

## Problema Attuale
Lo schema attuale ha 3 tabelle anagrafiche separate:
- `soci` - anagrafica soci
- `docenti` - anagrafica docenti  
- `soci_occasionali` - anagrafica esterni

**Limitazioni:**
- ❌ Duplicazione dati se una persona ha più ruoli
- ❌ Docente che segue lezioni come socio = 2 anagrafiche
- ❌ Socio che diventa docente = migrazione dati complessa
- ❌ Esterno che diventa socio = duplicazione
- ❌ Modifiche anagrafiche su più tabelle

---

## Soluzione Proposta: Anagrafica Unificata

### 1. Tabella `persone` (Anagrafica Master)
Contiene **tutti** i dati anagrafici condivisi

```sql
CREATE TABLE persone (
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

CREATE INDEX idx_persone_cognome_nome ON persone(cognome, nome);
CREATE INDEX idx_persone_email ON persone(email);
CREATE INDEX idx_persone_cf ON persone(codice_fiscale);
```

---

### 2. Tabella `soci` (Raccordo Ruoli)
Definisce i **ruoli** di ogni persona nella scuola

```sql
CREATE TABLE soci (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    persona_id INTEGER NOT NULL,
    tipo_socio TEXT NOT NULL CHECK(tipo_socio IN ('socio', 'docente', 'esterno', 'admin')),
    data_inizio DATE NOT NULL,
    data_fine DATE,
    stato TEXT DEFAULT 'attivo' CHECK(stato IN ('attivo', 'sospeso', 'cessato')),
    note_ruolo TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE CASCADE
);

CREATE INDEX idx_soci_persona ON soci(persona_id);
CREATE INDEX idx_soci_tipo ON soci(tipo_socio, stato);
CREATE UNIQUE INDEX idx_soci_persona_tipo_attivo ON soci(persona_id, tipo_socio, stato) 
    WHERE stato = 'attivo';
```

**Esempio Dati:**
```
persona_id | tipo_socio | data_inizio | data_fine | stato
-----------|------------|-------------|-----------|--------
1          | socio    | 2024-09-01  | NULL      | attivo
1          | docente    | 2025-01-01  | NULL      | attivo  
2          | docente    | 2023-01-01  | NULL      | attivo
3          | esterno    | 2025-02-01  | NULL      | attivo
```
👆 La persona_id=1 è sia socio che docente!

---

### 3. Tabelle Specializzate (Dati Ruolo-Specifici)

#### 3a. `soci_socio_dettagli`
Dati specifici per ruolo SOCIO

```sql
CREATE TABLE soci_socio_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    socio_id INTEGER NOT NULL UNIQUE,
    tutore_nome TEXT,
    tutore_telefono TEXT,
    tutore_email TEXT,
    scuola_frequentata TEXT,
    livello_iniziale TEXT,
    obiettivi TEXT,
    note_didattiche TEXT,
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
);
```

#### 3b. `soci_docente_dettagli`
Dati specifici per ruolo DOCENTE

```sql
CREATE TABLE soci_docente_dettagli (
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
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### 3c. `soci_esterno_dettagli`
Dati specifici per ruolo ESTERNO

```sql
CREATE TABLE soci_esterno_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    socio_id INTEGER NOT NULL UNIQUE,
    tipo_esterno TEXT DEFAULT 'privato' CHECK(tipo_esterno IN ('privato', 'band', 'associazione', 'azienda')),
    nome_band TEXT,
    partita_iva TEXT,
    codice_sdi TEXT,
    pec TEXT,
    note_commerciali TEXT,
    FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
);
```

---

### 4. Adeguamento Tabelle Esistenti

#### Tabella `lezioni`
```sql
-- PRIMA (riferimenti separati)
socio_id → soci(id)
docente_id → docenti(id)

-- DOPO (riferimento unificato a soci)
socio_socio_id → soci(id) WHERE tipo_socio='socio'
docente_socio_id → soci(id) WHERE tipo_socio='docente'
```

#### Tabella `eventi_calendario`
```sql
-- PRIMA
socio_id → soci(id)
docente_id → docenti(id)
socio_occasionale_id → soci_occasionali(id)

-- DOPO (riferimento unico a soci)
socio_id → soci(id)  -- Può essere socio, docente o esterno
```

#### Tabella `assenze`
```sql
-- PRIMA
socio_id → soci(id)
docente_id → docenti(id)

-- DOPO
socio_socio_id → soci(id) WHERE tipo_socio='socio'
docente_socio_id → soci(id) WHERE tipo_socio='docente'
```

---

## Vantaggi della Soluzione

### ✅ 1. Flessibilità Totale
```sql
-- Docente che segue lezioni come socio
INSERT INTO soci (persona_id, tipo_socio, data_inizio) VALUES (1, 'socio', '2024-09-01');
INSERT INTO soci (persona_id, tipo_socio, data_inizio) VALUES (1, 'docente', '2023-01-01');

-- Lezione dove docente ID=1 insegna all'socio ID=1 (stessa persona!)
INSERT INTO lezioni (socio_socio_id, docente_socio_id, ...) 
VALUES (
    (SELECT id FROM soci WHERE persona_id=1 AND tipo_socio='socio'),
    (SELECT id FROM soci WHERE persona_id=1 AND tipo_socio='docente'),
    ...
);
```

### ✅ 2. Storicizzazione Ruoli
```sql
-- Persona passa da socio a docente
-- Il vecchio ruolo socio viene cessato, non cancellato
UPDATE soci SET stato='cessato', data_fine='2025-06-30' 
WHERE persona_id=5 AND tipo_socio='socio';

INSERT INTO soci (persona_id, tipo_socio, data_inizio, stato) 
VALUES (5, 'docente', '2025-09-01', 'attivo');
```

### ✅ 3. Anagrafica Unica
```sql
-- Modifica email? Un solo UPDATE
UPDATE persone SET email='nuovo@email.it' WHERE id=1;

-- Tutti i ruoli vedono subito la modifica
SELECT p.email, s.tipo_socio 
FROM persone p 
JOIN soci s ON p.id = s.persona_id 
WHERE p.id=1;
```

### ✅ 4. Query Semplificate
```sql
-- Tutte le persone che sono sia soci che docenti
SELECT p.*, 
    GROUP_CONCAT(s.tipo_socio) as ruoli
FROM persone p
JOIN soci s ON p.id = s.persona_id
WHERE s.stato = 'attivo'
GROUP BY p.id
HAVING COUNT(DISTINCT s.tipo_socio) > 1;

-- Tutti i contatti (email/telefono) indipendentemente dal ruolo
SELECT DISTINCT email, telefono FROM persone WHERE attiva=1;
```

### ✅ 5. Integrità Referenziale
- Una persona eliminata → tutti i suoi ruoli vengono eliminati (CASCADE)
- Un ruolo eliminato → i dettagli specifici vengono eliminati (CASCADE)
- Nessun dato orfano

---

## Vista Helper per Compatibilità

Per facilitare la migrazione e mantenere compatibilità con codice esistente:

```sql
-- Vista che emula tabella soci
CREATE VIEW v_soci AS
SELECT 
    s.id as id,
    p.cognome,
    p.nome,
    p.email,
    p.telefono,
    p.data_nascita,
    p.indirizzo,
    p.note_anagrafiche as note,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at,
    ad.tutore_nome,
    ad.tutore_telefono
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_socio_dettagli ad ON s.id = ad.socio_id
WHERE s.tipo_socio = 'socio';

-- Vista che emula tabella docenti
CREATE VIEW v_docenti AS
SELECT 
    s.id as id,
    p.cognome,
    p.nome,
    p.email,
    p.telefono,
    dd.specializzazioni,
    p.note_anagrafiche as note,
    dd.user_id,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_docente_dettagli dd ON s.id = dd.socio_id
WHERE s.tipo_socio = 'docente';

-- Vista che emula tabella soci_occasionali
CREATE VIEW v_soci_occasionali AS
SELECT 
    s.id as id,
    p.cognome,
    p.nome,
    p.email,
    p.telefono,
    ed.tipo_esterno as tipo,
    ed.partita_iva,
    p.codice_fiscale,
    p.note_anagrafiche as note,
    CASE WHEN s.stato='attivo' THEN 1 ELSE 0 END as attivo,
    s.created_at
FROM persone p
JOIN soci s ON p.id = s.persona_id
LEFT JOIN soci_esterno_dettagli ed ON s.id = ed.socio_id
WHERE s.tipo_socio = 'esterno';
```

---

## Migration Plan

### Fase 1: Creazione Nuove Tabelle
1. Creare `persone`
2. Creare `soci`
3. Creare `soci_*_dettagli`

### Fase 2: Migrazione Dati
```sql
-- Migra soci
INSERT INTO persone (cognome, nome, email, telefono, data_nascita, indirizzo, note_anagrafiche, attiva, created_at)
SELECT cognome, nome, email, telefono, data_nascita, indirizzo, note, attivo, created_at
FROM soci;

INSERT INTO soci (persona_id, tipo_socio, data_inizio, stato)
SELECT p.id, 'socio', a.created_at, CASE WHEN a.attivo=1 THEN 'attivo' ELSE 'cessato' END
FROM soci a
JOIN persone p ON a.cognome=p.cognome AND a.nome=p.nome AND a.email=p.email;

-- Migra docenti
-- Migra soci_occasionali
-- ...
```

### Fase 3: Aggiornamento Foreign Keys
- Backup database
- Update FK in `lezioni`, `eventi_calendario`, `assenze`, etc.
- Test completo

### Fase 4: Creazione Viste Compatibilità
- Creare viste `v_soci`, `v_docenti`, `v_soci_occasionali`
- Test codice esistente con viste

### Fase 5: Drop Vecchie Tabelle
- Solo dopo test completi
- Drop `soci`, `docenti`, `soci_occasionali`

---

## Considerazioni

### Pro ✅
- Massima flessibilità ruoli multipli
- Anagrafica unica = nessuna duplicazione
- Storicizzazione completa
- Scalabilità futura (nuovi ruoli)
- Integrità referenziale garantita

### Contro ⚠️
- Migrazione dati complessa
- Query leggermente più complesse (più JOIN)
- Codice esistente da refactorare
- Formazione team su nuova struttura

### Quando Fare il Refactoring?
- ✅ **ADESSO**: Prima di crescere troppo
- ✅ Se ci sono già casi di docenti-soci
- ✅ Per facilitare gestione multi-ruolo futura
- ❌ NON ADESSO: Se sistema già in produzione con molti dati

---

## Conclusione

**Raccomandazione: SÌ, refactoring consigliato**

La struttura unificata è molto più professionale e flessibile. Il costo iniziale della migrazione viene ampiamente ripagato dalla facilità di gestione futura.

Se il sistema non è ancora in produzione con molti dati, **è il momento ideale per fare questo refactoring**.

---

**Autore:** Proposta Refactoring  
**Data:** 13/02/2026  
**Versione:** 1.0