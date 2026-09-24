<?php
/**
 * Script: Ricrea le viste dopo la migrazione da soci a soci
 * Aggiorna tutte le viste per usare la tabella "soci" al posto di "soci"
 */

try {
    $db_path = __DIR__ . '/musicall.sqlite';
    if (!file_exists($db_path)) {
        throw new Exception("Database non trovato: $db_path");
    }
    
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Ricreazione viste per schema soci...\n\n";
    
    // DROP viste vecchie se esistono (per sicurezza)
    $viste_vecchie = ['v_soci', 'v_assenze_da_recuperare', 'v_recuperi_con_stato', 
                      'v_calendario_unificato', 'v_pagamenti_dettagliati', 
                      'v_docenti', 'v_soci_occasionali', 'v_persone_multirolo', 
                      'v_calendario_completo'];
    foreach ($viste_vecchie as $vista) {
        try {
            $pdo->exec("DROP VIEW IF EXISTS $vista");
        } catch (PDOException $e) {
            // Ignora errori
        }
    }
    
    echo "📊 STEP 1: Ricreazione viste...\n\n";
    
    // Vista: v_soci (Soci attivi con nome completo)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_soci AS
        SELECT 
            id,
            cognome || ' ' || nome as nome_completo,
            cognome,
            nome,
            email,
            telefono,
            data_nascita,
            indirizzo,
            attivo
        FROM soci
        WHERE attivo = 1
        ORDER BY cognome, nome
    ");
    echo "✓ Vista v_soci creata\n";
    
    // Vista: v_assenze_da_recuperare (Assenze non recuperate)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_assenze_da_recuperare AS
        SELECT 
            a.id,
            a.data_assenza,
            a.causata_da,
            s.cognome || ' ' || s.nome as socio_nome,
            m.nome as materia,
            COUNT(r.id) as recuperi_programmati
        FROM assenze a
        JOIN soci s ON a.socio_id = s.id
        LEFT JOIN materie m ON a.materia_id = m.id
        LEFT JOIN recuperi r ON a.id = r.assenza_id AND r.annullato = 0
        WHERE a.da_recuperare = 1
        GROUP BY a.id
        ORDER BY a.data_assenza DESC
    ");
    echo "✓ Vista v_assenze_da_recuperare creata\n";
    
    // Vista: v_recuperi_con_stato (Recuperi con stato)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_recuperi_con_stato AS
        SELECT 
            r.id,
            r.data_recupero,
            r.ora_inizio,
            r.ora_fine,
            s.cognome || ' ' || s.nome as socio_nome,
            m.nome as materia,
            d.cognome || ' ' || d.nome as docente_nome,
            CASE 
                WHEN r.annullato = 1 THEN 'Annullato'
                WHEN r.completato = 1 THEN 'Completato'
                WHEN r.data_recupero < DATE('now') THEN 'Scaduto'
                ELSE 'Programmato'
            END as stato,
            au.nome as aula
        FROM recuperi r
        JOIN soci s ON r.socio_id = s.id
        JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN aule au ON r.aula_id = au.id
        ORDER BY r.data_recupero DESC
    ");
    echo "✓ Vista v_recuperi_con_stato creata\n";
    
    // Vista: v_calendario_unificato (Lezioni + Recuperi unificati nel calendario)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_calendario_unificato AS
        SELECT 
            'lezione' as tipo,
            l.id,
            l.giorno_settimana,
            l.ora_inizio,
            l.ora_fine,
            s.cognome || ' ' || s.nome as socio_nome,
            d.cognome || ' ' || d.nome as docente_nome,
            m.nome as materia,
            au.nome as aula,
            CAST(NULL as TEXT) as data_evento
        FROM lezioni l
        JOIN soci s ON l.socio_id = s.id
        JOIN docenti d ON l.docente_id = d.id
        LEFT JOIN materie m ON l.materia_id = m.id
        LEFT JOIN aule au ON l.aula_id = au.id
        WHERE l.attiva = 1
        
        UNION ALL
        
        SELECT 
            'recupero' as tipo,
            r.id,
            STRFTIME('%w', r.data_recupero) as giorno_settimana,
            r.ora_inizio,
            r.ora_fine,
            s.cognome || ' ' || s.nome as socio_nome,
            d.cognome || ' ' || d.nome as docente_nome,
            m.nome as materia,
            au.nome as aula,
            r.data_recupero
        FROM recuperi r
        JOIN soci s ON r.socio_id = s.id
        JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN aule au ON r.aula_id = au.id
        WHERE r.annullato = 0
        AND r.data_recupero >= DATE('now')
    ");
    echo "✓ Vista v_calendario_unificato creata\n";
    
    // Vista: v_pagamenti_dettagliati (Dettagli pagamenti per soci)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_pagamenti_dettagliati AS
        SELECT 
            p.id,
            s.cognome || ' ' || s.nome as socio_nome,
            p.importo,
            p.data_pagamento,
            mp.nome as modalita,
            p.note,
            p.confermato
        FROM pagamenti p
        JOIN soci s ON p.socio_id = s.id
        LEFT JOIN modalita_pagamento mp ON p.modalita_pagamento_id = mp.id
        WHERE p.importo > 0
        ORDER BY p.data_pagamento DESC
    ");
    echo "✓ Vista v_pagamenti_dettagliati creata\n";
    
    // Vista: v_docenti (Docenti con statistiche)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_docenti AS
        SELECT 
            d.id,
            d.cognome || ' ' || d.nome as nome_completo,
            d.cognome,
            d.nome,
            COUNT(DISTINCT l.id) as num_lezioni,
            COUNT(DISTINCT s.id) as num_soci,
            d.attivo
        FROM docenti d
        LEFT JOIN lezioni l ON d.id = l.docente_id AND l.attiva = 1
        LEFT JOIN soci s ON l.socio_id = s.id AND s.attivo = 1
        GROUP BY d.id
        ORDER BY d.cognome, d.nome
    ");
    echo "✓ Vista v_docenti creata\n";
    
    // Vista: v_soci_occasionali (Soci senza lezioni programmate)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_soci_occasionali AS
        SELECT DISTINCT
            s.id,
            s.cognome || ' ' || s.nome as nome_completo,
            s.email,
            s.telefono,
            COUNT(DISTINCT ia.id) as num_iscrizioni
        FROM soci s
        LEFT JOIN iscrizioni_annuali ia ON s.id = ia.socio_id
        LEFT JOIN lezioni l ON s.id = l.socio_id AND l.attiva = 1
        WHERE s.attivo = 1 AND l.id IS NULL
        GROUP BY s.id
        ORDER BY s.cognome, s.nome
    ");
    echo "✓ Vista v_soci_occasionali creata\n";
    
    // Vista: v_persone_multirolo (Persone che sono sia soci che docenti)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_persone_multirolo AS
        SELECT 
            s.id as socio_id,
            d.id as docente_id,
            s.cognome || ' ' || s.nome as nome_completo,
            'soci_e_docenti' as ruoli
        FROM soci s
        JOIN docenti d ON 
            LOWER(s.cognome) = LOWER(d.cognome) 
            AND LOWER(s.nome) = LOWER(d.nome)
        WHERE s.attivo = 1 AND d.attivo = 1
    ");
    echo "✓ Vista v_persone_multirolo creata\n";
    
    // Vista: v_calendario_completo (Calendario unificato con dettagli)
    $pdo->exec("
        CREATE VIEW IF NOT EXISTS v_calendario_completo AS
        SELECT 
            'lezione' as evento_tipo,
            l.id as evento_id,
            l.giorno_settimana,
            l.ora_inizio as ora_evento,
            l.ora_fine as ora_fine,
            s.cognome || ' ' || s.nome as socio_nome,
            d.cognome || ' ' || d.nome as docente_nome,
            m.nome as materia_nome,
            au.nome as aula_nome,
            CAST(NULL as TEXT) as data_evento,
            'Attiva' as stato
        FROM lezioni l
        JOIN soci s ON l.socio_id = s.id
        JOIN docenti d ON l.docente_id = d.id
        LEFT JOIN materie m ON l.materia_id = m.id
        LEFT JOIN aule au ON l.aula_id = au.id
        WHERE l.attiva = 1
        
        UNION ALL
        
        SELECT 
            'recupero' as evento_tipo,
            r.id as evento_id,
            '' as giorno_settimana,
            r.ora_inizio as ora_evento,
            r.ora_fine as ora_fine,
            s.cognome || ' ' || s.nome as socio_nome,
            d.cognome || ' ' || d.nome as docente_nome,
            m.nome as materia_nome,
            au.nome as aula_nome,
            r.data_recupero,
            CASE 
                WHEN r.annullato = 1 THEN 'Annullato'
                WHEN r.completato = 1 THEN 'Completato'
                ELSE 'Programmato'
            END
        FROM recuperi r
        JOIN soci s ON r.socio_id = s.id
        JOIN docenti d ON r.docente_id = d.id
        LEFT JOIN materie m ON r.materia_id = m.id
        LEFT JOIN aule au ON r.aula_id = au.id
        ORDER BY data_evento DESC, ora_evento
    ");
    echo "✓ Vista v_calendario_completo creata\n";
    
    echo "\n✅ Tutte le viste ricreate con successo!\n";
    
    // Verifica viste create
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='view' ORDER BY name");
    $viste = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Viste presenti nel database (" . count($viste) . "):\n";
    foreach ($viste as $row) {
        echo "  - " . $row['name'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    exit(1);
}
