-- =============================================
-- LEZIONI VENERDÌ
-- Generato automaticamente - VERIFICARE ORARI
-- =============================================

-- AULA MIDI (Chitarra - TESSITORE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '12:15', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%TERESI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '13:30', '14:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PECCERILLO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '14:30', '15:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BELCASTRO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '15:15', '16:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUNGO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:00', '18:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CARLINI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '18:00', '19:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SORRENTINO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '19:45', '20:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MIDI%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%GATTA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%TESSITORE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CHITARRA%' LIMIT 1);

-- AULA PIANO (Piano e Canto - SALVUCCI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '16:50', '17:35',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PETTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:35', '18:20',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%PIANO%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUZIO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%SALVUCCI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- AULA MAGNA (Canto - DI CRESCE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '14:15', '15:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SAVINI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:30', '18:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FRANCAVIGLIA%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '20:30', '21:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%MAGNA%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ALFANO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%DI CRESCE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA JAZZ (Batteria - ALBERINI)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '11:00', '11:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SPOSATO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '13:30', '14:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FABBRO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '16:45', '17:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PIRAS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:45', '18:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CIFALDI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '18:45', '19:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%COCO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '19:45', '20:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%JAZZ%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SCOGNAMIGLIO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%ALBERINI%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%BATTERIA%' LIMIT 1);

-- SALA POP (Canto - BUONO)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '11:15', '12:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%CARUSO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '12:15', '13:00',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%BUSSOLETTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '16:45', '17:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%FIERIMONTE%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:45', '18:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%VALLI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '18:30', '19:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%KOONS%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '19:30', '20:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%SACCO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '20:15', '21:15',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%POP%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%MUS.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%BUONO%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);

-- SALA ROCK (Canto - MARCANTE)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '15:00', '15:45',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%ALVAREZ%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '16:50', '17:35',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PETTI%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '17:45', '18:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%LAB.%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '18:30', '19:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%RIVELLINO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'VENERDÌ', '19:30', '20:30',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%ROCK%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%PROBBO%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%MARCANTE%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%CANTO%' LIMIT 1);
