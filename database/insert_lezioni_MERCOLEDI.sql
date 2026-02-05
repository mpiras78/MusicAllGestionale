-- =============================================
-- LEZIONI MERCOLEDÌ
-- Generato automaticamente - VERIFICARE ORARI
-- =============================================

-- AULA MIDI (Chitarra - TESSITORE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%COSENZA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '12:00', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GABRIELI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '13:30', '14:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ROSSI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '15:00', '15:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BELLUCCI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '15:45', '16:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ANGELIS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '17:30', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%TRAPANI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '18:15', '19:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PRISTERA'%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '19:15', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GUERRA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '20:00', '21:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CAMBARA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

-- AULA PIANO (Piano e Canto - SALVUCCI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '19:00', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CAPUTO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '20:00', '21:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PETACCIA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- AULA MAGNA (Canto - DI CRESCE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '15:45', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PANZANELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA JAZZ (Batteria - ALBERINI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '19:45', '20:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%NIEDDU%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

-- SALA POP (Canto - BUONO)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '15:45', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%HALL%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '19:00', '19:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FRANCAVIGLIA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '20:00', '21:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA ROCK (Canto - MARCANTE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '11:30', '12:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%EVANGELISTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '19:00', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '20:00', '21:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FENIZI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MERCOLEDÌ', '21:00', '22:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%TOLENTINO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
