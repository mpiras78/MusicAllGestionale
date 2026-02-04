-- Lezioni di esempio per popolare il calendario
-- Lunedì

INSERT INTO lezioni (allievo_id, docente_id, materia_id, aula_id, giorno_settimana, ora_inizio, ora_fine, tipo, attiva, created_at) VALUES
-- Lunedì
(1, 1, 1, 1, 'lunedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(2, 2, 2, 2, 'lunedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(3, 3, 3, 3, 'lunedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(4, 4, 4, 4, 'lunedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(5, 5, 5, 1, 'lunedi', '10:45', '11:30', 'regolare', 1, datetime('now')),

-- Martedì
(6, 6, 6, 2, 'martedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(7, 7, 7, 3, 'martedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(8, 8, 8, 4, 'martedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(9, 9, 9, 1, 'martedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(10, 10, 10, 2, 'martedi', '10:45', '11:30', 'regolare', 1, datetime('now')),

-- Mercoledì
(11, 11, 11, 3, 'mercoledi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(12, 12, 12, 4, 'mercoledi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(13, 13, 13, 1, 'mercoledi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(14, 14, 14, 2, 'mercoledi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(15, 15, 15, 3, 'mercoledi', '10:45', '11:30', 'regolare', 1, datetime('now')),

-- Giovedì
(16, 16, 16, 4, 'giovedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(17, 17, 17, 1, 'giovedi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(18, 18, 18, 2, 'giovedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(19, 19, 1, 3, 'giovedi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(20, 20, 2, 4, 'giovedi', '10:45', '11:30', 'regolare', 1, datetime('now')),

-- Venerdì
(21, 21, 3, 1, 'venerdi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(22, 22, 4, 2, 'venerdi', '09:15', '10:00', 'regolare', 1, datetime('now')),
(23, 23, 5, 3, 'venerdi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(24, 24, 6, 4, 'venerdi', '10:00', '10:45', 'regolare', 1, datetime('now')),
(25, 25, 7, 1, 'venerdi', '10:45', '11:30', 'regolare', 1, datetime('now')),

-- Sabato  
(26, 1, 8, 2, 'sabato', '09:15', '10:00', 'regolare', 1, datetime('now')),
(27, 2, 9, 3, 'sabato', '09:15', '10:00', 'regolare', 1, datetime('now')),
(28, 3, 10, 4, 'sabato', '10:00', '10:45', 'regolare', 1, datetime('now')),
(29, 4, 11, 1, 'sabato', '10:00', '10:45', 'regolare', 1, datetime('now')),
(30, 5, 12, 2, 'sabato', '10:45', '11:30', 'regolare', 1, datetime('now'));