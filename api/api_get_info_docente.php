<?php
/**
 * API Informazioni Docente
 * Restituisce dati anagrafici, lezioni, materie del docente
 */

require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $docente_id = $_GET['docente_id'] ?? null;
    
    if (!$docente_id) {
        throw new Exception('ID docente mancante');
    }
    
    $db = Database::getInstance();
    
    // Dati docente
    $docente = $db->queryOne("
        SELECT id, nome, cognome, 
               (cognome || ' ' || nome) as nome_completo,
               email, telefono, specializzazioni, note, created_at
        FROM docenti 
        WHERE id = ? AND attivo = 1
    ", [$docente_id]);
    
    if (!$docente) {
        throw new Exception('Docente non trovato');
    }
    
    // Materie insegnate
    $materie = $db->query("
        SELECT m.id, m.nome, m.categoria
        FROM materie m
        INNER JOIN docenti_materie dm ON m.id = dm.materia_id
        WHERE dm.docente_id = ? AND m.attiva = 1
        ORDER BY m.nome
    ", [$docente_id]);
    
    // Lezioni programmate
    $lezioni = $db->query("
        SELECT 
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            m.nome as materia,
            a.nome as aula,
            al.cognome || ' ' || al.nome as socio,
            al.id as socio_id
        FROM lezioni l
        INNER JOIN materie m ON l.materia_id = m.id
        LEFT JOIN aule a ON l.aula_id = a.id
        INNER JOIN soci al ON l.socio_id = al.id
        WHERE l.docente_id = ? AND l.attiva = 1
        ORDER BY 
            CASE l.giorno_settimana
                WHEN 'lunedi' THEN 1
                WHEN 'martedi' THEN 2
                WHEN 'mercoledi' THEN 3
                WHEN 'giovedi' THEN 4
                WHEN 'venerdi' THEN 5
                WHEN 'sabato' THEN 6
                WHEN 'domenica' THEN 7
            END,
            l.ora_inizio
    ", [$docente_id]);
    
    // Statistiche
    $statistiche = [
        'lezioni' => [
            'totale' => count($lezioni),
            'per_giorno' => []
        ]
    ];
    
    // Raggruppa lezioni per giorno
    foreach ($lezioni as $lez) {
        $giorno = ucfirst($lez['giorno_settimana']);
        if (!isset($statistiche['lezioni']['per_giorno'][$giorno])) {
            $statistiche['lezioni']['per_giorno'][$giorno] = 0;
        }
        $statistiche['lezioni']['per_giorno'][$giorno]++;
    }
    
    echo json_encode([
        'success' => true,
        'docente' => $docente,
        'materie' => $materie,
        'lezioni' => $lezioni,
        'statistiche' => $statistiche
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}