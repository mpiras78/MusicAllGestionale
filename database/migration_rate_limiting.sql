-- Migration: Rate Limiting Table
-- Data: 16 Febbraio 2026
-- Descrizione: Tabella per protezione brute force e rate limiting

CREATE TABLE IF NOT EXISTS rate_limit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    identifier VARCHAR(255) NOT NULL,
    action VARCHAR(50) NOT NULL,
    metadata TEXT,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indici per performance
CREATE INDEX IF NOT EXISTS idx_rate_limit_identifier_action 
ON rate_limit_log(identifier, action);

CREATE INDEX IF NOT EXISTS idx_rate_limit_timestamp 
ON rate_limit_log(timestamp);

-- Cleanup automatico vecchi record (esegui periodicamente via cron)
-- DELETE FROM rate_limit_log WHERE timestamp < datetime('now', '-7 days');
