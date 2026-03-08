<?php
/**
 * Crea tabella recuperi manualmente
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== CREAZIONE TABELLA RECUPERI ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. Crea tabella recuperi
    echo "Creazione tabella recuperi...\n";
    $db->execute("
        CREATE TABLE IF NOT EXISTS recuperi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            
            -- Riferimenti
            assenza_id INTEGER NOT NULL,
            lezione_originale_id INTEGER NOT NULL,
            allievo_id INTEGER NOT NULL,
            docente_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            
            -- Nuova programmazione
            data_recupero DATE NOT NULL,
            ora_inizio TIME NOT NULL,
            ora_fine TIME NOT NULL,
            aula_id INTEGER,
            
            -- Conferme (stato calcolato dinamicamente)
            confermata_da_docente BOOLEAN DEFAULT 0,
            confermata_da_segreteria BOOLEAN DEFAULT 0,
            confermata_da_user_id INTEGER,
            data_conferma DATETIME,
            
            -- Annullamento
            annullato BOOLEAN DEFAULT 0,
            motivo_annullamento TEXT,
            annullato_da_user_id INTEGER,
            data_annullamento DATETIME,
            
            -- Note
            note_segreteria TEXT,
            motivo_rifiuto TEXT,
            
            -- Audit
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            
            -- Foreign Keys
            FOREIGN KEY (assenza_id) REFERENCES assenze(id) ON DELETE CASCADE,
            FOREIGN KEY (lezione_originale_id) REFERENCES lezioni(id),
            FOREIGN KEY (allievo_id) REFERENCES soci(id),
            FOREIGN KEY (docente_id) REFERENCES docenti(id),
            FOREIGN KEY (materia_id) REFERENCES materie(id),
            FOREIGN KEY (aula_id) REFERENCES aule(id),
            FOREIGN KEY (confermata_da_user_id) REFERENCES users(id),
            FOREIGN KEY (annullato_da_user_id) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");
    echo "✓ Tabella recuperi creata\n\n";
    
    // 2. Crea indici
    echo "Creazione indici...\n";
    $db->execute("CREATE INDEX IF NOT EXISTS idx_recuperi_assenza ON recuperi(assenza_id)");
    echo "✓ idx_recuperi_assenza\n";
    
    $db->execute("CREATE INDEX IF NOT EXISTS idx_recuperi_data ON recuperi(data_recupero)");
    echo "✓ idx_recuperi_data\n";
    
    $db->execute("CREATE INDEX IF NOT EXISTS idx_recuperi_docente ON recuperi(docente_id)");
    echo "✓ idx_recuperi_docente\n";
    
    $db->execute("CREATE INDEX IF NOT EXISTS idx_recuperi_allievo ON recuperi(allievo_id)");
    echo "✓ idx_recuperi_allievo\n";
    
    $db->execute("CREATE INDEX IF NOT EXISTS idx_recuperi_stato ON recuperi(confermata_da_docente, annullato, data_recupero)");
    echo "✓ idx_recuperi_stato\n\n";
    
    // 3. Crea view
    echo "Creazione view...\n";
    $db->execute("
        CREATE VIEW IF NOT EXISTS v_recuperi_con_stato AS
        SELECT 
            r.*,
            CASE 
                WHEN r.annullato = 1 THEN 'annullato'
                WHEN r.data_recupero < DATE('now') THEN 'completato'
                WHEN r.confermata_da_docente = 1 OR r.confermata_da_segreteria = 1 THEN 'confermata'
                ELSE 'proposta'
            END as stato,
            
            al.cognome || ' ' || al.nome as allievo,
            d.cognome || ' ' || d.nome as docente,
            m.nome as materia,
            a.nome as aula,
            
            ass.data as data_assenza_originale,
            ass.causata_da,
            
            lo.giorno_settimana as giorno_originale,
            lo.ora_inizio as ora_originale_inizio,
            lo.ora_fine as ora_originale_fine
            
        FROM recuperi r
        LEFT JOIN soci s ON r.allievo_id = s.id
        LEFT JOIN persone al ON s.persona_id = al.id
        LEFT JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN aule a ON r.aula_id = a.id
        LEFT JOIN assenze ass ON r.assenza_id = ass.id
        LEFT JOIN lezioni lo ON r.lezione_originale_id = lo.id
    ");
    echo "✓ v_recuperi_con_stato\n\n";
    
    $db->execute("
        CREATE VIEW IF NOT EXISTS v_assenze_da_recuperare AS
        SELECT 
            ass.*,
            al.cognome || ' ' || al.nome as allievo,
            d.cognome || ' ' || d.nome as docente,
            m.nome as materia,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            
            (SELECT COUNT(*) FROM recuperi WHERE assenza_id = ass.id AND annullato = 0) as recuperi_proposti,
            (SELECT COUNT(*) FROM assenze WHERE allievo_id = ass.allievo_id AND causata_da = 'allievo') as totale_assenze_allievo
            
        FROM assenze ass
        JOIN lezioni l ON ass.lezione_id = l.id
        JOIN soci s ON l.allievo_id = s.id
        JOIN persone al ON s.persona_id = al.id
        JOIN docenti d ON l.docente_id = d.id
        JOIN materie m ON l.materia_id = m.id
        WHERE ass.necessita_recupero = 1
        AND NOT EXISTS (
            SELECT 1 FROM recuperi r 
            WHERE r.assenza_id = ass.id 
            AND r.annullato = 0
            AND (r.confermata_da_docente = 1 OR r.confermata_da_segreteria = 1)
        )
    ");
    echo "✓ v_assenze_da_recuperare\n\n";
    
    // 4. Trigger
    echo "Creazione trigger...\n";
    $db->execute("
        CREATE TRIGGER IF NOT EXISTS recuperi_updated_at
        AFTER UPDATE ON recuperi
        FOR EACH ROW
        BEGIN
            UPDATE recuperi SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
        END
    ");
    echo "✓ recuperi_updated_at\n\n";
    
    // Verifica
    echo "=== VERIFICA ===\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='recuperi'");
    if (!empty($tables)) {
        echo "✓ Tabella recuperi esistente\n";
    }
    
    $views = $db->query("SELECT name FROM sqlite_master WHERE type='view' AND name LIKE 'v_%recuper%'");
    foreach ($views as $view) {
        echo "✓ View: {$view['name']}\n";
    }
    
    echo "\n✅ TABELLA RECUPERI CREATA CON SUCCESSO!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}