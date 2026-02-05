-- Lezioni MERCOLEDI per DEMO
-- Estratte dal foglio Excel "MERCOLEDI"

-- AULA PIANO - LABANCA (Pianoforte)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '16:30:00', '17:30:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'SILVESTRI' AND a.nome = 'Chiara'
AND d.cognome = 'LABANCA'
AND m.nome = 'Pianoforte'
AND au.nome = 'AULA PIANO';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '17:30:00', '18:15:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'TRAPANI' AND a.nome = 'Sophia'
AND d.cognome = 'LABANCA'
AND m.nome = 'Pianoforte'
AND au.nome = 'AULA PIANO';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '19:15:00', '20:00:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'GUERRA' AND a.nome = 'Isabella'
AND d.cognome = 'LABANCA'
AND m.nome = 'Pianoforte'
AND au.nome = 'AULA PIANO';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '20:00:00', '21:00:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'CAMBARA' AND a.nome = 'Rosa'
AND d.cognome = 'LABANCA'
AND m.nome = 'Pianoforte'
AND au.nome = 'AULA PIANO';

-- AULA MAGNA - LORITO (Canto)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '15:45:00', '16:45:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'GRASSO' AND a.nome = 'Gaia'
AND d.cognome = 'LORITO'
AND m.nome = 'Canto'
AND au.nome = 'AULA MAGNA';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '17:30:00', '18:15:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'SCARPACI' AND a.nome = 'Danilo'
AND d.cognome = 'LORITO'
AND m.nome = 'Canto'
AND au.nome = 'AULA MAGNA';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '18:15:00', '19:15:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'CIOFFI' AND a.nome = 'Riccardo'
AND d.cognome = 'LORITO'
AND m.nome = 'Canto'
AND au.nome = 'AULA MAGNA';

-- SALA POP - URSINI (Chitarra)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '15:45:00', '16:45:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'BELLUCCI' AND a.nome = 'Giulia'
AND d.cognome = 'URSINI'
AND m.nome = 'Chitarra'
AND au.nome = 'SALA POP';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '18:15:00', '19:00:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'CIANFONI' AND a.nome = 'Francesco'
AND d.cognome = 'URSINI'
AND m.nome = 'Chitarra'
AND au.nome = 'SALA POP';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '19:00:00', '19:45:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'PIERACCI' AND a.nome = 'Danilo'
AND d.cognome = 'URSINI'
AND m.nome = 'Chitarra'
AND au.nome = 'SALA POP';

-- SALA ROCK - BONIOLI (Batteria)
INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '18:15:00', '19:00:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'NISI' AND a.nome = 'Giordano'
AND d.cognome = 'BONIOLI'
AND m.nome = 'Batteria'
AND au.nome = 'SALA ROCK';

INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, allievo_id, docente_id, materia_id, aula_id, tipo) 
SELECT 'mercoledi', '20:00:00', '21:00:00', a.id, d.id, m.id, au.id, 'regolare'
FROM allievi a, docenti d, materie m, aule au
WHERE a.cognome = 'FENIZI' AND a.nome = 'Alessandro'
AND d.cognome = 'BONIOLI'
AND m.nome = 'Batteria'
AND au.nome = 'SALA ROCK';