<?php
/**
 * Test API get_lezioni_socio
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Simula autenticazione admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['username'] = 'admin';

// Ottieni istanza database
$db = Database::getInstance();

// Test con socio ID = 1 (ex socio)
$socio_id = 1;

echo "=== TEST API LEZIONI SOCIO ===\n\n";
echo "Socio ID: $socio_id\n\n";

try {
    // Query diretta
    $lezioni = $db->query("
        SELECT 
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
            -- la tabella `lezioni` nel DB attuale usa ancora la colonna `socio_id`
            WHERE l.socio_id = ?
        ORDER BY 
            CASE l.giorno_settimana
                WHEN 'lunedi' THEN 1
                WHEN 'Lunedì' THEN 1
                WHEN 'martedi' THEN 2
                WHEN 'Martedì' THEN 2
                WHEN 'mercoledi' THEN 3
                WHEN 'Mercoledì' THEN 3
                WHEN 'giovedi' THEN 4
                WHEN 'Giovedì' THEN 4
                WHEN 'venerdi' THEN 5
                WHEN 'Venerdì' THEN 5
                WHEN 'sabato' THEN 6
                WHEN 'Sabato' THEN 7
                WHEN 'domenica' THEN 7
                WHEN 'Domenica' THEN 7
            END,
            l.ora_inizio
    ", [$socio_id]);
    
    echo "Lezioni trovate: " . count($lezioni) . "\n\n";
    
    if (empty($lezioni)) {
        echo "NESSUNA LEZIONE TROVATA!\n";
        echo "Verifica che esistano lezioni per questo socio.\n\n";
        
        // Check se il socio esiste (recupera anche dati anagrafici da `persone`)
        $socio = $db->queryOne("SELECT s.*, p.cognome as cognome, p.nome as nome
            FROM soci s
            LEFT JOIN persone p ON s.persona_id = p.id
            WHERE s.id = ?", [$socio_id]);
        if ($socio) {
            echo "✅ Socio esiste: " . ($socio['cognome'] ?? '') . " " . ($socio['nome'] ?? '') . "\n";
        } else {
            echo "❌ Socio NON esiste!\n";
        }
        
        // Conta totale lezioni
        $totale = $db->queryOne("SELECT COUNT(*) as cnt FROM lezioni");
        echo "\nTotale lezioni nel database: {$totale['cnt']}\n";
        
    } else {
        echo "JSON Response:\n";
        echo json_encode($lezioni, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n\n";
        
        echo "Dettaglio lezioni:\n";
        foreach ($lezioni as $lez) {
            echo "- {$lez['giorno_settimana']} {$lez['ora_inizio']}-{$lez['ora_fine']} - {$lez['materia']} - {$lez['docente']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FINE TEST ===\n";