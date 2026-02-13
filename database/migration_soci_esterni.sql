-- Migration: Tabella Soci Esterni
-- Data: 2026-02-13
-- Descrizione: Gestione soci esterni/occasionali per prenotazioni sala

CREATE TABLE IF NOT EXISTS soci_esterni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    telefono VARCHAR(20),
    note TEXT,
    attivo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indici per performance
CREATE INDEX IF NOT EXISTS idx_soci_esterni_email ON soci_esterni(email);
CREATE INDEX IF NOT EXISTS idx_soci_esterni_attivo ON soci_esterni(attivo);
CREATE INDEX IF NOT EXISTS idx_soci_esterni_nome_cognome ON soci_esterni(nome, cognome);