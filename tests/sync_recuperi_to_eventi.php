<?php
/**
 * Script per sincronizzare recuperi dalla tabella recuperi a eventi_calendario
 * 
 * Problema: I recuperi creati vengono salvati solo in 'recuperi' ma non in 'eventi_calendario'
 * Soluzione: Questo script copia tutti i recuperi programmati in eventi_calendario
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "=== SYNC RECUPERI → EVENTI_CALENDARIO ===\n\n";

// 1. Trova tipologia_id per recuperi
$stmt = $db->query("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO' LIMIT 1");
$tipologia_recupero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tipologia_recupero) {
    die("ERRORE: Tipologia 'LEZ_RECUPERO' non trovata!\n");
}

$tipologia_id = $tipologia_recupero['id'];
echo "✓ Tipologia recupero ID: {$tipologia_id}\n\n";

// 2. Trova recuperi che non sono in eventi_calendario
$stmt = $db->query("
    SELECT r.*
    FROM recuperi r
    WHERE NOT EXISTS (
        SELECT 1 FROM eventi_calendario e
        WHERE e.tipologia_id = {$tipologia_id}
        AND e.data_evento = r.data_recupero
        AND e.ora_inizio = r.ora_inizio
        AND e.socio_id = r.socio_id
    )
    ORDER BY r.data_recupero, r.ora_inizio
");

$recuperi_da_sync = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Trovati " . count($recuperi_da_sync) . " recuperi da sincronizzare\n\n";

if (count($recuperi_da_sync) == 0) {
    echo "✓ Tutti i recuperi sono già sincronizzati!\n";
    exit(0);
}

// 3. Inserisci in eventi_calendario
$db->beginTransaction();

try {
    $insertStmt = $db->prepare("
        INSERT INTO eventi_calendario (
            tipologia_id,
            ricorrente,
            giorno_settimana,
            data_evento,
            ora_inizio,
            ora_fine,
            aula_id,
            docente_id,
            socio_id,
            materia_id,
            titolo,
            note,
            confermato,
            attivo,
            created_at
        ) VALUES (
            :tipologia_id,
            0,
            NULL,
            :data_evento,
            :ora_inizio,
            :ora_fine,
            :aula_id,
            :docente_id,
            :socio_id,
            :materia_id,
            'Recupero',
            :note,
            1,
            1,
            datetime('now', 'localtime')
        )
    ");
    
    $synced = 0;
    foreach ($recuperi_da_sync as $rec) {
        // Ottieni data assenza per note
        $assenza = $db->query("SELECT data_assenza FROM assenze WHERE id = ?", [$rec['assenza_id']])->fetch(PDO::FETCH_ASSOC);
        $data_assenza_formattata = $assenza ? date('d/m/Y', strtotime($assenza['data_assenza'])) : 'N/D';
        $note_recupero = "Recupero lezione del {$data_assenza_formattata}";
        if (!empty($rec['note_segreteria'])) {
            $note_recupero .= " - " . $rec['note_segreteria'];
        }
        
        $insertStmt->execute([
            ':tipologia_id' => $tipologia_id,
            ':data_evento' => $rec['data_recupero'],
            ':ora_inizio' => $rec['ora_inizio'],
            ':ora_fine' => $rec['ora_fine'],
            ':aula_id' => $rec['aula_id'],
            ':docente_id' => $rec['docente_id'],
            ':socio_id' => $rec['socio_id'],
            ':materia_id' => $rec['materia_id'],
            ':note' => $note_recupero
        ]);
        
        echo "✓ Sincronizzato recupero ID {$rec['id']} - ";
        echo "Data: {$rec['data_recupero']}, Orario: {$rec['ora_inizio']}-{$rec['ora_fine']}\n";
        
        $synced++;
    }
    
    $db->commit();
    
    echo "\n=== COMPLETATO ===\n";
    echo "✓ Sincronizzati {$synced} recuperi in eventi_calendario\n";
    echo "✓ Ora i recuperi dovrebbero essere visibili nel calendario!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERRORE durante la sincronizzazione:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}