-- =============================================
-- MIGRATION: Auditing e Sicurezza
-- Aggiunge colonne di tracciamento a tutte le tabelle
-- =============================================

-- 1. Tabella per reset password
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_reset_token ON password_reset_tokens(token, expires_at);
CREATE INDEX IF NOT EXISTS idx_reset_user ON password_reset_tokens(user_id);

-- 2. Aggiungi colonne auditing alla tabella users (se non esistono)
-- Nota: SQLite non supporta ALTER COLUMN, quindi verifichiamo prima

-- 3. Aggiungi colonne auditing a docenti
ALTER TABLE docenti ADD COLUMN updated_at DATETIME;
ALTER TABLE docenti ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- 4. Aggiungi colonne auditing ad allievi  
ALTER TABLE allievi ADD COLUMN updated_at DATETIME;
ALTER TABLE allievi ADD COLUMN updated_by INTEGER REFERENCES users(id);
ALTER TABLE allievi ADD COLUMN created_by INTEGER REFERENCES users(id);

-- 5. Aggiungi colonne auditing ad aule
ALTER TABLE aule ADD COLUMN created_at DATETIME DEFAULT (datetime('now','localtime'));
ALTER TABLE aule ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE aule ADD COLUMN updated_at DATETIME;
ALTER TABLE aule ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- 6. Aggiungi colonne auditing a materie
ALTER TABLE materie ADD COLUMN created_at DATETIME DEFAULT (datetime('now','localtime'));
ALTER TABLE materie ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE materie ADD COLUMN updated_at DATETIME;
ALTER TABLE materie ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- 7. Aggiungi colonne auditing a lezioni
ALTER TABLE lezioni ADD COLUMN updated_at DATETIME;
ALTER TABLE lezioni ADD COLUMN updated_by INTEGER REFERENCES users(id);
ALTER TABLE lezioni ADD COLUMN created_by INTEGER REFERENCES users(id);

-- 8. Aggiungi colonne auditing a docenti_materie
ALTER TABLE docenti_materie ADD COLUMN created_at DATETIME DEFAULT (datetime('now','localtime'));
ALTER TABLE docenti_materie ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE docenti_materie ADD COLUMN updated_at DATETIME;
ALTER TABLE docenti_materie ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- 9. Aggiungi colonne auditing a laboratori
ALTER TABLE laboratori ADD COLUMN created_at DATETIME DEFAULT (datetime('now','localtime'));
ALTER TABLE laboratori ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE laboratori ADD COLUMN updated_at DATETIME;
ALTER TABLE laboratori ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- 10. Aggiungi colonne auditing a laboratori_partecipanti
ALTER TABLE laboratori_partecipanti ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE laboratori_partecipanti ADD COLUMN updated_at DATETIME;
ALTER TABLE laboratori_partecipanti ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- =============================================
-- ISTRUZIONI ESECUZIONE:
-- sqlite3 database/musicall.sqlite < database/migration_auditing.sql
-- =============================================