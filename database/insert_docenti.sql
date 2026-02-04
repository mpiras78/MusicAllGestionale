-- ============================================
-- INSERT DOCENTI - MusicAll
-- Script generato dal file Excel con relazioni materie
-- ============================================

-- NOTA: Eseguire DOPO aver importato lo schema base con le materie

-- Inserimento docenti
INSERT INTO docenti (cognome, nome, email, telefono, specializzazioni, attivo) VALUES
('D''AURIA', 'Ludovica', NULL, NULL, 'Violino', 1),
('ALBERINI', 'Camillo', 'camilloalberini@gmail.com', NULL, 'Batteria', 1),
('ALIEN', 'Dee', NULL, NULL, 'Beatbox', 1),
('BONIOLI', 'Andrea', 'goemontero@gmail.com', NULL, 'Batteria', 1),
('BUONO', 'Eleonora', 'dysdemonalyric@gmail.com', NULL, 'Canto', 1),
('CICCARELLI', 'Valerio', 'studiovaleinfo@gmail.com', NULL, 'Pianoforte', 1),
('DE CARLI', 'Gaetano', 'tanodrum.gdc@gmail.com', NULL, 'Batteria', 1),
('DI CRESCE', 'Francesca', 'francescadicresce@gmail.com', NULL, 'Canto', 1),
('DI GIORGIO', 'Giuseppe', 'giuseppe.digiorgio91@gmail.com', NULL, 'Canto Metal', 1),
('FERILLI', 'Francesco', 'funkerilli@libero.it', NULL, 'Basso', 1),
('LABANCA', 'Domenico', 'domesatomi82@gmail.com', NULL, 'Pianoforte', 1),
('LORITO', 'Giorgio', 'giorgio.lorito@gmail.com', NULL, 'Canto', 1),
('MARCANTE', 'Daniele', 'danielemarcante@gmail.com', NULL, 'Chitarra, Ukulele', 1),
('MENCHERINI', 'Loris', 'coachcruel84@gmail.com', NULL, 'Rap e Beatmaking', 1),
('NICASTRI', 'Dimitri', 'dimitri.nicastri@libero.it', NULL, 'Batteria', 1),
('OMBRES', 'Danilo', 'dnldanilo87@gmail.com', NULL, 'Batteria', 1),
('PACCHIAROTTI', 'Alice', 'alicepacchiarotti99@gmail.com', NULL, 'Piano e Canto Principianti', 1),
('PICCININI', 'Ludovico', NULL, NULL, 'Chitarra', 1),
('PITINI', 'Simone', 'simonepitini46@gmail.com', NULL, 'Canto', 1),
('SABA', 'Alessandro', 'alexbass.saba@gmail.com', NULL, 'Basso', 1),
('SALVUCCI', 'Fabia', 'fabia.edith@gmail.com', NULL, 'Canto', 1),
('SCARTONI', 'Matteo', 'matteoscartoni@gmail.com', NULL, 'Chitarra', 1),
('TESSITORE', 'Emiliano', 'emilianotessitore@gmail.com', NULL, 'Chitarra', 1),
('URSINI', 'Matteo', 'ursini.352@gmail.com', NULL, 'Chitarra, Ukulele', 1),
('ZINO', 'Stella', 'vivalamusicablues@gmail.com', NULL, 'Canto', 1);

-- ============================================
-- RELAZIONI DOCENTI-MATERIE
-- ============================================
-- Mappatura automatica basata sulle specializzazioni

-- D'AURIA Ludovica -> Violino
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'D''AURIA' AND d.nome = 'Ludovica' AND m.nome = 'Violino';

-- ALBERINI Camillo -> Batteria
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'ALBERINI' AND d.nome = 'Camillo' AND m.nome = 'Batteria';

-- ALIEN Dee -> Beatbox
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'ALIEN' AND d.nome = 'Dee' AND m.nome = 'Beatbox';

-- BONIOLI Andrea -> Batteria
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'BONIOLI' AND d.nome = 'Andrea' AND m.nome = 'Batteria';

-- BUONO Eleonora -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'BUONO' AND d.nome = 'Eleonora' AND m.nome = 'Canto Pop';

