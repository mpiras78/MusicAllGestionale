-- ========================================
-- SISTEMA ISCRIZIONI E PAGAMENTI
-- Schema Database con Tabelle Tipologiche
-- ========================================

-- ========================================
-- TABELLE TIPOLOGICHE
-- ========================================

-- Tipi di Pagamento (invece di stringhe hardcoded)
CREATE TABLE tipi_pagamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    codice VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    
    -- Configurazione
    richiede_mese BOOLEAN DEFAULT 0, -- Se true, deve specificare mese_riferimento
    richiede_iscrizione BOOLEAN DEFAULT 0, -- Se true, deve linkare a iscrizione_id
    
    -- Contabilità
    categoria_contabile VARCHAR(50), -- 'RETTA', 'TASSA', 'VENDITA', 'ALTRO'
    
    attivo BOOLEAN DEFAULT 1,
    ordinamento INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed tipi pagamento
INSERT INTO tipi_pagamento (codice, nome, descrizione, richiede_mese, richiede_iscrizione, categoria_contabile, ordinamento) VALUES
('QUOTA_ASSOCIATIVA', 'Quota Associativa Annuale', 'Quota associativa per anno accademico', 0, 0, 'TASSA', 1),
('MENSILE', 'Quota Mensile Lezioni', 'Pagamento mensile per frequenza lezioni', 1, 1, 'RETTA', 2),
('EXTRA', 'Pagamento Extra', 'Pagamento non ricorrente (recuperi, lezioni extra)', 0, 1, 'RETTA', 3),
('MATERIALE', 'Materiale Didattico', 'Acquisto libri, spartiti, metronomi, etc', 0, 0, 'VENDITA', 4),
('SAGGIO', 'Partecipazione Saggio', 'Quota partecipazione a saggi/concerti', 0, 0, 'ALTRO', 5);

-- Metodi di Pagamento (invece di stringhe hardcoded)
CREATE TABLE metodi_pagamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    codice VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    
    -- Configurazione
    richiede_riferimento BOOLEAN DEFAULT 0, -- Se true, campo riferimento obbligatorio
    
    attivo BOOLEAN DEFAULT 1,
    ordinamento INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed metodi pagamento
INSERT INTO metodi_pagamento (codice, nome, richiede_riferimento, ordinamento) VALUES
('CONTANTI', 'Contanti', 0, 1),
('BONIFICO', 'Bonifico Bancario', 1, 2),
('CARTA', 'Carta di Credito', 0, 3),
('POS', 'POS Bancomat', 0, 4),
('PAYPAL', 'PayPal', 1, 5),
('ASSEGNO', 'Assegno', 1, 6);

-- ========================================
-- CONFIGURAZIONE TARIFFE
-- ========================================

CREATE TABLE configurazione_tariffe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    anno_accademico VARCHAR(20) NOT NULL,
    
    -- Quota Associativa
    quota_associativa DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    
    -- Tariffe Lezioni (default, poi override per materia)
    tariffa_individuale DECIMAL(10,2) DEFAULT 120.00, -- al mese
    tariffa_gruppo DECIMAL(10,2) DEFAULT 80.00,
    tariffa_lab DECIMAL(10,2) DEFAULT 60.00,
    
    -- Note
    note TEXT,
    
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(anno_accademico)
);

-- Seed configurazione anno corrente
INSERT INTO configurazione_tariffe (anno_accademico, quota_associativa, tariffa_individuale, tariffa_gruppo, tariffa_lab) VALUES
('2025-2026', 50.00, 120.00, 80.00, 60.00);

-- Tariffe per materia specifica (override)
CREATE TABLE tariffe_materie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    anno_accademico VARCHAR(20) NOT NULL,
    materia_id INTEGER NOT NULL,
    
    tariffa_individuale DECIMAL(10,2),
    tariffa_gruppo DECIMAL(10,2),
    
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    UNIQUE(anno_accademico, materia_id)
);

-- ========================================
-- ISCRIZIONI
-- ========================================

CREATE TABLE iscrizioni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Allievo
    allievo_id INTEGER NOT NULL,
    
    -- Anno Accademico (es: '2025-2026')
    anno_accademico VARCHAR(20) NOT NULL,
    
    -- Corso/Lezioni
    materia_id INTEGER NOT NULL,
    docente_id INTEGER NOT NULL,
    tipo_corso VARCHAR(50) NOT NULL, -- 'individuale', 'gruppo', 'lab'
    
    -- Date
    data_iscrizione DATE NOT NULL,
    data_inizio_corso DATE NOT NULL,
    data_fine_corso DATE,
    
    -- Stato
    stato VARCHAR(20) DEFAULT 'attiva', -- 'attiva', 'sospesa', 'conclusa'
    
    -- Audit
    creato_da INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (materia_id) REFERENCES materie(id),
    FOREIGN KEY (docente_id) REFERENCES docenti(id),
    FOREIGN KEY (creato_da) REFERENCES users(id),
    
    -- Un allievo può avere solo 1 iscrizione attiva per materia/anno
    UNIQUE(allievo_id, materia_id, anno_accademico)
);

CREATE INDEX idx_iscrizioni_allievo ON iscrizioni(allievo_id);
CREATE INDEX idx_iscrizioni_anno ON iscrizioni(anno_accademico);
CREATE INDEX idx_iscrizioni_stato ON iscrizioni(stato);

-- ========================================
-- PAGAMENTI
-- ========================================

