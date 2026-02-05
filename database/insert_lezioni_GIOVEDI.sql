-- =============================================
-- LEZIONI GIOVEDÌ
-- Generato automaticamente - VERIFICARE ORARI
-- =============================================

-- AULA MIDI (Chitarra - TESSITORE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '15:00', '16:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SALERNO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '18:15', '19:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MONSU'%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '19:15', '20:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%IMPERIOLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

-- AULA MAGNA (Canto - DI CRESCE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '11:00', '12:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PORZIO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA JAZZ (Batteria - ALBERINI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '14:00', '15:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PARIS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '16:00', '17:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BOTTARELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '17:00', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GOZZO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '19:00', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MARZULLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

-- SALA POP (Canto - BUONO)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '10:30', '11:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%RICCIARELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '15:00', '15:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CASTALDO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '15:45', '16:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SALVATORI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '16:30', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ROSSI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '17:30', '18:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%VALENTINI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '18:30', '19:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BRAICO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '19:15', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FERLANTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '20:00', '21:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '21:00', '22:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MONTARSI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA ROCK (Canto - MARCANTE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '15:00', '16:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CERUZZI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '16:45', '17:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MONSU'%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '17:45', '18:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MONSU'%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '18:45', '19:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%RICHETTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'GIOVEDÌ', '19:45', '20:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%REGOLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
