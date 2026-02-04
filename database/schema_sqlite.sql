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