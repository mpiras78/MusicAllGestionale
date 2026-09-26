-- MusicAll production baseline (SQLite 3.37+)
-- Generated from the technical/functional analysis of 2026-09-26.
-- Create a NEW database with this file; do not execute it on musicall.sqlite.

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

BEGIN TRANSACTION;

CREATE TABLE dati_associazione (
    singleton INTEGER PRIMARY KEY CHECK (singleton = 1),
    nome_scuola TEXT NOT NULL,
    indirizzo TEXT,
    cap TEXT,
    citta TEXT,
    provincia TEXT,
    telefono_principale TEXT,
    telefono_secondario TEXT,
    email_amministrativa TEXT,
    email_pagamenti TEXT,
    iban TEXT,
    intestatario_conto TEXT,
    partita_iva TEXT,
    codice_fiscale TEXT,
    descrizione_ricevute TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE persone (
    id INTEGER PRIMARY KEY,
    cognome TEXT NOT NULL,
    nome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    cellulare TEXT,
    data_nascita TEXT,
    luogo_nascita TEXT,
    codice_fiscale TEXT UNIQUE,
    indirizzo TEXT,
    cap TEXT,
    citta TEXT,
    provincia TEXT,
    nazione TEXT NOT NULL DEFAULT 'Italia',
    note_anagrafiche TEXT,
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_persone_cognome_nome ON persone(cognome, nome);
CREATE INDEX idx_persone_email ON persone(email);

CREATE TABLE ruoli_utente (
    codice TEXT PRIMARY KEY,
    descrizione TEXT NOT NULL
);
INSERT INTO ruoli_utente (codice, descrizione) VALUES
    ('admin', 'Amministratore'),
    ('docente', 'Docente'),
    ('segreteria', 'Segreteria');

CREATE TABLE utenti (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER UNIQUE,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    email_accesso TEXT,
    attivo INTEGER NOT NULL DEFAULT 1 CHECK (attivo IN (0, 1)),
    force_password_change INTEGER NOT NULL DEFAULT 0 CHECK (force_password_change IN (0, 1)),
    last_login_at TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE SET NULL
);

CREATE TABLE utenti_ruoli (
    utente_id INTEGER NOT NULL,
    ruolo_codice TEXT NOT NULL,
    PRIMARY KEY (utente_id, ruolo_codice),
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (ruolo_codice) REFERENCES ruoli_utente(codice) ON DELETE RESTRICT
);

CREATE TABLE allievi (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER NOT NULL UNIQUE,
    tutore_nome TEXT,
    tutore_cognome TEXT,
    tutore_telefono TEXT,
    tutore_email TEXT,
    tutore_relazione TEXT,
    scuola_frequentata TEXT,
    livello_iniziale TEXT,
    obiettivi TEXT,
    note_didattiche TEXT,
    stato TEXT NOT NULL DEFAULT 'attivo' CHECK (stato IN ('attivo', 'sospeso', 'cessato')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT
);

CREATE TABLE docenti (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER NOT NULL UNIQUE,
    utente_id INTEGER UNIQUE,
    specializzazioni TEXT,
    cv TEXT,
    titoli_studio TEXT,
    esperienze TEXT,
    disponibilita TEXT,
    tariffa_oraria_centesimi INTEGER CHECK (tariffa_oraria_centesimi >= 0),
    note_professionali TEXT,
    stato TEXT NOT NULL DEFAULT 'attivo' CHECK (stato IN ('attivo', 'sospeso', 'cessato')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE SET NULL
);

CREATE TABLE esterni (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER NOT NULL UNIQUE,
    tipo TEXT NOT NULL DEFAULT 'privato' CHECK (tipo IN ('privato', 'band', 'associazione', 'azienda')),
    nome_band TEXT,
    partita_iva TEXT,
    codice_sdi TEXT,
    pec TEXT,
    note_commerciali TEXT,
    stato TEXT NOT NULL DEFAULT 'attivo' CHECK (stato IN ('attivo', 'sospeso', 'cessato')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT
);

CREATE TABLE relazioni_familiari (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER NOT NULL,
    familiare_id INTEGER NOT NULL,
    relazione TEXT,
    data_inizio TEXT NOT NULL,
    data_fine TEXT,
    stato TEXT NOT NULL DEFAULT 'attiva' CHECK (stato IN ('attiva', 'revocata')),
    motivo_revoca TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (persona_id <> familiare_id),
    CHECK (data_fine IS NULL OR data_fine >= data_inizio),
    UNIQUE (persona_id, familiare_id, data_inizio),
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (familiare_id) REFERENCES persone(id) ON DELETE RESTRICT
);
CREATE INDEX idx_relazioni_familiari_familiare ON relazioni_familiari(familiare_id, stato);

CREATE TABLE anni_accademici (
    id INTEGER PRIMARY KEY,
    codice TEXT NOT NULL UNIQUE,
    data_inizio TEXT NOT NULL,
    data_fine TEXT NOT NULL,
    attivo INTEGER NOT NULL DEFAULT 1 CHECK (attivo IN (0, 1)),
    CHECK (data_fine > data_inizio)
);

CREATE TABLE materie (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL UNIQUE,
    categoria TEXT NOT NULL DEFAULT 'strumento'
        CHECK (categoria IN ('strumento', 'canto', 'teoria', 'insieme', 'laboratorio', 'custom')),
    descrizione TEXT,
    durata_standard_minuti INTEGER NOT NULL DEFAULT 45 CHECK (durata_standard_minuti > 0),
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE docenti_materie (
    docente_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    livello TEXT CHECK (livello IN ('principiante', 'intermedio', 'avanzato', 'tutti')),
    note TEXT,
    PRIMARY KEY (docente_id, materia_id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT
);

CREATE TABLE aule (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL UNIQUE,
    descrizione TEXT,
    capienza INTEGER CHECK (capienza IS NULL OR capienza > 0),
    attrezzature TEXT,
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1)),
    ordine_visualizzazione INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tipologie_laboratorio (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL UNIQUE,
    descrizione TEXT,
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1))
);

CREATE TABLE piani_corso (
    id INTEGER PRIMARY KEY,
    codice TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    durata_lezione_minuti INTEGER NOT NULL CHECK (durata_lezione_minuti > 0),
    costo_mensile_centesimi INTEGER NOT NULL CHECK (costo_mensile_centesimi >= 0),
    tipologia_laboratorio_id INTEGER,
    descrizione TEXT,
    attivo INTEGER NOT NULL DEFAULT 1 CHECK (attivo IN (0, 1)),
    ordinamento INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipologia_laboratorio_id) REFERENCES tipologie_laboratorio(id) ON DELETE RESTRICT
);

CREATE TABLE laboratori (
    id INTEGER PRIMARY KEY,
    tipologia_laboratorio_id INTEGER NOT NULL,
    anno_accademico_id INTEGER NOT NULL,
    nome TEXT NOT NULL,
    docente_id INTEGER NOT NULL,
    aula_id INTEGER NOT NULL,
    giorno_settimana INTEGER NOT NULL CHECK (giorno_settimana BETWEEN 1 AND 7),
    ora_inizio TEXT NOT NULL,
    ora_fine TEXT NOT NULL,
    max_partecipanti INTEGER CHECK (max_partecipanti IS NULL OR max_partecipanti > 0),
    attivo INTEGER NOT NULL DEFAULT 1 CHECK (attivo IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (ora_fine > ora_inizio),
    FOREIGN KEY (tipologia_laboratorio_id) REFERENCES tipologie_laboratorio(id) ON DELETE RESTRICT,
    FOREIGN KEY (anno_accademico_id) REFERENCES anni_accademici(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE RESTRICT
);

CREATE TABLE iscrizioni_annuali (
    id INTEGER PRIMARY KEY,
    allievo_id INTEGER NOT NULL,
    anno_accademico_id INTEGER NOT NULL,
    numero_tessera TEXT NOT NULL UNIQUE,
    importo_centesimi INTEGER NOT NULL CHECK (importo_centesimi >= 0),
    stato TEXT NOT NULL DEFAULT 'in_attesa'
        CHECK (stato IN ('in_attesa', 'pagata', 'annullata')),
    data_iscrizione TEXT NOT NULL,
    note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (allievo_id, anno_accademico_id),
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE RESTRICT,
    FOREIGN KEY (anno_accademico_id) REFERENCES anni_accademici(id) ON DELETE RESTRICT
);

CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY,
    allievo_id INTEGER NOT NULL,
    anno_accademico_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    piano_corso_id INTEGER NOT NULL,
    data_inizio TEXT NOT NULL,
    data_fine TEXT,
    stato TEXT NOT NULL DEFAULT 'attiva' CHECK (stato IN ('attiva', 'sospesa', 'conclusa', 'annullata')),
    is_pacchetto INTEGER NOT NULL DEFAULT 0 CHECK (is_pacchetto IN (0, 1)),
    numero_lezioni_pacchetto INTEGER CHECK (numero_lezioni_pacchetto IS NULL OR numero_lezioni_pacchetto > 0),
    quota_iscrizione_centesimi INTEGER NOT NULL DEFAULT 0 CHECK (quota_iscrizione_centesimi >= 0),
    sconto_centesimi INTEGER NOT NULL DEFAULT 0 CHECK (sconto_centesimi >= 0),
    note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (data_fine IS NULL OR data_fine >= data_inizio),
    CHECK (
        (is_pacchetto = 0 AND numero_lezioni_pacchetto IS NULL)
        OR
        (is_pacchetto = 1 AND numero_lezioni_pacchetto IS NOT NULL)
    ),
    UNIQUE (allievo_id, anno_accademico_id, materia_id, data_inizio),
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE RESTRICT,
    FOREIGN KEY (anno_accademico_id) REFERENCES anni_accademici(id) ON DELETE RESTRICT,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id, materia_id) REFERENCES docenti_materie(docente_id, materia_id) ON DELETE RESTRICT,
    FOREIGN KEY (piano_corso_id) REFERENCES piani_corso(id) ON DELETE RESTRICT
);
CREATE INDEX idx_iscrizioni_docente_stato ON iscrizioni(docente_id, stato);
CREATE INDEX idx_iscrizioni_allievo_stato ON iscrizioni(allievo_id, stato);

CREATE TABLE partecipanti_laboratorio (
    laboratorio_id INTEGER NOT NULL,
    allievo_id INTEGER NOT NULL,
    data_iscrizione TEXT NOT NULL,
    data_fine TEXT,
    stato TEXT NOT NULL DEFAULT 'attivo' CHECK (stato IN ('attivo', 'ritirato')),
    note TEXT,
    PRIMARY KEY (laboratorio_id, allievo_id, data_iscrizione),
    CHECK (data_fine IS NULL OR data_fine >= data_iscrizione),
    FOREIGN KEY (laboratorio_id) REFERENCES laboratori(id) ON DELETE RESTRICT,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE RESTRICT
);

CREATE TABLE tipologie_evento (
    id INTEGER PRIMARY KEY,
    codice TEXT NOT NULL UNIQUE,
    categoria TEXT NOT NULL CHECK (categoria IN ('lezione', 'prenotazione', 'recupero', 'laboratorio', 'altro')),
    nome TEXT NOT NULL,
    descrizione TEXT,
    colore_bg TEXT,
    colore_border TEXT,
    icona TEXT,
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1)),
    ordinamento INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE eventi_calendario (
    id INTEGER PRIMARY KEY,
    tipologia_id INTEGER NOT NULL,
    iscrizione_id INTEGER,
    laboratorio_id INTEGER,
    aula_id INTEGER NOT NULL,
    docente_id INTEGER,
    materia_id INTEGER,
    ricorrente INTEGER NOT NULL DEFAULT 0 CHECK (ricorrente IN (0, 1)),
    giorno_settimana INTEGER CHECK (giorno_settimana BETWEEN 1 AND 7),
    data_evento TEXT,
    data_inizio TEXT,
    data_fine TEXT,
    ora_inizio TEXT NOT NULL,
    ora_fine TEXT NOT NULL,
    titolo TEXT,
    descrizione TEXT,
    note TEXT,
    stato TEXT NOT NULL DEFAULT 'pianificato'
        CHECK (stato IN ('bozza', 'pianificato', 'confermato', 'annullato', 'completato')),
    created_by INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (ora_fine > ora_inizio),
    CHECK (
        (ricorrente = 0 AND data_evento IS NOT NULL AND giorno_settimana IS NULL)
        OR
        (ricorrente = 1 AND data_evento IS NULL AND giorno_settimana IS NOT NULL AND data_inizio IS NOT NULL)
    ),
    CHECK (data_fine IS NULL OR data_inizio IS NULL OR data_fine >= data_inizio),
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE RESTRICT,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE RESTRICT,
    FOREIGN KEY (laboratorio_id) REFERENCES laboratori(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES utenti(id) ON DELETE SET NULL
);
CREATE INDEX idx_eventi_aula_data ON eventi_calendario(aula_id, data_evento, ora_inizio);
CREATE INDEX idx_eventi_docente_data ON eventi_calendario(docente_id, data_evento, ora_inizio);
CREATE INDEX idx_eventi_iscrizione ON eventi_calendario(iscrizione_id);

CREATE TABLE utilizzi_lezioni_custom (
    id INTEGER PRIMARY KEY,
    iscrizione_id INTEGER NOT NULL,
    evento_id INTEGER UNIQUE,
    data_lezione TEXT NOT NULL,
    ora_inizio TEXT NOT NULL,
    ora_fine TEXT NOT NULL,
    docente_id INTEGER NOT NULL,
    aula_id INTEGER,
    note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (ora_fine > ora_inizio),
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE RESTRICT,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE RESTRICT
);
CREATE INDEX idx_utilizzi_custom_iscrizione ON utilizzi_lezioni_custom(iscrizione_id, data_lezione);

CREATE TABLE partecipanti_evento (
    evento_id INTEGER NOT NULL,
    persona_id INTEGER NOT NULL,
    ruolo TEXT NOT NULL DEFAULT 'partecipante'
        CHECK (ruolo IN ('partecipante', 'allievo', 'docente', 'richiedente')),
    stato TEXT NOT NULL DEFAULT 'confermato'
        CHECK (stato IN ('invitato', 'confermato', 'assente', 'annullato')),
    note TEXT,
    PRIMARY KEY (evento_id, persona_id),
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE CASCADE,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT
);

CREATE TABLE chiusure_attivita (
    id INTEGER PRIMARY KEY,
    data_chiusura TEXT NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'chiusura_scuola',
    descrizione TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (data_chiusura, tipo)
);

CREATE TABLE assenze (
    id INTEGER PRIMARY KEY,
    evento_id INTEGER NOT NULL,
    persona_id INTEGER NOT NULL,
    tipo TEXT NOT NULL CHECK (tipo IN ('allievo', 'docente')),
    motivo TEXT,
    necessita_recupero INTEGER NOT NULL DEFAULT 1 CHECK (necessita_recupero IN (0, 1)),
    minuti_da_recuperare INTEGER CHECK (minuti_da_recuperare IS NULL OR minuti_da_recuperare > 0),
    stato TEXT NOT NULL DEFAULT 'registrata'
        CHECK (stato IN ('registrata', 'recupero_proposto', 'recuperata', 'annullata')),
    note TEXT,
    created_by INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (evento_id, persona_id),
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE RESTRICT,
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL
);

CREATE TABLE recuperi (
    id INTEGER PRIMARY KEY,
    assenza_id INTEGER NOT NULL UNIQUE,
    evento_recupero_id INTEGER NOT NULL UNIQUE,
    stato TEXT NOT NULL DEFAULT 'proposto'
        CHECK (stato IN ('proposto', 'confermato', 'rifiutato', 'annullato', 'completato')),
    confermato_da_utente_id INTEGER,
    data_conferma TEXT,
    motivo_rifiuto TEXT,
    annullato_da_utente_id INTEGER,
    data_annullamento TEXT,
    motivo_annullamento TEXT,
    note_segreteria TEXT,
    created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assenza_id) REFERENCES assenze(id) ON DELETE RESTRICT,
    FOREIGN KEY (evento_recupero_id) REFERENCES eventi_calendario(id) ON DELETE RESTRICT,
    FOREIGN KEY (confermato_da_utente_id) REFERENCES utenti(id) ON DELETE SET NULL,
    FOREIGN KEY (annullato_da_utente_id) REFERENCES utenti(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE RESTRICT
);

CREATE TABLE configurazioni_tariffarie (
    id INTEGER PRIMARY KEY,
    anno_accademico_id INTEGER NOT NULL UNIQUE,
    quota_associativa_centesimi INTEGER NOT NULL CHECK (quota_associativa_centesimi >= 0),
    note TEXT,
    attiva INTEGER NOT NULL DEFAULT 1 CHECK (attiva IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anno_accademico_id) REFERENCES anni_accademici(id) ON DELETE RESTRICT
);

CREATE TABLE tariffe_materie (
    id INTEGER PRIMARY KEY,
    configurazione_tariffaria_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    piano_corso_id INTEGER,
    importo_mensile_centesimi INTEGER NOT NULL CHECK (importo_mensile_centesimi >= 0),
    data_inizio_validita TEXT NOT NULL,
    data_fine_validita TEXT,
    CHECK (data_fine_validita IS NULL OR data_fine_validita >= data_inizio_validita),
    UNIQUE (configurazione_tariffaria_id, materia_id, piano_corso_id, data_inizio_validita),
    FOREIGN KEY (configurazione_tariffaria_id) REFERENCES configurazioni_tariffarie(id) ON DELETE RESTRICT,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (piano_corso_id) REFERENCES piani_corso(id) ON DELETE RESTRICT
);

CREATE TABLE metodi_pagamento (
    id INTEGER PRIMARY KEY,
    codice TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    richiede_riferimento INTEGER NOT NULL DEFAULT 0 CHECK (richiede_riferimento IN (0, 1)),
    attivo INTEGER NOT NULL DEFAULT 1 CHECK (attivo IN (0, 1)),
    ordinamento INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE tipi_addebito (
    codice TEXT PRIMARY KEY,
    nome TEXT NOT NULL,
    richiede_iscrizione INTEGER NOT NULL DEFAULT 0 CHECK (richiede_iscrizione IN (0, 1)),
    richiede_periodo INTEGER NOT NULL DEFAULT 0 CHECK (richiede_periodo IN (0, 1))
);
INSERT INTO tipi_addebito (codice, nome, richiede_iscrizione, richiede_periodo) VALUES
    ('quota_associativa', 'Quota associativa annuale', 0, 0),
    ('retta', 'Retta corso', 1, 1),
    ('prenotazione', 'Prenotazione sala', 0, 0),
    ('extra', 'Addebito extra', 0, 0),
    ('materiale', 'Materiale didattico', 0, 0),
    ('saggio', 'Partecipazione saggio', 0, 0);

CREATE TABLE addebiti (
    id INTEGER PRIMARY KEY,
    debitore_persona_id INTEGER NOT NULL,
    tipo_codice TEXT NOT NULL,
    iscrizione_id INTEGER,
    iscrizione_annuale_id INTEGER,
    evento_id INTEGER,
    periodo_riferimento TEXT,
    descrizione TEXT NOT NULL,
    importo_centesimi INTEGER NOT NULL CHECK (importo_centesimi >= 0),
    scadenza TEXT,
    stato TEXT NOT NULL DEFAULT 'aperto' CHECK (stato IN ('bozza', 'aperto', 'parziale', 'saldato', 'annullato')),
    numero_documento TEXT UNIQUE,
    created_by INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (
        (iscrizione_id IS NOT NULL) + (iscrizione_annuale_id IS NOT NULL) + (evento_id IS NOT NULL) <= 1
    ),
    FOREIGN KEY (debitore_persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (tipo_codice) REFERENCES tipi_addebito(codice) ON DELETE RESTRICT,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE RESTRICT,
    FOREIGN KEY (iscrizione_annuale_id) REFERENCES iscrizioni_annuali(id) ON DELETE RESTRICT,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL
);
CREATE INDEX idx_addebiti_debitore_stato ON addebiti(debitore_persona_id, stato, scadenza);

CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY,
    pagatore_persona_id INTEGER NOT NULL,
    metodo_pagamento_id INTEGER NOT NULL,
    importo_centesimi INTEGER NOT NULL CHECK (importo_centesimi > 0),
    data_pagamento TEXT NOT NULL,
    riferimento_transazione TEXT,
    numero_ricevuta TEXT UNIQUE,
    stato TEXT NOT NULL DEFAULT 'registrato' CHECK (stato IN ('registrato', 'annullato', 'rimborsato')),
    note TEXT,
    registrato_da INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pagatore_persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (metodo_pagamento_id) REFERENCES metodi_pagamento(id) ON DELETE RESTRICT,
    FOREIGN KEY (registrato_da) REFERENCES utenti(id) ON DELETE SET NULL
);

CREATE TABLE allocazioni_pagamento (
    pagamento_id INTEGER NOT NULL,
    addebito_id INTEGER NOT NULL,
    importo_centesimi INTEGER NOT NULL CHECK (importo_centesimi > 0),
    PRIMARY KEY (pagamento_id, addebito_id),
    FOREIGN KEY (pagamento_id) REFERENCES pagamenti(id) ON DELETE RESTRICT,
    FOREIGN KEY (addebito_id) REFERENCES addebiti(id) ON DELETE RESTRICT
);
CREATE INDEX idx_allocazioni_addebito ON allocazioni_pagamento(addebito_id);

CREATE TABLE note (
    id INTEGER PRIMARY KEY,
    persona_id INTEGER,
    evento_id INTEGER,
    aula_id INTEGER,
    titolo TEXT,
    contenuto TEXT NOT NULL,
    priorita TEXT NOT NULL DEFAULT 'media' CHECK (priorita IN ('bassa', 'media', 'alta', 'urgente')),
    data_scadenza TEXT,
    completata INTEGER NOT NULL DEFAULT 0 CHECK (completata IN (0, 1)),
    created_by INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK ((persona_id IS NOT NULL) + (evento_id IS NOT NULL) + (aula_id IS NOT NULL) <= 1),
    FOREIGN KEY (persona_id) REFERENCES persone(id) ON DELETE RESTRICT,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL
);

CREATE TABLE activation_tokens (
    id INTEGER PRIMARY KEY,
    utente_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    used_at TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);
CREATE INDEX idx_activation_tokens_valid ON activation_tokens(utente_id, expires_at, used_at);

CREATE TABLE password_reset_tokens (
    id INTEGER PRIMARY KEY,
    utente_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    used_at TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

CREATE TABLE rate_limit_log (
    id INTEGER PRIMARY KEY,
    identifier TEXT NOT NULL,
    action TEXT NOT NULL,
    metadata TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_rate_limit_identifier_action ON rate_limit_log(identifier, action, created_at);

CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY,
    utente_id INTEGER,
    azione TEXT NOT NULL,
    entita_tipo TEXT,
    entita_id INTEGER,
    descrizione TEXT,
    ip_address TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE SET NULL
);
CREATE INDEX idx_activity_log_entity ON activity_log(entita_tipo, entita_id, created_at);

CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY,
    utente_id INTEGER,
    azione TEXT NOT NULL,
    tabella_nome TEXT NOT NULL,
    record_id INTEGER,
    valore_precedente TEXT,
    valore_successivo TEXT,
    dettagli TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE SET NULL
);
CREATE INDEX idx_audit_log_record ON audit_log(tabella_nome, record_id, created_at);

CREATE TABLE batch_runs (
    id INTEGER PRIMARY KEY,
    tipo TEXT NOT NULL,
    eseguito_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    stato TEXT NOT NULL CHECK (stato IN ('successo', 'errore', 'parziale')),
    elementi_processati INTEGER NOT NULL DEFAULT 0 CHECK (elementi_processati >= 0),
    elementi_falliti INTEGER NOT NULL DEFAULT 0 CHECK (elementi_falliti >= 0),
    dettagli TEXT
);

COMMIT;
