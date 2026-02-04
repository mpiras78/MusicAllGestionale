-- ============================================
-- MUSICALL - Database Schema
-- Sistema Gestione Scuola di Musica
-- ============================================

-- Tabella Utenti (per autenticazione)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'docente', 'segreteria') DEFAULT 'segreteria',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    active BOOLEAN DEFAULT TRUE
);

-- Tabella Docenti
CREATE TABLE IF NOT EXISTS docenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cognome VARCHAR(50) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    specializzazioni TEXT,
    note TEXT,
    user_id INT,
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cognome_nome (cognome, nome)
);

-- Tabella Allievi
CREATE TABLE IF NOT EXISTS allievi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cognome VARCHAR(50) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    data_nascita DATE,
    indirizzo TEXT,
    note TEXT,
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cognome_nome (cognome, nome),
    INDEX idx_email (email)
);

-- Tabella Aule/Sale
CREATE TABLE IF NOT EXISTS aule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descrizione TEXT,
    capienza INT,
    attrezzature TEXT,
    attiva BOOLEAN DEFAULT TRUE,
    ordine_visualizzazione INT DEFAULT 0,
    INDEX idx_ordine (ordine_visualizzazione)
);

-- Tabella Materie/Corsi
CREATE TABLE IF NOT EXISTS materie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria ENUM('strumento', 'canto', 'teoria', 'insieme', 'laboratorio', 'custom') DEFAULT 'strumento',
    descrizione TEXT,
    durata_standard INT DEFAULT 45 COMMENT 'Durata in minuti',
    attiva BOOLEAN DEFAULT TRUE
);

-- Tabella Relazione Docenti-Materie (many-to-many)
CREATE TABLE IF NOT EXISTS docenti_materie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    livello ENUM('principiante', 'intermedio', 'avanzato', 'tutti') DEFAULT NULL,
    note TEXT,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    UNIQUE KEY unique_docente_materia (docente_id, materia_id),
    INDEX idx_docente (docente_id),
    INDEX idx_materia (materia_id)
);

-- Tabella Slot Orari Template
CREATE TABLE IF NOT EXISTS slot_orari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    INDEX idx_orario (ora_inizio, ora_fine)
);

-- Tabella Lezioni Programmate
CREATE TABLE IF NOT EXISTS lezioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allievo_id INT NOT NULL,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    aula_id INT NOT NULL,
    giorno_settimana ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica') NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    tipo ENUM('regolare', 'custom', 'recupero', 'laboratorio') DEFAULT 'regolare',
    attiva BOOLEAN DEFAULT TRUE,
    data_inizio DATE,
    data_fine DATE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE,
    INDEX idx_giorno (giorno_settimana),
    INDEX idx_orario (ora_inizio, ora_fine),
    INDEX idx_docente (docente_id),
    INDEX idx_allievo (allievo_id)
);

-- Tabella Assenze
CREATE TABLE IF NOT EXISTS assenze (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lezione_id INT,
    allievo_id INT NOT NULL,
    docente_id INT NOT NULL,
    data_assenza DATE NOT NULL,
    tipo ENUM('allievo', 'docente') NOT NULL,
    motivo TEXT,
    da_recuperare BOOLEAN DEFAULT TRUE,
    recuperata BOOLEAN DEFAULT FALSE,
    data_recupero DATE NULL,
    recupero_lezione_id INT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (lezione_id) REFERENCES lezioni(id) ON DELETE SET NULL,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (recupero_lezione_id) REFERENCES lezioni(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_data (data_assenza),
    INDEX idx_recupero (recuperata, data_recupero)
);

-- Tabella Lezioni Custom/Una tantum
CREATE TABLE IF NOT EXISTS lezioni_custom (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allievo_id INT NOT NULL,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    data_lezione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INT,
    numero_lezione INT,
    totale_lezioni INT,
    costo DECIMAL(10,2),
    pagata BOOLEAN DEFAULT FALSE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materie(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE SET NULL,
    INDEX idx_data (data_lezione),
    INDEX idx_allievo (allievo_id),
    INDEX idx_docente (docente_id)
);

-- Tabella Laboratori/Musica d'Insieme
CREATE TABLE IF NOT EXISTS laboratori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    docente_id INT NOT NULL,
    giorno_settimana ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica') NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INT NOT NULL,
    max_partecipanti INT,
    attivo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE CASCADE
);

-- Tabella Partecipanti Laboratori
CREATE TABLE IF NOT EXISTS laboratori_partecipanti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laboratorio_id INT NOT NULL,
    allievo_id INT NOT NULL,
    data_iscrizione DATE DEFAULT (CURRENT_DATE),
    attivo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (laboratorio_id) REFERENCES laboratori(id) ON DELETE CASCADE,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_partecipante (laboratorio_id, allievo_id)
);