CREATE TABLE pagamenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Relazioni
    allievo_id INTEGER NOT NULL,
    iscrizione_id INTEGER, -- NULL se quota associativa o altro non legato a iscrizione
    
    -- Tipo Pagamento (FK a tabella tipologica)
    tipo_pagamento_id INTEGER NOT NULL,
    
    -- Periodo (per mensili)
    anno_accademico VARCHAR(20) NOT NULL,
    mese_riferimento VARCHAR(20), -- 'settembre', 'ottobre', etc
    
    -- Importi
    importo DECIMAL(10,2) NOT NULL,
    sconto DECIMAL(10,2) DEFAULT 0,
    importo_netto DECIMAL(10,2) NOT NULL, -- importo - sconto
    
    -- Flag Quota Associativa
    include_quota_associativa BOOLEAN DEFAULT 0, -- Se true, questo pagamento include anche quota annuale
    importo_quota_associativa DECIMAL(10,2) DEFAULT 0, -- Importo quota inclusa (per split visivo)
    
    -- Dettagli Pagamento
    metodo_pagamento_id INTEGER NOT NULL,
    data_pagamento DATE NOT NULL,
    data_scadenza DATE,
    
    -- Riferimenti
    numero_ricevuta VARCHAR(50),
    riferimento_transazione VARCHAR(100), -- Numero bonifico, ID transazione, etc
    note TEXT,
    
    -- Stato
    stato VARCHAR(20) DEFAULT 'pagato', -- 'pagato', 'parziale', 'in_attesa', 'annullato'
    
    -- Audit
    registrato_da INTEGER NOT NULL, -- User che ha registrato il pagamento
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (allievo_id) REFERENCES allievi(id),
    FOREIGN KEY (iscrizione_id) REFERENCES iscrizioni(id),
    FOREIGN KEY (tipo_pagamento_id) REFERENCES tipi_pagamento(id),
    FOREIGN KEY (metodo_pagamento_id) REFERENCES metodi_pagamento(id),
    FOREIGN KEY (registrato_da) REFERENCES users(id)
);

CREATE INDEX idx_pagamenti_allievo ON pagamenti(allievo_id);
CREATE INDEX idx_pagamenti_tipo ON pagamenti(tipo_pagamento_id);
CREATE INDEX idx_pagamenti_anno ON pagamenti(anno_accademico);
CREATE INDEX idx_pagamenti_mese ON pagamenti(mese_riferimento);
CREATE INDEX idx_pagamenti_data ON pagamenti(data_pagamento);
CREATE INDEX idx_pagamenti_metodo ON pagamenti(metodo_pagamento_id);

-- ========================================
-- QUERY UTILITIES
-- ========================================

-- View: Pagamenti con dettagli tipo e metodo
CREATE VIEW v_pagamenti_dettagliati AS
SELECT 
    p.*,
    tp.codice AS tipo_codice,
    tp.nome AS tipo_nome,
    mp.codice AS metodo_codice,
    mp.nome AS metodo_nome,
    a.nome || ' ' || a.cognome AS allievo_nome,
    i.anno_accademico,
    m.nome AS materia_nome
FROM pagamenti p
LEFT JOIN tipi_pagamento tp ON p.tipo_pagamento_id = tp.id
LEFT JOIN metodi_pagamento mp ON p.metodo_pagamento_id = mp.id
LEFT JOIN allievi a ON p.allievo_id = a.id
LEFT JOIN iscrizioni i ON p.iscrizione_id = i.id
LEFT JOIN materie m ON i.materia_id = m.id;

-- ========================================
-- FUNZIONI PHP EQUIVALENTI
-- ========================================

/*
-- Verifica quota associativa pagata
function verificaQuotaAssociativa($allievoId, $annoAccademico) {
    $tipoQuotaId = TipoPagamento::where('codice', 'QUOTA_ASSOCIATIVA')->value('id');
    
    return !Pagamento::where('allievo_id', $allievoId)
        ->where('anno_accademico', $annoAccademico)
        ->where('tipo_pagamento_id', $tipoQuotaId)
        ->where('stato', 'pagato')
        ->exists();
}

-- Registra pagamento quota associativa
function registraQuotaAssociativa($allievoId, $annoAccademico, $importo, $metodoPagamentoId) {
    $tipoQuotaId = TipoPagamento::where('codice', 'QUOTA_ASSOCIATIVA')->value('id');
    
    return Pagamento::create([
        'allievo_id' => $allievoId,
        'tipo_pagamento_id' => $tipoQuotaId,
        'anno_accademico' => $annoAccademico,
        'importo' => $importo,
        'importo_netto' => $importo,
        'metodo_pagamento_id' => $metodoPagamentoId,
        'data_pagamento' => date('Y-m-d'),
        'stato' => 'pagato',
        'registrato_da' => auth()->id()
    ]);
}

-- Registra pagamento mensile
function registraPagamentoMensile($iscrizioneId, $mese, $importo, $metodoPagamentoId) {
    $iscrizione = Iscrizione::find($iscrizioneId);
    $tipoMensileId = TipoPagamento::where('codice', 'MENSILE')->value('id');
    
    return Pagamento::create([
        'allievo_id' => $iscrizione->allievo_id,
        'iscrizione_id' => $iscrizioneId,
        'tipo_pagamento_id' => $tipoMensileId,
        'anno_accademico' => $iscrizione->anno_accademico,
        'mese_riferimento' => $mese,
        'importo' => $importo,
        'importo_netto' => $importo,
        'metodo_pagamento_id' => $metodoPagamentoId,
        'data_pagamento' => date('Y-m-d'),
        'stato' => 'pagato',
        'registrato_da' => auth()->id()
    ]);
}
*/