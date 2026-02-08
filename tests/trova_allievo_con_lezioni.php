<?php
/**
 * Trova un allievo che ha lezioni assegnate
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

echo "=== TROVA ALLIEVO CON LEZIONI ===\n\n";

try {
    // Trova allievi con lezioni
    $allievi_con_lezioni = $db->query("
        SELECT DISTINCT
            a.id,
            a.cognome || ' ' || a.nome as nome_completo,
            COUNT(l.id) as num_lezioni
        FROM allievi a
        JOIN lezioni l ON a.id = l.allievo_id
        GROUP BY a.id, a.cognome, a.nome
        ORDER BY num_lezioni DESC
        LIMIT 10
    ");
    
    if (empty($allievi_con_lezioni)) {
        echo "❌ NESSUN ALLIEVO HA LEZIONI ASSEGNATE!\n";
        echo "Il database lezioni è vuoto o non collegato correttamente.\n";
    } else {
        echo "✅ Trovati " . count($allievi_con_lezioni) . " allievi con lezioni:\n\n";
        
        foreach ($allievi_con_lezioni as $all) {
            echo "ID {$all['id']}: {$all['nome_completo']} - {$all['num_lezioni']} lezioni\n";
        }
        
        echo "\n---\n";
        echo "Usa uno di questi ID per testare l'API.\n";
        echo "Esempio: api_get_lezioni_allievo.php?allievo_id=" . $allievi_con_lezioni[0]['id'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n=== FINE ===\n";