<?php
require_once 'includes/bootstrap.php';

echo "=== FIX TABELLA ISCRIZIONI ===\n\n";

$db = Database::getInstance()->getConnection();

try {
    // Verifica struttura attuale
    echo "1. Verifica struttura attuale...\n";
    $result = $db->query("PRAGMA table_info(iscrizioni)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Colonne attuali:\n";
    foreach ($columns as $col) {
        echo "  - {$col['name']} ({$col['type']})\n";
    }
    
    // Verifica se serve la migration
    $hasDataInizio = false;
    $hasTipoCorsoConfigId = false;
    
    foreach ($columns as $col) {
        if ($col['name'] === 'data_inizio') $hasDataInizio = true;
        if ($col['name'] === 'tipo_corso_config_id') $hasTipoCorsoConfigId = true;
    }
    
    if ($hasDataInizio && $hasTipoCorsoConfigId) {
        echo "\n✅ Tabella già corretta!\n";
        exit(0);
    }
    
    echo "\n2. Esecuzione migration...\n";
    
    // Backup
    $db->exec("CREATE TABLE IF NOT EXISTS iscrizioni_backup AS SELECT * FROM iscrizioni");
    echo "  ✓ Backup creato\n";
    
    // Ricrea tabella
    $db->exec("DROP TABLE IF EXISTS iscrizioni");
    
    $db->exec("
        CREATE TABLE iscrizioni (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            socio_id INTEGER NOT NULL,
            tipo_corso_config_id INTEGER,
            materia_id INTEGER NOT NULL,
            docente_id INTEGER NOT NULL,
            anno_accademico VARCHAR(20) NOT NULL,
            data_inizio DATE NOT NULL,
            data_fine DATE,
            stato VARCHAR(20) DEFAULT 'attiva',
            quota_iscrizione DECIMAL(10,2) DEFAULT 30.00,
            sconto_fratelli DECIMAL(10,2) DEFAULT 0,
            sconto_meta_anno DECIMAL(10,2) DEFAULT 0,
            note TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (socio_id) REFERENCES soci(id),
            FOREIGN KEY (tipo_corso_config_id) REFERENCES tipi_corso_config(id),
            FOREIGN KEY (materia_id) REFERENCES materie(id),
            FOREIGN KEY (docente_id) REFERENCES docenti(id)
        )
    ");
    echo "  ✓ Tabella ricreata\n";
    
    // Ripristina dati
    $backupColumns = $db->query("PRAGMA table_info(iscrizioni_backup)")->fetchAll(PDO::FETCH_ASSOC);
    $backupColNames = array_column($backupColumns, 'name');
    
    // Costruisci query INSERT dinamica
    $selectParts = [
        'id',
        'socio_id',
        in_array('tipo_corso_config_id', $backupColNames) ? 'tipo_corso_config_id' : 
            (in_array('tipo_corso_id', $backupColNames) ? 'tipo_corso_id' : 'NULL'),
        'materia_id',
        'docente_id',
        'anno_accademico',
        in_array('data_inizio', $backupColNames) ? 'data_inizio' : 
            (in_array('data_inizio_corso', $backupColNames) ? 'data_inizio_corso' : 
            (in_array('data_iscrizione', $backupColNames) ? 'data_iscrizione' : 'CURRENT_DATE')),
        in_array('data_fine', $backupColNames) ? 'data_fine' : 
            (in_array('data_fine_corso', $backupColNames) ? 'data_fine_corso' : 'NULL'),
        'stato',
        in_array('quota_iscrizione', $backupColNames) ? 'quota_iscrizione' : '30.00',
        in_array('sconto_fratelli', $backupColNames) ? 'sconto_fratelli' : '0',
        in_array('sconto_meta_anno', $backupColNames) ? 'sconto_meta_anno' : '0',
        in_array('note', $backupColNames) ? 'note' : 'NULL',
        'created_at'
    ];
    
    $sql = "INSERT INTO iscrizioni (
        id, socio_id, tipo_corso_config_id, materia_id, docente_id,
        anno_accademico, data_inizio, data_fine, stato,
        quota_iscrizione, sconto_fratelli, sconto_meta_anno, note, created_at
    ) SELECT " . implode(', ', $selectParts) . " FROM iscrizioni_backup";
    
    $db->exec($sql);
    echo "  ✓ Dati ripristinati\n";
    
    // Ricrea indici
    $db->exec("CREATE INDEX idx_iscrizioni_socio ON iscrizioni(socio_id, stato)");
    $db->exec("CREATE INDEX idx_iscrizioni_anno ON iscrizioni(anno_accademico, stato)");
    $db->exec("CREATE INDEX idx_iscrizioni_docente ON iscrizioni(docente_id, stato)");
    $db->exec("CREATE INDEX idx_iscrizioni_stato ON iscrizioni(stato, data_inizio)");
    echo "  ✓ Indici ricreati\n";
    
    // Rimuovi backup
    $db->exec("DROP TABLE iscrizioni_backup");
    echo "  ✓ Backup rimosso\n";
    
    echo "\n✅ MIGRATION COMPLETATA CON SUCCESSO!\n\n";
    
    // Mostra nuova struttura
    echo "Nuova struttura:\n";
    $result = $db->query("PRAGMA table_info(iscrizioni)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['name']} ({$col['type']})\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    echo "\nRollback...\n";
    
    // Tenta rollback
    try {
        $db->exec("DROP TABLE IF EXISTS iscrizioni");
        $db->exec("ALTER TABLE iscrizioni_backup RENAME TO iscrizioni");
        echo "✓ Rollback completato\n";
    } catch (Exception $e2) {
        echo "❌ Errore rollback: " . $e2->getMessage() . "\n";
    }
    
    exit(1);
}