-- Tabella Note/Promemoria
CREATE TABLE IF NOT EXISTS note (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('generale', 'allievo', 'docente', 'aula') NOT NULL,
    riferimento_id INT,
    titolo VARCHAR(200),
    contenuto TEXT NOT NULL,
    priorita ENUM('bassa', 'media', 'alta', 'urgente') DEFAULT 'media',
    data_scadenza DATE,
    completata BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo, riferimento_id),
    INDEX idx_scadenza (data_scadenza, completata)
);

-- Tabella Log Attività
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

-- ============================================
-- DATI INIZIALI
-- ============================================

-- Inserimento Aule (basato sul file Excel)
INSERT INTO aule (nome, descrizione, ordine_visualizzazione, attiva) VALUES
('AULA MIDI', 'Aula MIDI - Registrazione e produzione', 1, TRUE),
('AULA PIANO', 'Aula Pianoforte', 2, TRUE),
('AULA MAGNA', 'Aula Magna - Multi-purpose', 3, TRUE),
('SALA JAZZ', 'Sala Jazz - Ensemble', 4, TRUE),
('SALA POP', 'Sala Pop - Band e gruppi', 5, TRUE),
('SALA ROCK', 'Sala Rock - Band e batteria', 6, TRUE);

-- Inserimento Materie principali
INSERT INTO materie (nome, categoria, durata_standard, attiva) VALUES
('Chitarra', 'strumento', 45, TRUE),
('Pianoforte', 'strumento', 45, TRUE),
('Batteria', 'strumento', 45, TRUE),
('Basso', 'strumento', 45, TRUE),
('Violino', 'strumento', 45, TRUE),
('Ukulele', 'strumento', 30, TRUE),
('Canto Pop', 'canto', 45, TRUE),
('Canto Jazz', 'canto', 45, TRUE),
('Canto Metal', 'canto', 45, TRUE),
('Canto Lirico', 'canto', 45, TRUE),
('Beatbox', 'strumento', 45, TRUE),
('Rap e Beatmaking', 'strumento', 60, TRUE),
('Musica d''Insieme', 'insieme', 60, TRUE),
('Solfeggio', 'teoria', 45, TRUE),
('Teoria Musicale', 'teoria', 45, TRUE),
('Laboratorio Baby', 'laboratorio', 45, TRUE),
('Laboratorio Ritmico', 'laboratorio', 45, TRUE),
('Piano e Canto', 'custom', 60, TRUE);

-- Slot Orari Standard (dalle 9:15 alle 22:00, slot da 45 minuti)
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

-- Utente Admin di default (password: admin123 - da cambiare!)
INSERT INTO users (username, password, email, role, active) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@musicall.it', 'admin', TRUE);

-- ============================================
-- VISTE UTILI
-- ============================================

-- Vista per calendario settimanale
CREATE OR REPLACE VIEW v_calendario_settimanale AS
SELECT 
    l.id,
    l.giorno_settimana,
    l.ora_inizio,
    l.ora_fine,
    a.nome as aula,
    a.id as aula_id,
    al.cognome as allievo_cognome,
    al.nome as allievo_nome,
    al.id as allievo_id,
    d.cognome as docente_cognome,
    d.nome as docente_nome,
    d.id as docente_id,
    m.nome as materia,
    m.categoria as materia_categoria,
    l.tipo,
    l.note
FROM lezioni l
JOIN allievi al ON l.allievo_id = al.id
JOIN docenti d ON l.docente_id = d.id
JOIN materie m ON l.materia_id = m.id
JOIN aule a ON l.aula_id = a.id
WHERE l.attiva = TRUE
ORDER BY 
    FIELD(l.giorno_settimana, 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'),
    l.ora_inizio,
    a.ordine_visualizzazione;

-- Vista per conteggio assenze
CREATE OR REPLACE VIEW v_statistiche_assenze AS
SELECT 
    al.id as allievo_id,
    al.cognome,
    al.nome,
    COUNT(CASE WHEN ass.tipo = 'allievo' THEN 1 END) as assenze_allievo,
    COUNT(CASE WHEN ass.tipo = 'allievo' AND ass.recuperata = TRUE THEN 1 END) as assenze_recuperate,
    COUNT(CASE WHEN ass.tipo = 'allievo' AND ass.recuperata = FALSE AND ass.da_recuperare = TRUE THEN 1 END) as assenze_da_recuperare
FROM allievi al
LEFT JOIN assenze ass ON al.id = ass.allievo_id
WHERE al.attivo = TRUE
GROUP BY al.id, al.cognome, al.nome;

-- Vista per statistiche docenti
CREATE OR REPLACE VIEW v_statistiche_docenti AS
SELECT 
    d.id as docente_id,
    d.cognome,
    d.nome,
    COUNT(DISTINCT l.id) as totale_lezioni,
    COUNT(DISTINCT l.allievo_id) as totale_allievi,
    COUNT(CASE WHEN ass.tipo = 'docente' THEN 1 END) as assenze_docente
FROM docenti d
LEFT JOIN lezioni l ON d.id = l.docente_id AND l.attiva = TRUE
LEFT JOIN assenze ass ON d.id = ass.docente_id
WHERE d.attivo = TRUE
GROUP BY d.id, d.cognome, d.nome;