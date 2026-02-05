-- =============================================
-- MIGRATION: Sistema Attivazione Utenti
-- Tabella per token di attivazione account
-- =============================================

-- Tabella per token di attivazione
CREATE TABLE IF NOT EXISTS activation_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    url_token VARCHAR(64) NOT NULL,
    activation_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used INTEGER DEFAULT 0,
    used_at DATETIME,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activation_url_token ON activation_tokens(url_token, expires_at);
CREATE INDEX IF NOT EXISTS idx_activation_code ON activation_tokens(activation_code);
CREATE INDEX IF NOT EXISTS idx_activation_user ON activation_tokens(user_id);

-- Aggiungi colonna per forzare cambio password al primo accesso
ALTER TABLE users ADD COLUMN force_password_change INTEGER DEFAULT 0;

-- =============================================
-- ISTRUZIONI ESECUZIONE:
-- sqlite3 database/musicall.sqlite < database/migration_activation.sql
-- =============================================