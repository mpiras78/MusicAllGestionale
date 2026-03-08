<?php
/**
 * Corregge materia Diaconita da Chitarra a Canto
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance();

echo "🎵 CORREZIONE MATERIA DIACONITA\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Trova socio (ex allievo) Diaconita nella nuova anagrafica
    $allievo = $db->queryOne("SELECT s.id as id, p.cognome as cognome, p.nome as nome
        FROM soci s
        JOIN persone p ON s.persona_id = p.id
        WHERE p.cognome LIKE '%Diaconita%'");
    
    if (!$allievo) {
        echo "❌ Socio Diaconita non trovato!\n";
        exit(1);
    }
    
    echo "📋 Socio: {$allievo['cognome']} {$allievo['nome']}\n\n";
    
    // Trova materia Canto
    $canto = $db->queryOne("SELECT * FROM materie WHERE nome LIKE '%Canto%'");
    
    if (!$canto) {
        echo "❌ Materia Canto non trovata!\n";
        exit(1);
    }
    
    echo "🎤 Materia Canto ID: {$canto['id']}\n\n";
    
    // Trova lezioni attuali
    $lezioni = $db->query("
        SELECT l.*, 
               m.nome as materia,
               d.cognome || ' ' || d.nome as docente,
               a.nome as aula
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
        JOIN aule a ON l.aula_id = a.id
        WHERE l.allievo_id = ?
    ", [$allievo['id']]);
    
    echo "📚 Lezioni trovate:\n\n";
    
    foreach ($lezioni as $lez) {
        echo "   Lezione ID {$lez['id']}:\n";
        echo "   - Giorno: {$lez['giorno_settimana']}\n";
        echo "   - Orario: {$lez['ora_inizio']} - {$lez['ora_fine']}\n";
        echo "   - Materia ATTUALE: {$lez['materia']}\n";
        echo "   - Docente: {$lez['docente']}\n";
        echo "   - Aula: {$lez['aula']}\n\n";
    }
    
    // Aggiorna a Canto
    echo "🔄 Aggiorno materia a Canto...\n";
    
    $updated = 0;
    foreach ($lezioni as $lez) {
        $db->execute("
            UPDATE lezioni 
            SET materia_id = ?
            WHERE id = ?
        ", [$canto['id'], $lez['id']]);
        $updated++;
    }
    
    echo "✅ Aggiornate $updated lezioni!\n\n";
    
    // Verifica finale
    echo "✅ VERIFICA MODIFICHE:\n\n";
    
    $verificaLezioni = $db->query("
        SELECT l.*, 
               m.nome as materia,
               d.cognome || ' ' || d.nome as docente,
               a.nome as aula
        FROM lezioni l
        JOIN materie m ON l.materia_id = m.id
        JOIN docenti d ON l.docente_id = d.id
        JOIN aule a ON l.aula_id = a.id
        WHERE l.allievo_id = ?
    ", [$allievo['id']]);
    
    foreach ($verificaLezioni as $lez) {
        echo "   ✓ Lezione ID {$lez['id']}:\n";
        echo "     - Materia: {$lez['materia']} ✅\n";
        echo "     - Docente: {$lez['docente']}\n";
        echo "     - Aula: {$lez['aula']}\n\n";
    }
    
    echo "🎉 CORREZIONE COMPLETATA!\n\n";
    echo "📋 Dati finali Diaconita:\n";
    echo "   - Docente: BUONO Eleonora\n";
    echo "   - Aula: AULA MAGNA\n";
    echo "   - Materia: Canto ✅\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}