-- CICCARELLI Valerio -> Pianoforte
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'CICCARELLI' AND d.nome = 'Valerio' AND m.nome = 'Pianoforte';

-- DE CARLI Gaetano -> Batteria
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'DE CARLI' AND d.nome = 'Gaetano' AND m.nome = 'Batteria';

-- DI CRESCE Francesca -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'DI CRESCE' AND d.nome = 'Francesca' AND m.nome = 'Canto Pop';

-- DI GIORGIO Giuseppe -> Canto Metal
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'DI GIORGIO' AND d.nome = 'Giuseppe' AND m.nome = 'Canto Metal';

-- FERILLI Francesco -> Basso
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'FERILLI' AND d.nome = 'Francesco' AND m.nome = 'Basso';

-- LABANCA Domenico -> Pianoforte
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'LABANCA' AND d.nome = 'Domenico' AND m.nome = 'Pianoforte';

-- LORITO Giorgio -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'LORITO' AND d.nome = 'Giorgio' AND m.nome = 'Canto Pop';

-- MARCANTE Daniele -> Chitarra, Ukulele
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'MARCANTE' AND d.nome = 'Daniele' AND m.nome IN ('Chitarra', 'Ukulele');

-- MENCHERINI Loris -> Rap e Beatmaking
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'MENCHERINI' AND d.nome = 'Loris' AND m.nome = 'Rap e Beatmaking';

-- NICASTRI Dimitri -> Batteria
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'NICASTRI' AND d.nome = 'Dimitri' AND m.nome = 'Batteria';

-- OMBRES Danilo -> Batteria
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'OMBRES' AND d.nome = 'Danilo' AND m.nome = 'Batteria';

-- PACCHIAROTTI Alice -> Pianoforte, Piano e Canto
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'principiante' 
FROM docenti d, materie m 
WHERE d.cognome = 'PACCHIAROTTI' AND d.nome = 'Alice' AND m.nome IN ('Pianoforte', 'Piano e Canto');

-- PICCININI Ludovico -> Chitarra
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'PICCININI' AND d.nome = 'Ludovico' AND m.nome = 'Chitarra';

-- PITINI Simone -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'PITINI' AND d.nome = 'Simone' AND m.nome = 'Canto Pop';

-- SABA Alessandro -> Basso
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'SABA' AND d.nome = 'Alessandro' AND m.nome = 'Basso';

-- SALVUCCI Fabia -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'SALVUCCI' AND d.nome = 'Fabia' AND m.nome = 'Canto Pop';

-- SCARTONI Matteo -> Chitarra
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'SCARTONI' AND d.nome = 'Matteo' AND m.nome = 'Chitarra';

-- TESSITORE Emiliano -> Chitarra
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'TESSITORE' AND d.nome = 'Emiliano' AND m.nome = 'Chitarra';

-- URSINI Matteo -> Chitarra, Ukulele
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'URSINI' AND d.nome = 'Matteo' AND m.nome IN ('Chitarra', 'Ukulele');

-- ZINO Stella -> Canto Pop
INSERT INTO docenti_materie (docente_id, materia_id, livello) 
SELECT d.id, m.id, 'tutti' 
FROM docenti d, materie m 
WHERE d.cognome = 'ZINO' AND d.nome = 'Stella' AND m.nome = 'Canto Pop';

-- ============================================
-- STATISTICHE
-- ============================================
-- Totale docenti inseriti: 25
-- Totale relazioni docenti-materie: 30+
--
-- Docenti per materia:
-- - Batteria: 5 docenti (ALBERINI, BONIOLI, DE CARLI, NICASTRI, OMBRES)
-- - Canto Pop: 6 docenti (BUONO, DI CRESCE, LORITO, PITINI, SALVUCCI, ZINO)
-- - Chitarra: 5 docenti (MARCANTE, PICCININI, SCARTONI, TESSITORE, URSINI)
-- - Pianoforte: 3 docenti (CICCARELLI, LABANCA, PACCHIAROTTI)
-- - Basso: 2 docenti (FERILLI, SABA)
-- - Ukulele: 2 docenti (MARCANTE, URSINI)
-- - Specialità: Violino, Beatbox, Canto Metal, Rap, Piano e Canto
-- ============================================