<?php
/**
 * Trova un socio che ha lezioni assegnate
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

echo "=== TROVA SOCIO CON LEZIONI ===\n\n";

try {
    // Trova soci con lezioni: `soci` è collegata ad anagrafica `persone` tramite persona_id
    $allievi_con_lezioni = $db->query(
        "SELECT DISTINCT
            s.id,
            p.cognome || ' ' || p.nome as nome_completo,
            COUNT(l.id) as num_lezioni
        FROM soci s
        JOIN persone p ON s.persona_id = p.id
        JOIN lezioni l ON s.id = l.allievo_id
        GROUP BY s.id, p.cognome, p.nome
        ORDER BY num_lezioni DESC
        LIMIT 10"
    );
    
    if (empty($allievi_con_lezioni)) {
        echo "❌ NESSUN SOCIO HA LEZIONI ASSEGNATE!\n";
        echo "Il database lezioni è vuoto o non collegato correttamente.\n";
    } else {
        echo "✅ Trovati " . count($allievi_con_lezioni) . " soci con lezioni:\n\n";

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