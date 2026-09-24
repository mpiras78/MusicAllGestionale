-- =============================================
-- LEZIONI MARTEDÌ
-- Basato su conferme manuali dall'Excel
-- =============================================

-- AULA MIDI (6 lezioni confermate)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '10:00', '10:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FERRANTE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '15:45', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CARDONI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:45', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GIORDANO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LAURINI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:15', '19:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MAIURI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '19:00', '19:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%DIVIDUS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

-- AULA PIANO (6 lezioni confermate)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '10:15', '11:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%NARD%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '11:15', '12:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%VILLANI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:45', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BARBATI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%PACCHIAROTTI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%PIANO%' OR UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%EL MOSLEH%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%PACCHIAROTTI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%PIANO%' OR UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:30', '19:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BRITTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%PACCHIAROTTI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%PIANO%' OR UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '19:30', '20:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%RICCO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%PACCHIAROTTI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%PIANO%' OR UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- AULA MAGNA (9 lezioni confermate + da completare)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '10:30', '11:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%AMADDII%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '11:15', '12:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%RAVENNA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '12:15', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SATTA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '14:45', '15:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MARCANTE%' AND UPPER(nome) LIKE '%DANIELE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '15:45', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SANNA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:45', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BASILE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SANITA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:15', '19:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ANTONELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '19:00', '19:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MURATTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA JAZZ (Docente ALBERINI - Batteria)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '15:30', '16:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SPEZZAFERRO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:30', '17:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%VITACCA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:00', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BARRELLA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GARGIULO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:15', '19:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FINELLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '19:00', '19:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PAONESSA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

-- SALA POP (Docenti BUONO/LORITO - Canto)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '10:00', '11:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%COLIA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '11:00', '12:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%TRIESTE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '15:30', '16:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BIANCHINI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:00', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CROCE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:45', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CAMODECA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BARBATI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:15', '19:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%INSIEME%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '19:15', '20:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PICA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%LORITO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA ROCK (Docenti MARCANTE/DI GIORGIO - Canto)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '15:30', '16:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BLASIS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:00', '16:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ARDIZZI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '16:45', '17:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FLORE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI GIORGIO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '17:30', '18:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FEDE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI GIORGIO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '18:15', '19:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%AMADDII%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI GIORGIO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

