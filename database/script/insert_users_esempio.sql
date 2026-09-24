-- =============================================
-- UTENTI DI ESEMPIO
-- Password per tutti: password123
-- =============================================

-- Utente Segreteria
INSERT INTO users (username, password, email, role, active) VALUES
('segreteria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'segreteria@musicall.it', 'segreteria', 1);

-- Utente Docente (esempio generico)
INSERT INTO users (username, password, email, role, active) VALUES
('docente.prova', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'docente@musicall.it', 'docente', 1);

-- Utente Docente collegato a MARCANTE (se esiste in tabella docenti)
-- Nota: Questo verrà collegato tramite interfaccia web

-- =============================================
-- ISTRUZIONI:
-- 1. Esegui questo script: sqlite3 database/musicall.sqlite < database/insert_users_esempio.sql
-- 2. Login con:
--    - Username: segreteria / Password: password123
--    - Username: docente.prova / Password: password123
-- 3. Usa interfaccia web per collegare docenti esistenti agli utenti
-- =============================================