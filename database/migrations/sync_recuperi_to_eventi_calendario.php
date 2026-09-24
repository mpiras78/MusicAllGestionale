<?php
/**
 * Migration: Sincronizza recuperi esistenti con evento_calendario
 * 
 * Questo script crea gli eventi_calendario mancanti per tutti i recuperi
 * che non hanno ancora un evento_calendario associato.
 * 
 * Esecuzione: php database/migrations/sync_recuperi_to_eventi_calendario.php
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "🔄 Sincronizzazione recuperi con evento_calendario...\n\n";

try {
    // 1. Conta recuperi senza evento_calendario
    $count_query = $db->query("
        SELECT COUNT(*) as count FROM recuperi r
        WHERE r.annullato = 0 
        AND NOT EXISTS (
            SELECT 1 FROM eventi_calendario e
            WHERE e.data_evento = r.data_recupero
            AND e.ora_inizio = r.ora_inizio
            AND e.socio_id = r.socio_id
        )
    ");
    $count = $count_query->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        echo "✅ Nessun recupero da sincronizzare. Tutti i recuperi hanno evento_calendario.\n";
        exit(0);
    }
    
    echo "Found {$count} recuperi senza evento_calendario. Procedendo con la sincronizzazione...\n\n";
    
    // 2. Ottieni ID tipologia recupero
    $tipologia_query = $db->query("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO' LIMIT 1");
    $tipologia = $tipologia_query->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipologia) {
        echo "❌ ERRORE: Tipologia 'LEZ_RECUPERO' non trovata! Creazione evento_calendario non possibile.\n";
        echo "   Asicurarsi che la tipologia evento 'Lezione Recupero' esista.\n";
        exit(1);
    }
    
    $tipologia_id = $tipologia['id'];
    echo "✓ Tipologia LEZ_RECUPERO trovata (ID: $tipologia_id)\n\n";
    
    // 3. Ottieni tutti i recuperi senza evento_calendario
    $recuperi_query = $db->query("
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            r.aula_id,
            r.docente_id,
            r.socio_id,
            r.materia_id,
            a.data_assenza
        FROM recuperi r
        LEFT JOIN assenze a ON r.assenza_id = a.id
        WHERE r.annullato = 0 
        AND NOT EXISTS (
            SELECT 1 FROM eventi_calendario e
            WHERE e.data_evento = r.data_recupero
            AND e.ora_inizio = r.ora_inizio
            AND e.socio_id = r.socio_id
        )
        ORDER BY r.data_recupero DESC
    ");
    
    $recuperi = $recuperi_query->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Inserisci eventi_calendario per ogni recupero
    $insert_count = 0;
    $stmt = $db->prepare("
        INSERT INTO eventi_calendario (
            tipologia_id, ricorrente, giorno_settimana, data_evento,
            ora_inizio, ora_fine, aula_id, docente_id, socio_id, materia_id,
            titolo, note, confermato, attivo, created_at
        ) VALUES (?, 0, NULL, ?, ?, ?, ?, ?, ?, ?, 'Recupero', ?, 1, 1, datetime('now', 'localtime'))
    ");
    
    foreach ($recuperi as $recupero) {
        $data_assenza_formattata = '';
        if (!empty($recupero['data_assenza'])) {
            $data_assenza_formattata = date('d/m/Y', strtotime($recupero['data_assenza']));
        }
        $note = $data_assenza_formattata ? "Recupero lezione del {$data_assenza_formattata}" : "Recupero lezione";
        
        $stmt->execute([
            $tipologia_id,
            $recupero['data_recupero'],
            $recupero['ora_inizio'],
            $recupero['ora_fine'],
            $recupero['aula_id'],
            $recupero['docente_id'],
            $recupero['socio_id'],
            $recupero['materia_id'],
            $note
        ]);
        
        $insert_count++;
        echo "✓ Sincronizzato recupero ID {$recupero['id']}: {$recupero['data_recupero']} {$recupero['ora_inizio']}\n";
    }
    
    echo "\n✅ Sincronizzazione completata!\n";
    echo "   {$insert_count} eventi_calendario creati\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE durante la sincronizzazione:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}
