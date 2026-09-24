-- ============================================
-- MIGRATION DATI: Da Vecchie Tabelle a Anagrafica Unificata
-- Data: 13/02/2026
-- Versione: 3.0.0
-- ============================================
-- IMPORTANTE: Eseguire SOLO dopo migration_anagrafica_unificata.sql
-- ============================================

BEGIN TRANSACTION;

-- ============================================
-- STEP 1: MIGRAZIONE ALLIEVI
-- ============================================

-- 1a. Migra anagrafica allievi in persone
INSERT INTO persone (
    cognome, nome, email, telefono, data_nascita, indirizzo,
    note_anagrafiche, attiva, created_at
)
SELECT 
    cognome, nome, email, telefono, data_nascita, indirizzo,
    note, attivo, created_at
FROM allievi
WHERE NOT EXISTS (
    SELECT 1 FROM persone p 
    WHERE p.cognome = allievi.cognome 
    AND p.nome = allievi.nome
    AND COALESCE(p.email, '') = COALESCE(allievi.email, '')
);

-- 1b. Crea record soci per allievi
INSERT INTO soci (persona_id, tipo_socio, data_inizio, stato, created_at)
SELECT 
    p.id,
    'allievo',
    COALESCE(a.created_at, date('now')),
    CASE WHEN a.attivo = 1 THEN 'attivo' ELSE 'cessato' END,
    a.created_at
FROM allievi a
JOIN persone p ON 
    p.cognome = a.cognome 
    AND p.nome = a.nome
    AND COALESCE(p.email, '') = COALESCE(a.email, '')
WHERE NOT EXISTS (
    SELECT 1 FROM soci s 
    WHERE s.persona_id = p.id 
    AND s.tipo_socio = 'allievo'
);

-- 1c. Aggiungi dettagli allievo (se presenti campi specifici)
-- Per ora non abbiamo campi specifici allievo nelle vecchie tabelle
-- Ma creiamo i record vuoti per futura espansione
INSERT INTO soci_allievo_dettagli (socio_id)
SELECT s.id
FROM soci s
WHERE s.tipo_socio = 'allievo'
AND NOT EXISTS (
    SELECT 1 FROM soci_allievo_dettagli sad 
    WHERE sad.socio_id = s.id
);

-- ============================================
-- STEP 2: MIGRAZIONE DOCENTI
-- ============================================

-- 2a. Migra anagrafica docenti in persone
INSERT INTO persone (
    cognome, nome, email, telefono,
    note_anagrafiche, attiva, created_at
)
SELECT 
    cognome, nome, email, telefono,
    note, attivo, created_at
FROM docenti
WHERE NOT EXISTS (
    SELECT 1 FROM persone p 
    WHERE p.cognome = docenti.cognome 
    AND p.nome = docenti.nome
    AND COALESCE(p.email, '') = COALESCE(docenti.email, '')
);

-- 2b. Crea record soci per docenti
INSERT INTO soci (persona_id, tipo_socio, data_inizio, stato, created_at)
SELECT 
    p.id,
    'docente',
    COALESCE(d.created_at, date('now')),
    CASE WHEN d.attivo = 1 THEN 'attivo' ELSE 'cessato' END,
    d.created_at
FROM docenti d
JOIN persone p ON 
    p.cognome = d.cognome 
    AND p.nome = d.nome
    AND COALESCE(p.email, '') = COALESCE(d.email, '')
WHERE NOT EXISTS (
    SELECT 1 FROM soci s 
    WHERE s.persona_id = p.id 
    AND s.tipo_socio = 'docente'
);

-- 2c. Aggiungi dettagli docente
INSERT INTO soci_docente_dettagli (
    socio_id, user_id, specializzazioni
)
SELECT 
    s.id,
    d.user_id,
    d.specializzazioni
FROM docenti d
JOIN persone p ON 
    p.cognome = d.cognome 
    AND p.nome = d.nome
    AND COALESCE(p.email, '') = COALESCE(d.email, '')
JOIN soci s ON s.persona_id = p.id AND s.tipo_socio = 'docente'
WHERE NOT EXISTS (
    SELECT 1 FROM soci_docente_dettagli sdd 
    WHERE sdd.socio_id = s.id
);

-- ============================================
-- STEP 3: MIGRAZIONE SOCI OCCASIONALI
-- ============================================

-- 3a. Migra anagrafica esterni in persone
INSERT INTO persone (
    cognome, nome, email, telefono, codice_fiscale,
    note_anagrafiche, attiva, created_at
)
SELECT 
    cognome, nome, email, telefono, codice_fiscale,
    note, attivo, created_at
