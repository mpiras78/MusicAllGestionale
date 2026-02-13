<?php
/**
 * Script per aggiornare le note dei recuperi esistenti in eventi_calendario
 * Sostituisce "Recupero ID X" con "Recupero lezione del GG/MM/AAAA"
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "=== AGGIORNAMENTO NOTE RECUPERI ===\n\n";

// 1. Trova tipologia_id per recuperi
$stmt = $db->query("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO' LIMIT 1");
$tipologia_recupero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tipologia_recupero) {
    die("ERRORE: Tipologia 'LEZ_RECUPERO' non trovata!\n");
}

$tipologia_id = $tipologia_recupero['id'];
echo "✓ Tipologia recupero ID: {$tipologia_id}\n\n";

// 2. Trova tutti i recuperi in eventi_calendario
$stmt = $db->prepare("
    SELECT e.id, e.data_evento, e.ora_inizio, e.allievo_id, e.note
    FROM eventi_calendario e
    WHERE e.tipologia_id = ?
    ORDER BY e.data_evento, e.ora_inizio
");
$stmt->execute([$tipologia_id]);
$eventi_recupero = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Trovati " . count($eventi_recupero) . " recuperi in eventi_calendario\n\n";

if (count($eventi_recupero) == 0) {
    echo "Nessun recupero da aggiornare.\n";
    exit(0);
}

// 3. Aggiorna note per ogni recupero
$db->beginTransaction();

try {
    $updated = 0;
    
    foreach ($eventi_recupero as $evt) {
        // Trova recupero corrispondente in tabella recuperi
        $stmt = $db->prepare("
            SELECT r.assenza_id, r.note_segreteria
            FROM recuperi r
            WHERE r.data_recupero = ?
            AND r.ora_inizio = ?
            AND r.allievo_id = ?
            LIMIT 1
        ");
        $stmt->execute([$evt['data_evento'], $evt['ora_inizio'], $evt['allievo_id']]);
        $recupero = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recupero) {
            // Ottieni data assenza
            $stmt = $db->prepare("SELECT data_assenza FROM assenze WHERE id = ?");
            $stmt->execute([$recupero['assenza_id']]);
            $assenza = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($assenza) {
                $data_assenza_formattata = date('d/m/Y', strtotime($assenza['data_assenza']));
                $note_nuove = "Recupero lezione del {$data_assenza_formattata}";
                
                if (!empty($recupero['note_segreteria'])) {
                    $note_nuove .= " - " . $recupero['note_segreteria'];
                }
                
                // Aggiorna solo se le note sono diverse
                if ($evt['note'] !== $note_nuove) {
                    $stmt = $db->prepare("UPDATE eventi_calendario SET note = ? WHERE id = ?");
                    $stmt->execute([$note_nuove, $evt['id']]);
                    
                    echo "✓ Aggiornato recupero ID {$evt['id']} - ";
                    echo "Data recupero: {$evt['data_evento']}, Assenza del: {$data_assenza_formattata}\n";
                    echo "  Vecchie note: {$evt['note']}\n";
                    echo "  Nuove note: {$note_nuove}\n\n";
                    
                    $updated++;
                }
            }
        }
    }
    
    $db->commit();
    
    echo "\n=== COMPLETATO ===\n";
    echo "✓ Aggiornati {$updated} recuperi su " . count($eventi_recupero) . "\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERRORE durante l'aggiornamento:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}