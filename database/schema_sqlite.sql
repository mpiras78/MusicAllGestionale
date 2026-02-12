-- ============================================
-- MUSICALL - Database Schema SQLite
-- Sistema Gestione Scuola di Musica
-- ============================================

-- Tabella Utenti
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    role TEXT DEFAULT 'segreteria' CHECK(role IN ('admin', 'docente', 'segreteria')),
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    last_login DATETIME,
    active INTEGER DEFAULT 1
);

-- Tabella Docenti
CREATE TABLE IF NOT EXISTS docenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cognome TEXT NOT NULL,
    nome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    specializzazioni TEXT,
    note TEXT,
    user_id INTEGER,
    attivo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_docenti_cognome_nome ON docenti(cognome, nome);

-- Tabella Allievi
CREATE TABLE IF NOT EXISTS allievi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cognome TEXT NOT NULL,
    nome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    data_nascita DATE,
    indirizzo TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime'))
);
CREATE INDEX IF NOT EXISTS idx_allievi_cognome_nome ON allievi(cognome, nome);
CREATE INDEX IF NOT EXISTS idx_allievi_email ON allievi(email);

-- Tabella Aule/Sale
CREATE TABLE IF NOT EXISTS aule (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    descrizione TEXT,
    capienza INTEGER,
    attrezzature TEXT,
    attiva INTEGER DEFAULT 1,
    ordine_visualizzazione INTEGER DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_aule_ordine ON aule(ordine_visualizzazione);

-- Tabella Materie/Corsi
CREATE TABLE IF NOT EXISTS materie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    categoria TEXT DEFAULT 'strumento' CHECK(categoria IN ('strumento', 'canto', 'teoria', 'insieme', 'laboratorio', 'custom')),
    descrizione TEXT,
    durata_standard INTEGER DEFAULT 45,
    attiva INTEGER DEFAULT 1
);

-- Tabella Relazione Docenti-Materie (many-to-many)
CREATE TABLE IF NOT EXISTS docenti_materie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    docente_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    livello TEXT CHECK(livello IN ('principiante', 'intermedio', 'avanzato', 'tutti')),
    note TEXT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    UNIQUE(docente_id, materia_id)
);
CREATE INDEX IF NOT EXISTS idx_docenti_materie_docente ON docenti_materie(docente_id);
CREATE INDEX IF NOT EXISTS idx_docenti_materie_materia ON docenti_materie(materia_id);