FROM soci_occasionali
WHERE NOT EXISTS (
    SELECT 1 FROM persone p 
    WHERE p.cognome = soci_occasionali.cognome 
    AND p.nome = soci_occasionali.nome
    AND COALESCE(p.email, '') = COALESCE(soci_occasionali.email, '')
);

-- 3b. Crea record soci per esterni
INSERT INTO soci (persona_id, tipo_socio, data_inizio, stato, created_at)
SELECT 
    p.id,
    'esterno',
    COALESCE(so.created_at, date('now')),
    CASE WHEN so.attivo = 1 THEN 'attivo' ELSE 'cessato' END,
    so.created_at
FROM soci_occasionali so
JOIN persone p ON 
    p.cognome = so.cognome 
    AND p.nome = so.nome
    AND COALESCE(p.email, '') = COALESCE(so.email, '')
WHERE NOT EXISTS (
    SELECT 1 FROM soci s 
    WHERE s.persona_id = p.id 
    AND s.tipo_socio = 'esterno'
);

-- 3c. Aggiungi dettagli esterni
INSERT INTO soci_esterno_dettagli (
    socio_id, tipo_esterno, partita_iva
)
SELECT 
    s.id,
    so.tipo,
    so.partita_iva
FROM soci_occasionali so
JOIN persone p ON 
    p.cognome = so.cognome 
    AND p.nome = so.nome
    AND COALESCE(p.email, '') = COALESCE(so.email, '')
JOIN soci s ON s.persona_id = p.id AND s.tipo_socio = 'esterno'
WHERE NOT EXISTS (
    SELECT 1 FROM soci_esterno_dettagli sed 
    WHERE sed.socio_id = s.id
);

-- ============================================
-- STEP 4: VERIFICA E REPORT
-- ============================================

-- Crea tabella temporanea per report migrazione
CREATE TEMP TABLE migration_report (
    descrizione TEXT,
    count INTEGER
);

INSERT INTO migration_report VALUES ('Totale persone migrate', (SELECT COUNT(*) FROM persone));
INSERT INTO migration_report VALUES ('Totale ruoli soci creati', (SELECT COUNT(*) FROM soci));
INSERT INTO migration_report VALUES ('Allievi migrati', (SELECT COUNT(*) FROM soci WHERE tipo_socio='allievo'));
INSERT INTO migration_report VALUES ('Docenti migrati', (SELECT COUNT(*) FROM soci WHERE tipo_socio='docente'));
INSERT INTO migration_report VALUES ('Esterni migrati', (SELECT COUNT(*) FROM soci WHERE tipo_socio='esterno'));
INSERT INTO migration_report VALUES ('Persone con ruoli multipli', (SELECT COUNT(*) FROM v_persone_multirolo));

-- Verifica consistenza
INSERT INTO migration_report VALUES ('Allievi vecchia tabella', (SELECT COUNT(*) FROM allievi));
INSERT INTO migration_report VALUES ('Docenti vecchia tabella', (SELECT COUNT(*) FROM docenti));
INSERT INTO migration_report VALUES ('Esterni vecchia tabella', (SELECT COUNT(*) FROM soci_occasionali));

-- Mostra report
SELECT '=== REPORT MIGRAZIONE ===' as report;
SELECT * FROM migration_report;

-- Verifica persone senza ruoli (anomalie)
SELECT '=== ANOMALIE: Persone senza ruoli ===' as anomalie;
SELECT p.id, p.cognome, p.nome, p.email
FROM persone p
WHERE NOT EXISTS (SELECT 1 FROM soci s WHERE s.persona_id = p.id)
LIMIT 10;

-- Verifica ruoli senza dettagli
SELECT '=== ANOMALIE: Ruoli senza dettagli ===' as anomalie;
SELECT s.id, s.tipo_socio, p.cognome, p.nome
FROM soci s
JOIN persone p ON s.persona_id = p.id
WHERE s.tipo_socio = 'allievo' 
AND NOT EXISTS (SELECT 1 FROM soci_allievo_dettagli sad WHERE sad.socio_id = s.id)
LIMIT 10;

COMMIT;

-- ============================================
-- MIGRATION DATI COMPLETATA
-- ============================================
-- Prossimi step:
-- 1. Verificare i report sopra
-- 2. Se OK, eseguire migration_update_fk.sql
-- 3. Testare sistema con nuove tabelle
-- 4. Solo dopo test completi, drop vecchie tabelle
-- ============================================