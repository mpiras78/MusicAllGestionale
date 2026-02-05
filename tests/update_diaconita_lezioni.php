<?php
/**
 * Aggiorna lezioni di Diaconita
 * Da: Tessitore + Midi
 * A: Buono + Magna
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

echo "🔄 AGGIORNAMENTO LEZIONI DIACONITA\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Trova allievo Diaconita
    $allievo = $db->queryOne("SELECT * FROM allievi WHERE cognome LIKE '%Diaconita%' OR nome LIKE '%Diaconita%'");
    
    if (!$allievo) {
        echo "❌ Allievo Diaconita non trovato!\n";
        exit(1);
    }
    
    echo "📋 Allievo trovato:\n";
    echo "   ID: {$allievo['id']}\n";
    echo "   Nome: {$allievo['cognome']} {$allievo['nome']}\n\n";
    
    // 2. Trova docente Tessitore (vecchio)
    $tessitore = $db->queryOne("SELECT * FROM docenti WHERE cognome LIKE '%Tessitore%'");
    
    // 3. Trova docente Buono (nuovo)
    $buono = $db->queryOne("SELECT * FROM docenti WHERE cognome LIKE '%Buono%'");
    
    if (!$buono) {
        echo "❌ Docente Buono non trovato!\n";
        exit(1);
    }
    
    echo "👨‍🏫 Docente nuovo:\n";
    echo "   ID: {$buono['id']}\n";
    echo "   Nome: {$buono['cognome']} {$buono['nome']}\n\n";
    
    // 4. Trova aula Midi (vecchia)
    $midi = $db->queryOne("SELECT * FROM aule WHERE nome LIKE '%Midi%'");
    
    // 5. Trova aula Magna (nuova)
    $magna = $db->queryOne("SELECT * FROM aule WHERE nome LIKE '%Magna%'");
    
    if (!$magna) {
        echo "❌ Aula Magna non trovata!\n";
        exit(1);
    }
    
    echo "🚪 Aula nuova:\n";
    echo "   ID: {$magna['id']}\n";
    echo "   Nome: {$magna['nome']}\n\n";
    
    // 6. Trova lezioni da aggiornare
    $whereConditions = ["allievo_id = ?"];
    $params = [$allievo['id']];
    
    if ($tessitore) {
        $whereConditions[] = "docente_id = ?";
        $params[] = $tessitore['id'];
    }
    
    if ($midi) {
        $whereConditions[] = "aula_id = ?";
        $params[] = $midi['id'];
    }
    
    $where = implode(' AND ', $whereConditions);
    
    $lezioni = $db->query("
        SELECT l.*, 
               d.cognome || ' ' || d.nome as docente,
               a.nome as aula,
               m.nome as materia
        FROM lezioni l
        JOIN docenti d ON l.docente_id = d.id
        JOIN aule a ON l.aula_id = a.id
        JOIN materie m ON l.materia_id = m.id
        WHERE $where
    ", $params);
    
    if (empty($lezioni)) {
        echo "⚠️  Nessuna lezione trovata con i criteri specificati.\n";
        echo "   Cerco tutte le lezioni di Diaconita...\n\n";
        
        $lezioni = $db->query("
            SELECT l.*, 
                   d.cognome || ' ' || d.nome as docente,
                   a.nome as aula,
                   m.nome as materia
            FROM lezioni l
            JOIN docenti d ON l.docente_id = d.id
            JOIN aule a ON l.aula_id = a.id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.allievo_id = ?
        ", [$allievo['id']]);
    }
    
    echo "📚 Lezioni trovate: " . count($lezioni) . "\n\n";
    
    foreach ($lezioni as $lez) {
        echo "   Lezione ID {$lez['id']}:\n";
        echo "   - Giorno: {$lez['giorno_settimana']}\n";
        echo "   - Orario: {$lez['ora_inizio']} - {$lez['ora_fine']}\n";
        echo "   - Docente: {$lez['docente']}\n";
        echo "   - Aula: {$lez['aula']}\n";
        echo "   - Materia: {$lez['materia']}\n\n";
    }
    
    // 7. Aggiorna lezioni
    if (count($lezioni) > 0) {
        echo "🔄 Aggiorno lezioni...\n";
        
        $updated = 0;
        foreach ($lezioni as $lez) {
            $db->execute("
                UPDATE lezioni 
                SET docente_id = ?, aula_id = ?
                WHERE id = ?
            ", [$buono['id'], $magna['id'], $lez['id']]);
            $updated++;
        }
        
        echo "✅ Aggiornate $updated lezioni!\n\n";
        
        // Verifica
        echo "✅ VERIFICA MODIFICHE:\n\n";
        $verificaLezioni = $db->query("
            SELECT l.*, 
                   d.cognome || ' ' || d.nome as docente,
                   a.nome as aula,
                   m.nome as materia
            FROM lezioni l
            JOIN docenti d ON l.docente_id = d.id
            JOIN aule a ON l.aula_id = a.id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.allievo_id = ?
        ", [$allievo['id']]);
        
        foreach ($verificaLezioni as $lez) {
            echo "   ✓ Lezione ID {$lez['id']}:\n";
            echo "     - Docente: {$lez['docente']}\n";
            echo "     - Aula: {$lez['aula']}\n\n";
        }
        
        echo "🎉 AGGIORNAMENTO COMPLETATO!\n";
    } else {
        echo "⚠️  Nessuna lezione da aggiornare.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}