-- Tabella Slot Orari Template
CREATE TABLE IF NOT EXISTS slot_orari (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_slot_orario ON slot_orari(ora_inizio, ora_fine);

-- Tabella Lezioni Programmate
CREATE TABLE IF NOT EXISTS lezioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    aula_id INTEGER NOT NULL,
    giorno_settimana TEXT NOT NULL CHECK(giorno_settimana IN ('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')),
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    tipo TEXT DEFAULT 'regolare' CHECK(tipo IN ('regolare', 'custom', 'recupero', 'laboratorio')),
    attiva INTEGER DEFAULT 1,
    data_inizio DATE,
    data_fine DATE,
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_lezioni_giorno ON lezioni(giorno_settimana);
CREATE INDEX IF NOT EXISTS idx_lezioni_orario ON lezioni(ora_inizio, ora_fine);
CREATE INDEX IF NOT EXISTS idx_lezioni_docente ON lezioni(docente_id);
CREATE INDEX IF NOT EXISTS idx_lezioni_allievo ON lezioni(allievo_id);

-- Tabella Assenze
CREATE TABLE IF NOT EXISTS assenze (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lezione_id INTEGER,
    allievo_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    data_assenza DATE NOT NULL,
    tipo TEXT NOT NULL CHECK(tipo IN ('allievo', 'docente')),
    motivo TEXT,
    da_recuperare INTEGER DEFAULT 1,
    recuperata INTEGER DEFAULT 0,
    data_recupero DATE,
    recupero_lezione_id INTEGER,
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    FOREIGN KEY (lezione_id) REFERENCES lezioni(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (recupero_lezione_id) REFERENCES lezioni(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_assenze_data ON assenze(data_assenza);
CREATE INDEX IF NOT EXISTS idx_assenze_recupero ON assenze(recuperata, data_recupero);

-- Tabella Lezioni Custom/Una tantum
CREATE TABLE IF NOT EXISTS lezioni_custom (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    data_lezione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INTEGER,
    numero_lezione INTEGER,
    totale_lezioni INTEGER,
    costo REAL,
    pagata INTEGER DEFAULT 0,
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_lezioni_custom_data ON lezioni_custom(data_lezione);
CREATE INDEX IF NOT EXISTS idx_lezioni_custom_allievo ON lezioni_custom(allievo_id);
CREATE INDEX IF NOT EXISTS idx_lezioni_custom_docente ON lezioni_custom(docente_id);

-- Tabella Laboratori/Musica d'Insieme
CREATE TABLE IF NOT EXISTS laboratori (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    descrizione TEXT,
    docente_id INTEGER NOT NULL,
    giorno_settimana TEXT NOT NULL CHECK(giorno_settimana IN ('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')),
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INTEGER NOT NULL,
    max_partecipanti INTEGER,
    attivo INTEGER DEFAULT 1,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE
);

-- Tabella Partecipanti Laboratori
CREATE TABLE IF NOT EXISTS laboratori_partecipanti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    laboratorio_id INTEGER NOT NULL,
    allievo_id INTEGER NOT NULL,
    data_iscrizione DATE DEFAULT (date('now','localtime')),
    attivo INTEGER DEFAULT 1,
    FOREIGN KEY (laboratorio_id) REFERENCES laboratori(id) ON DELETE CASCADE,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    UNIQUE(laboratorio_id, allievo_id)
);

-- Tabella Note/Promemoria
CREATE TABLE IF NOT EXISTS note (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo TEXT NOT NULL CHECK(tipo IN ('generale', 'allievo', 'docente', 'aula')),
    riferimento_id INTEGER,
    titolo TEXT,
    contenuto TEXT NOT NULL,
    priorita TEXT DEFAULT 'media' CHECK(priorita IN ('bassa', 'media', 'alta', 'urgente')),
    data_scadenza DATE,
    completata INTEGER DEFAULT 0,
    created_by INTEGER,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_note_tipo ON note(tipo, riferimento_id);
CREATE INDEX IF NOT EXISTS idx_note_scadenza ON note(data_scadenza, completata);

-- Tabella Log Attività
CREATE TABLE IF NOT EXISTS activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    entity_type TEXT,
    entity_id INTEGER,
    description TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_log(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_entity ON activity_log(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_log(created_at);

-- ============================================
-- SISTEMA EVENTI, CALENDARIO E PAGAMENTI
-- ============================================

-- Tabella Tipologie Evento
CREATE TABLE IF NOT EXISTS tipologie_evento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria TEXT NOT NULL CHECK(categoria IN ('lezione', 'prenotazione')),
    codice TEXT UNIQUE NOT NULL,
    nome TEXT NOT NULL,
    descrizione TEXT,
    colore_bg TEXT DEFAULT '#ffffff',
    colore_border TEXT DEFAULT '#000000',
    icona TEXT,
    attiva INTEGER DEFAULT 1,
    ordine_visualizzazione INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT (datetime('now','localtime'))
);
CREATE INDEX IF NOT EXISTS idx_tipologie_categoria ON tipologie_evento(categoria, attiva);
CREATE INDEX IF NOT EXISTS idx_tipologie_codice ON tipologie_evento(codice);

-- Tabella Soci Occasionali/Esterni
CREATE TABLE IF NOT EXISTS soci_occasionali (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cognome TEXT NOT NULL,
    email TEXT,
    telefono TEXT,
    tipo TEXT DEFAULT 'privato' CHECK(tipo IN ('privato', 'band', 'associazione', 'altro')),
    partita_iva TEXT,
    codice_fiscale TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    updated_at DATETIME
);
CREATE INDEX IF NOT EXISTS idx_soci_occasionali_email ON soci_occasionali(email);
CREATE INDEX IF NOT EXISTS idx_soci_occasionali_telefono ON soci_occasionali(telefono);
CREATE INDEX IF NOT EXISTS idx_soci_occasionali_nome ON soci_occasionali(cognome, nome);

-- Tabella Iscrizioni Mensili
CREATE TABLE IF NOT EXISTS iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    allievo_id INTEGER NOT NULL,
    mese INTEGER NOT NULL CHECK(mese BETWEEN 1 AND 12),
    anno INTEGER NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    stato TEXT DEFAULT 'attiva' CHECK(stato IN ('attiva', 'sospesa', 'conclusa')),
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE(allievo_id, mese, anno)
);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_allievo ON iscrizioni(allievo_id);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_periodo ON iscrizioni(anno, mese);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_stato ON iscrizioni(stato);

-- Tabella Eventi Calendario (Unificata)
CREATE TABLE IF NOT EXISTS eventi_calendario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipologia_id INTEGER NOT NULL,
    ricorrente INTEGER DEFAULT 0,
    giorno_settimana TEXT CHECK(giorno_settimana IN ('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')),
    data_evento DATE,
    data_inizio DATE,
    data_fine DATE,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INTEGER NOT NULL,
    docente_id INTEGER,
    materia_id INTEGER,
    allievo_id INTEGER,
    socio_occasionale_id INTEGER,
    iscrizione_id INTEGER,
    titolo TEXT,
    descrizione TEXT,
    note TEXT,
    attivo INTEGER DEFAULT 1,
    confermato INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    updated_at DATETIME,
    updated_by INTEGER,
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE RESTRICT,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE SET NULL,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_occasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CHECK ((ricorrente = 0 AND data_evento IS NOT NULL AND giorno_settimana IS NULL) OR (ricorrente = 1 AND data_evento IS NULL AND giorno_settimana IS NOT NULL)),
    CHECK ((allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL) OR (allievo_id IS NULL AND socio_occasionale_id IS NULL))
);
CREATE INDEX IF NOT EXISTS idx_eventi_tipologia ON eventi_calendario(tipologia_id, attivo);
CREATE INDEX IF NOT EXISTS idx_eventi_data ON eventi_calendario(data_evento);
CREATE INDEX IF NOT EXISTS idx_eventi_giorno ON eventi_calendario(giorno_settimana, ricorrente);
CREATE INDEX IF NOT EXISTS idx_eventi_aula ON eventi_calendario(aula_id);
CREATE INDEX IF NOT EXISTS idx_eventi_docente ON eventi_calendario(docente_id);
CREATE INDEX IF NOT EXISTS idx_eventi_allievo ON eventi_calendario(allievo_id);
CREATE INDEX IF NOT EXISTS idx_eventi_socio ON eventi_calendario(socio_occasionale_id);
CREATE INDEX IF NOT EXISTS idx_eventi_iscrizione ON eventi_calendario(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_eventi_periodo ON eventi_calendario(data_inizio, data_fine);

-- Tabella Listini Prezzi
CREATE TABLE IF NOT EXISTS listini_prezzi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipologia_id INTEGER NOT NULL,
    destinatario TEXT NOT NULL CHECK(destinatario IN ('allievo_iscritto', 'docente', 'socio_occasionale', 'altro')),
    prezzo_orario REAL,
    prezzo_forfait REAL,
    data_inizio_validita DATE NOT NULL,
    data_fine_validita DATE,
    attivo INTEGER DEFAULT 1,
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (tipologia_id) REFERENCES tipologie_evento(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_listini_tipologia ON listini_prezzi(tipologia_id, attivo);
CREATE INDEX IF NOT EXISTS idx_listini_destinatario ON listini_prezzi(destinatario);
CREATE INDEX IF NOT EXISTS idx_listini_validita ON listini_prezzi(data_inizio_validita, data_fine_validita);

-- Tabella Pagamenti
CREATE TABLE IF NOT EXISTS pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo TEXT NOT NULL CHECK(tipo IN ('iscrizione_mensile', 'prenotazione_sala', 'altro')),
    iscrizione_id INTEGER,
    evento_id INTEGER,
    allievo_id INTEGER,
    socio_occasionale_id INTEGER,
    importo_totale REAL NOT NULL,
    importo_pagato REAL DEFAULT 0,
    importo_residuo REAL,
    stato TEXT DEFAULT 'da_pagare' CHECK(stato IN ('da_pagare', 'parzialmente_pagato', 'pagato', 'annullato')),
    data_emissione DATE NOT NULL DEFAULT (date('now')),
    data_scadenza DATE,
    data_pagamento DATE,
    modalita_pagamento TEXT CHECK(modalita_pagamento IN ('contanti', 'bonifico', 'carta', 'paypal', 'altro')),
    numero_fattura TEXT,
    numero_ricevuta TEXT,
    note TEXT,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    created_by INTEGER,
    updated_at DATETIME,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE SET NULL,
    FOREIGN KEY (socio_occasionale_id) REFERENCES soci_occasionali(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CHECK (iscrizione_id IS NOT NULL OR evento_id IS NOT NULL),
    CHECK ((allievo_id IS NOT NULL AND socio_occasionale_id IS NULL) OR (allievo_id IS NULL AND socio_occasionale_id IS NOT NULL))
);
CREATE INDEX IF NOT EXISTS idx_pagamenti_tipo ON pagamenti(tipo, stato);
CREATE INDEX IF NOT EXISTS idx_pagamenti_iscrizione ON pagamenti(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_evento ON pagamenti(evento_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_allievo ON pagamenti(allievo_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_socio ON pagamenti(socio_occasionale_id);
CREATE INDEX IF NOT EXISTS idx_pagamenti_scadenza ON pagamenti(data_scadenza, stato);

-- Tabella Dettagli Iscrizioni
CREATE TABLE IF NOT EXISTS iscrizioni_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    iscrizione_id INTEGER NOT NULL,
    evento_id INTEGER NOT NULL,
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    costo_mensile REAL,
    note TEXT,
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventi_calendario(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE RESTRICT,
    UNIQUE(iscrizione_id, evento_id)
);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_dettagli_iscrizione ON iscrizioni_dettagli(iscrizione_id);
CREATE INDEX IF NOT EXISTS idx_iscrizioni_dettagli_evento ON iscrizioni_dettagli(evento_id);

-- VIEW Calendario Unificato
CREATE VIEW IF NOT EXISTS v_calendario_unificato AS
SELECT 
    e.id,
    e.ricorrente,
    e.giorno_settimana,
    e.data_evento,
    e.data_inizio,
    e.data_fine,
    e.ora_inizio,
    e.ora_fine,
    t.categoria as tipo_evento,
    t.codice as tipologia_codice,
    t.nome as tipologia_nome,
    t.colore_bg,
    t.colore_border,
    t.icona,
    a.nome as aula,
    a.id as aula_id,
    d.cognome || ' ' || d.nome as docente,
    d.id as docente_id,
    COALESCE(al.cognome || ' ' || al.nome, so.cognome || ' ' || so.nome) as partecipante,
    CASE 
        WHEN e.allievo_id IS NOT NULL THEN 'allievo'
        WHEN e.socio_occasionale_id IS NOT NULL THEN 'esterno'
        ELSE NULL
    END as tipo_partecipante,
    e.allievo_id,
    e.socio_occasionale_id,
    m.nome as materia,
    m.id as materia_id,
    e.confermato,
    e.attivo,
    i.id as iscrizione_id,
    i.mese as iscrizione_mese,
    i.anno as iscrizione_anno,
    p.id as pagamento_id,
    p.stato as stato_pagamento,
    p.importo_totale,
    p.importo_pagato
FROM eventi_calendario e
INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
LEFT JOIN aule a ON e.aula_id = a.id
LEFT JOIN docenti d ON e.docente_id = d.id
LEFT JOIN allievi al ON e.allievo_id = al.id
LEFT JOIN soci_occasionali so ON e.socio_occasionale_id = so.id
LEFT JOIN materie m ON e.materia_id = m.id
LEFT JOIN iscrizioni i ON e.iscrizione_id = i.id
LEFT JOIN pagamenti p ON (p.evento_id = e.id OR p.iscrizione_id = e.iscrizione_id)
WHERE e.attivo = 1;

-- ============================================
-- DATI INIZIALI
-- ============================================

-- Inserimento Aule
INSERT INTO aule (nome, descrizione, ordine_visualizzazione, attiva) VALUES
('AULA MIDI', 'Aula MIDI - Registrazione e produzione', 1, 1),
('AULA PIANO', 'Aula Pianoforte', 2, 1),
('AULA MAGNA', 'Aula Magna - Multi-purpose', 3, 1),
('SALA JAZZ', 'Sala Jazz - Ensemble', 4, 1),
('SALA POP', 'Sala Pop - Band e gruppi', 5, 1),
('SALA ROCK', 'Sala Rock - Band e batteria', 6, 1);

-- Inserimento Materie
INSERT INTO materie (nome, categoria, durata_standard, attiva) VALUES
('Chitarra', 'strumento', 45, 1),
('Pianoforte', 'strumento', 45, 1),
('Batteria', 'strumento', 45, 1),
('Basso', 'strumento', 45, 1),
('Violino', 'strumento', 45, 1),
('Ukulele', 'strumento', 30, 1),
('Canto Pop', 'canto', 45, 1),
('Canto Jazz', 'canto', 45, 1),
('Canto Metal', 'canto', 45, 1),
('Canto Lirico', 'canto', 45, 1),
('Beatbox', 'strumento', 45, 1),
('Rap e Beatmaking', 'strumento', 60, 1),
('Musica d''Insieme', 'insieme', 60, 1),
('Solfeggio', 'teoria', 45, 1),
('Teoria Musicale', 'teoria', 45, 1),
('Laboratorio Baby', 'laboratorio', 45, 1),
('Laboratorio Ritmico', 'laboratorio', 45, 1),
('Piano e Canto', 'custom', 60, 1);

-- Slot Orari Standard
INSERT INTO slot_orari (ora_inizio, ora_fine) VALUES
('09:15:00', '10:00:00'),
('10:00:00', '10:45:00'),
('10:45:00', '11:30:00'),
('11:30:00', '12:15:00'),
('12:15:00', '13:00:00'),
('13:00:00', '13:45:00'),
('13:45:00', '14:30:00'),
('14:30:00', '15:15:00'),
('15:15:00', '16:00:00'),
('16:00:00', '16:45:00'),
('16:45:00', '17:30:00'),
('17:30:00', '18:15:00'),
('18:15:00', '19:00:00'),
('19:00:00', '19:45:00'),
('19:45:00', '20:30:00'),
('20:30:00', '21:15:00'),
('21:15:00', '22:00:00');

-- Utente Admin (password: admin123)
INSERT INTO users (username, password, email, role, active) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@musicall.it', 'admin', 1);

-- Tipologie Eventi
INSERT INTO tipologie_evento (categoria, codice, nome, descrizione, colore_bg, colore_border, icona, ordine_visualizzazione) VALUES
('lezione', 'LEZ_REGOLARE', 'Lezione Regolare', 'Lezione settimanale ricorrente', '#fff5f0', '#ff6b35', 'bi-music-note-beamed', 1),
('lezione', 'LEZ_CUSTOM', 'Lezione Custom', 'Lezione una tantum', '#e3f2fd', '#2196f3', 'bi-star', 2),
('lezione', 'LEZ_LABORATORIO', 'Laboratorio', 'Musica d''insieme/laboratorio', '#f3e5f5', '#9c27b0', 'bi-people', 3),
('lezione', 'LEZ_RECUPERO', 'Recupero', 'Lezione di recupero', '#e8f5e9', '#4caf50', 'bi-arrow-repeat', 4),
('prenotazione', 'PREN_SALA_ALLIEVI', 'Prenotazione Sala Allievi', 'Prenotazione sala per allievi iscritti (gratuita)', '#e8f5e9', '#4caf50', 'bi-door-open', 11),
('prenotazione', 'PREN_DOCENTE', 'Prenotazione Docente', 'Prenotazione sala da parte di docenti', '#fff9c4', '#fdd835', 'bi-person-badge', 12),
('prenotazione', 'PREN_ESTERNO', 'Prenotazione Esterno', 'Prenotazione sala da soci occasionali/esterni', '#ffebee', '#ef5350', 'bi-calendar-event', 13);

-- Listini Prezzi Iniziali
INSERT INTO listini_prezzi (tipologia_id, destinatario, prezzo_orario, data_inizio_validita) 
SELECT id, 'allievo_iscritto', 0.00, '2026-01-01' FROM tipologie_evento WHERE codice='PREN_SALA_ALLIEVI';

INSERT INTO listini_prezzi (tipologia_id, destinatario, prezzo_orario, data_inizio_validita) 
SELECT id, 'docente', 15.00, '2026-01-01' FROM tipologie_evento WHERE codice='PREN_DOCENTE';

INSERT INTO listini_prezzi (tipologia_id, destinatario, prezzo_orario, data_inizio_validita) 
SELECT id, 'socio_occasionale', 30.00, '2026-01-01' FROM tipologie_evento WHERE codice='PREN_ESTERNO';
