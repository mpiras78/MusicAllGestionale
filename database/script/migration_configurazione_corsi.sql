-- =====================================================
-- MIGRATION: Sistema Configurazione Corsi e Laboratori
-- =====================================================

-- 1. Tabella Tipi Corso (Configurazione)
CREATE TABLE IF NOT EXISTS tipi_corso_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL COMMENT 'Es: Base 45min, Standard 60min',
    durata_lezione INT NOT NULL COMMENT 'Minuti: 45 o 60',
    costo_mensile DECIMAL(10,2) NOT NULL COMMENT 'Costo al mese',
    include_laboratorio BOOLEAN DEFAULT FALSE,
    tipo_laboratorio_id INT NULL COMMENT 'Laboratorio incluso',
    descrizione TEXT,
    attivo BOOLEAN DEFAULT TRUE,
    ordine_visualizzazione INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_attivo (attivo),
    INDEX idx_ordine (ordine_visualizzazione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabella Tipi Laboratorio (Configurazione)
CREATE TABLE IF NOT EXISTS tipi_laboratorio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL COMMENT 'Es: Musica d''insieme, Teoria, Piano per cantanti',
    descrizione TEXT,
    docente_id INT NULL COMMENT 'Docente di riferimento',
    giorno_settimana TINYINT NOT NULL COMMENT '1=Lunedì, 7=Domenica',
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    aula_id INT NULL,
    max_partecipanti INT DEFAULT NULL COMMENT 'NULL = illimitato',
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_giorno (giorno_settimana),
    INDEX idx_docente (docente_id),
    INDEX idx_attivo (attivo),
    FOREIGN KEY (docente_id) REFERENCES docenti(id) ON DELETE SET NULL,
    FOREIGN KEY (aula_id) REFERENCES aule(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Aggiungi FK a tipi_corso_config
ALTER TABLE tipi_corso_config 
ADD CONSTRAINT fk_tipo_laboratorio 
FOREIGN KEY (tipo_laboratorio_id) REFERENCES tipi_laboratorio(id) ON DELETE SET NULL;

-- 4. Tabella Pagamenti Mensili
CREATE TABLE IF NOT EXISTS pagamenti_mensili (
    id INT PRIMARY KEY AUTO_INCREMENT,
    iscrizione_id INT NOT NULL,
    mese_riferimento CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    importo DECIMAL(10,2) NOT NULL,
    data_pagamento DATE NULL,
    stato ENUM('non_pagato','pagato','parziale') DEFAULT 'non_pagato',
    metodo_pagamento ENUM('contanti','bonifico','carta','altro') NULL,
    lezioni_generate BOOLEAN DEFAULT FALSE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_iscrizione_mese (iscrizione_id, mese_riferimento),
    INDEX idx_mese (mese_riferimento),
    INDEX idx_stato (stato),
    INDEX idx_data_pagamento (data_pagamento),
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Modifica tabella iscrizioni
ALTER TABLE iscrizioni 
ADD COLUMN IF NOT EXISTS tipo_corso_config_id INT NULL COMMENT 'Riferimento configurazione corso',
ADD COLUMN IF NOT EXISTS anno_scolastico VARCHAR(9) NULL COMMENT '2024/2025',
ADD COLUMN IF NOT EXISTS sconto_fratelli DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS sconto_meta_anno BOOLEAN DEFAULT FALSE,
ADD INDEX idx_tipo_corso (tipo_corso_config_id),
ADD INDEX idx_anno_scolastico (anno_scolastico);

-- 6. Modifica tabella lezioni (template settimanale)
ALTER TABLE lezioni 
ADD COLUMN IF NOT EXISTS iscrizione_id INT NULL COMMENT 'Collegamento iscrizione',
ADD INDEX idx_iscrizione (iscrizione_id);

-- 7. Estendi eventi_calendario per lezioni generate
ALTER TABLE eventi_calendario
ADD COLUMN IF NOT EXISTS lezione_template_id INT NULL COMMENT 'Template lezione settimanale',
ADD COLUMN IF NOT EXISTS pagamento_mensile_id INT NULL COMMENT 'Pagamento che ha generato',
ADD COLUMN IF NOT EXISTS presenza ENUM('presente','assente','giustificato') NULL,
ADD COLUMN IF NOT EXISTS generata_automaticamente BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS tipo_laboratorio_id INT NULL COMMENT 'Se è un laboratorio',
ADD INDEX idx_lezione_template (lezione_template_id),
ADD INDEX idx_pagamento (pagamento_mensile_id),
ADD INDEX idx_tipo_laboratorio (tipo_laboratorio_id);

-- 8. Tabella Partecipanti Laboratorio
CREATE TABLE IF NOT EXISTS laboratorio_partecipanti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_laboratorio_id INT NOT NULL,
    allievo_id INT NOT NULL,
    data_iscrizione DATE NOT NULL,
    attivo BOOLEAN DEFAULT TRUE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_laboratorio_allievo (tipo_laboratorio_id, allievo_id),
    INDEX idx_allievo (allievo_id),
    INDEX idx_attivo (attivo),
    FOREIGN KEY (tipo_laboratorio_id) REFERENCES tipi_laboratorio(id) ON DELETE CASCADE,
    FOREIGN KEY (allievo_id) REFERENCES allievi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Dati iniziali Tipi Laboratorio
INSERT INTO tipi_laboratorio (nome, descrizione, giorno_settimana, ora_inizio, ora_fine, attivo) VALUES
('Musica d''insieme', 'Laboratorio di musica d''insieme', 3, '18:00:00', '19:30:00', TRUE),
('Teoria e Solfeggio', 'Corso di teoria musicale e solfeggio', 2, '17:00:00', '18:00:00', TRUE),
('Pianoforte per Cantanti', 'Corso di pianoforte complementare per cantanti', 4, '16:00:00', '17:00:00', TRUE);

-- 10. Dati iniziali Tipi Corso
INSERT INTO tipi_corso_config (nome, durata_lezione, costo_mensile, include_laboratorio, descrizione, ordine_visualizzazione) VALUES
('Base 45min', 45, 80.00, FALSE, 'Lezione individuale 45 minuti', 1),
('Base 60min', 60, 100.00, FALSE, 'Lezione individuale 60 minuti', 2),
('Standard 45min + Teoria', 45, 95.00, TRUE, 'Lezione 45min + Laboratorio Teoria', 3),
('Standard 60min + Teoria', 60, 115.00, TRUE, 'Lezione 60min + Laboratorio Teoria', 4),
('Extended 45min + Musica Insieme', 45, 105.00, TRUE, 'Lezione 45min + Musica d''insieme', 5),
('Extended 60min + Musica Insieme', 60, 125.00, TRUE, 'Lezione 60min + Musica d''insieme', 6);

-- 11. Vista Calendario Completo (con laboratori)
CREATE OR REPLACE VIEW v_calendario_completo AS
SELECT 
    'lezione' as tipo_evento,
    l.id,
    l.giorno_settimana,
    l.ora_inizio,
    l.ora_fine,
    CONCAT(a.cognome, ' ', a.nome) as allievo,
    CONCAT(d.cognome, ' ', d.nome) as docente,
    m.nome as materia,
    au.nome as aula,
    l.attiva,
    NULL as max_partecipanti
FROM lezioni l
LEFT JOIN allievi a ON l.allievo_id = a.id
LEFT JOIN docenti d ON l.docente_id = d.id
LEFT JOIN materie m ON l.materia_id = m.id
LEFT JOIN aule au ON l.aula_id = au.id

UNION ALL

SELECT 
    'laboratorio' as tipo_evento,
    tl.id,
    tl.giorno_settimana,
    tl.ora_inizio,
    tl.ora_fine,
    tl.nome as allievo,
    CONCAT(d.cognome, ' ', d.nome) as docente,
    tl.nome as materia,
    au.nome as aula,
    tl.attivo as attiva,
    tl.max_partecipanti
FROM tipi_laboratorio tl
LEFT JOIN docenti d ON tl.docente_id = d.id
LEFT JOIN aule au ON tl.aula_id = au.id
WHERE tl.attivo = TRUE;

-- Fine migration
