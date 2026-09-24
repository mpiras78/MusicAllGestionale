-- =============================================
-- LEZIONI SABATO
-- Generato automaticamente - VERIFICARE ORARI
-- =============================================

-- AULA MIDI (Chitarra - TESSITORE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SORRENTINO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '11:00', '12:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SABATINO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '12:00', '12:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MAZZI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '14:00', '15:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PIETRANTONIO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:00', '16:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CIPRELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '16:00', '17:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LUCA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '17:00', '17:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CAVALLARO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

-- AULA PIANO (Piano e Canto - SALVUCCI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LABANCHI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '11:00', '11:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PAULHIAC%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '11:45', '12:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GUELI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- AULA MAGNA (Canto - DI CRESCE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '12:00', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%DELLE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '13:00', '14:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%HENKE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '14:00', '15:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%DELLE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:00', '16:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ANTONIELLA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '16:00', '17:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FORMISANO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '17:00', '17:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GRASSO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA JAZZ (Batteria - ALBERINI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CARDILLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '11:00', '12:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PISANI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '12:00', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PISANI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '13:00', '14:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CASTELLANI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '14:00', '14:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FILACCHIONE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '14:45', '15:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MAGALOTTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:30', '16:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MONTARSI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '16:15', '17:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LUCA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '17:15', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%VIGNA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

-- SALA POP (Canto - BUONO)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:00', '16:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA ROCK (Canto - MARCANTE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CASILLO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '12:00', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LOFFREDO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '14:15', '15:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CASALINUOVO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:00', '15:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CAMODECA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '15:45', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '17:30', '18:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'SABATO', '18:30', '19:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BERNABEI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
