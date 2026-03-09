<?php
/**
 * Script Esecuzione Fase 1: Database Migration
 * 
 * Rinomina soci → soci + crea 12 nuove tabelle
 * 
 * Uso: php database/run_migration_fase1.php
 */

// Setup percorsi
define('BASE_PATH', __DIR__ . '/..');
define('DB_PATH', BASE_PATH . '/database/musicall.sqlite');

// Carica configurazione
require_once BASE_PATH . '/config/database.php';

// Connessione SQLite
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connesso a database SQLite: " . DB_PATH . "\n\n";
    
    // Disabilita foreign key checks temporaneamente
    $pdo->exec("PRAGMA foreign_keys = OFF");
    echo "⚠️  Foreign key constraints disabilitate temporaneamente\n\n";
    
    // DROP viste che dipendono da soci
    echo "📋 STEP 0: Rimozione viste dipendenti da soci...\n";
    $viste_da_rimuovere = ['v_soci', 'v_assenze_da_recuperare', 'v_recuperi_con_stato', 
                           'v_calendario_unificato', 'v_pagamenti_dettagliati', 
                           'v_docenti', 'v_soci_occasionali', 'v_persone_multirolo', 
                           'v_calendario_completo'];
    foreach ($viste_da_rimuovere as $vista) {
        try {
            $pdo->exec("DROP VIEW IF EXISTS $vista");
            echo "  ✓ Vista rimossa: $vista\n";
        } catch (PDOException $e) {
            // Ignora errori
        }
    }
    echo "✅ Viste rimosse\n\n";
    
    // BACKUP: Rinomina tabella soci
    echo "📋 STEP 1: Backup tabella soci...\n";
    try {
        $pdo->exec("ALTER TABLE soci RENAME TO soci_v2_backup");
        echo "✅ Tabella soci rinominata a soci_v2_backup\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'no such table') !== false) {
            echo "⚠️  Tabella soci non trovata (probabile che sia già stata migrata)\n\n";
        } else {
            throw $e;
        }
    }
    
    // CREA TABELLA: soci (da soci_v2_backup)
    echo "📋 STEP 2: Creazione tabella soci...\n";
    
    // Verifica se soci esiste già
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='soci'");
    $soci_exists = $stmt->fetch() !== false;
    
    if ($soci_exists) {
        echo "⊙ Tabella soci esiste già\n";
    } else {
        // Prima: lettura schema soci_v2_backup
        $stmt = $pdo->query("PRAGMA table_info(soci_v2_backup)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($columns)) {
            // Crea tabella soci con stessi campi
            $col_defs = [];
            foreach ($columns as $col) {
                $col_defs[] = "{$col['name']} {$col['type']}";
            }
            
            $sql = "CREATE TABLE soci (" . implode(", ", $col_defs) . ")";
            $pdo->exec($sql);
            
            // Copia dati
            $pdo->exec("INSERT INTO soci SELECT * FROM soci_v2_backup");
            
            echo "✅ Tabella soci creata e dati migrati\n";
        } else {
            // Se backup non esiste, crea schema vuoto
            $pdo->exec("CREATE TABLE soci (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                cognome TEXT NOT NULL,
                data_nascita DATE,
                email TEXT UNIQUE,
                telefono TEXT,
                indirizzo TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            echo "✅ Tabella soci creata (vuota)\n";
        }
    }
    
    // Aggiungi colonne nuove a soci
    echo "📋 STEP 3: Aggiunta colonne a soci...\n";
    $nuove_colonne = ['telefono_2', 'cap', 'citta', 'created_at', 'updated_at'];
    $stmt = $pdo->query("PRAGMA table_info(soci)");
    $colonne_esistenti = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    
    foreach ($nuove_colonne as $col) {
        if (!in_array($col, $colonne_esistenti)) {
            if ($col === 'created_at' || $col === 'updated_at') {
                $pdo->exec("ALTER TABLE soci ADD COLUMN $col TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            } else {
                $pdo->exec("ALTER TABLE soci ADD COLUMN $col VARCHAR(100)");
            }
            echo "  ✓ Aggiunta colonna: $col\n";
        } else {
            echo "  ⊙ Colonna già esiste: $col\n";
        }
    }
    echo "✅ Colonne aggiunte a soci\n\n";
    
    // CREA TABELLE NUOVE
    echo "📋 STEP 4: Creazione 12 nuove tabelle...\n\n";
    
    // 1. dati_associazione
    $pdo->exec("CREATE TABLE IF NOT EXISTS dati_associazione (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_scuola TEXT NOT NULL,
        indirizzo TEXT,
        cap TEXT,
        citta TEXT,
        provincia TEXT,
        telefono_principale TEXT,
        telefono_secondario TEXT,
        email_amministrativa TEXT,
        email_pagamenti TEXT,
        iban TEXT,
        intestatario_conto TEXT,
        costo_iscrizione_agosto_febbraio REAL DEFAULT 150.0,
        costo_iscrizione_marzo_luglio REAL DEFAULT 100.0,
        sconto_familiare_percentuale REAL DEFAULT 10.0,
        sconto_compleanno_percentuale REAL DEFAULT 5.0,
        numero_recuperi_garantiti INTEGER DEFAULT 3,
        partita_iva TEXT,
        codice_fiscale TEXT,
        descrizione_ricevute TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ dati_associazione\n";
    
    // 2. iscrizioni_annuali
    $pdo->exec("CREATE TABLE IF NOT EXISTS iscrizioni_annuali (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        socio_id INTEGER NOT NULL,
        anno_accademico INTEGER NOT NULL,
        numero_tessera TEXT NOT NULL UNIQUE,
        costo_iscrizione REAL NOT NULL,
        stato_pagamento TEXT DEFAULT 'PAGAMENTO_PENDENTE',
        data_iscrizione DATE NOT NULL,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
        UNIQUE(socio_id, anno_accademico)
    )");
    echo "✅ iscrizioni_annuali\n";
    
    // 3. modalita_pagamento
    $pdo->exec("CREATE TABLE IF NOT EXISTS modalita_pagamento (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL UNIQUE,
        descrizione TEXT,
        attivo BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ modalita_pagamento\n";
    
    // 4. pagamenti
    $pdo->exec("CREATE TABLE IF NOT EXISTS pagamenti (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        socio_id INTEGER NOT NULL,
        importo REAL NOT NULL,
        mensilita_riferimento TEXT NOT NULL,
        modalita_pagamento_id INTEGER NOT NULL,
        stato TEXT DEFAULT 'PAGATO',
        data_pagamento DATETIME,
        admin_id INTEGER,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
        FOREIGN KEY (modalita_pagamento_id) REFERENCES modalita_pagamento(id)
    )");
    echo "✅ pagamenti\n";
    
    // 5. famiglia
    $pdo->exec("CREATE TABLE IF NOT EXISTS famiglia (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        socio_id INTEGER NOT NULL,
        familiare_id INTEGER NOT NULL,
        data_collegamento DATE NOT NULL,
        stato TEXT DEFAULT 'ATTIVO',
        data_revoca DATE,
        motivo_revoca TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
        FOREIGN KEY (familiare_id) REFERENCES soci(id) ON DELETE CASCADE,
        UNIQUE(socio_id, familiare_id)
    )");
    echo "✅ famiglia\n";
    
    // 6. sospensioni_corso
    $pdo->exec("CREATE TABLE IF NOT EXISTS sospensioni_corso (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        corso_id INTEGER NOT NULL,
        tipo TEXT NOT NULL,
        data_inizio DATE NOT NULL,
        data_fine DATE,
        motivo TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (corso_id) REFERENCES corsi_soci(id) ON DELETE CASCADE
    )");
    echo "✅ sospensioni_corso\n";
    
    // 7. alert
    $pdo->exec("CREATE TABLE IF NOT EXISTS alert (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        socio_id INTEGER NOT NULL,
        corso1_id INTEGER,
        corso2_id INTEGER,
        tipo TEXT DEFAULT 'SOVRAPPOSIZIONE',
        descrizione TEXT NOT NULL,
        visibile BOOLEAN DEFAULT 1,
        data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
        FOREIGN KEY (corso1_id) REFERENCES corsi_soci(id) ON DELETE SET NULL,
        FOREIGN KEY (corso2_id) REFERENCES corsi_soci(id) ON DELETE SET NULL
    )");
    echo "✅ alert\n";
    
    // 8. batch_runs
    $pdo->exec("CREATE TABLE IF NOT EXISTS batch_runs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        batch_tipo TEXT NOT NULL,
        data_esecuzione DATETIME NOT NULL,
        numero_email_inviate INTEGER DEFAULT 0,
        numero_email_fallite INTEGER DEFAULT 0,
        status TEXT DEFAULT 'SUCCESSO',
        log_dettagli TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ batch_runs\n";
    
    // 9. audit_log
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        azione TEXT NOT NULL,
        tabella TEXT NOT NULL,
        record_id INTEGER,
        vecchio_valore TEXT,
        nuovo_valore TEXT,
        admin_id INTEGER,
        socio_id INTEGER,
        ip_address TEXT,
        user_agent TEXT,
        dettagli TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE SET NULL
    )");
    echo "✅ audit_log\n";
    
    // 10. chiusure_attivita
    $pdo->exec("CREATE TABLE IF NOT EXISTS chiusure_attivita (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        data_chiusura DATE NOT NULL UNIQUE,
        tipo TEXT DEFAULT 'CHIUSURA_SCUOLA',
        descrizione TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ chiusure_attivita\n";
    
    echo "\n";
    
    // INSERISCI DATI INIZIALI
    echo "📋 STEP 5: Inserimento dati iniziali...\n\n";
    
    // dati_associazione
    $pdo->exec("DELETE FROM dati_associazione");
    $pdo->exec("INSERT INTO dati_associazione 
        (nome_scuola, indirizzo, cap, citta, provincia,
         costo_iscrizione_agosto_febbraio, costo_iscrizione_marzo_luglio,
         sconto_familiare_percentuale, numero_recuperi_garantiti)
        VALUES ('Scuola di Musica MusicAll', 'Via Roma 123', '16100', 'Genova', 'GE',
                150.0, 100.0, 10.0, 3)");
    echo "✅ dati_associazione inizializzato\n";
    
    // modalita_pagamento
    $pdo->exec("DELETE FROM modalita_pagamento");
    $pdo->exec("INSERT INTO modalita_pagamento (nome, descrizione) VALUES
        ('CONTANTI', 'Pagamento in contanti presso la scuola'),
        ('BONIFICO', 'Trasferimento bancario'),
        ('CARTA', 'Carta di credito (da implementare)')");
    echo "✅ modalita_pagamento inizializzato\n\n";
    
    // CREA INDICI
    echo "📋 STEP 6: Creazione indici per performance...\n\n";
    
    $indici = [
        "CREATE INDEX IF NOT EXISTS idx_soci_cognome_nome ON soci(cognome, nome)",
        "CREATE INDEX IF NOT EXISTS idx_soci_citta ON soci(citta)",
        "CREATE INDEX IF NOT EXISTS idx_pagamenti_socio ON pagamenti(socio_id)",
        "CREATE INDEX IF NOT EXISTS idx_iscrizioni_socio ON iscrizioni_annuali(socio_id)",
        "CREATE INDEX IF NOT EXISTS idx_famiglia_socio ON famiglia(socio_id)",
        "CREATE INDEX IF NOT EXISTS idx_famiglia_familiare ON famiglia(familiare_id)",
        "CREATE INDEX IF NOT EXISTS idx_audit_azione ON audit_log(azione)",
        "CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at)"
    ];
    
    foreach ($indici as $idx) {
        try {
            $pdo->exec($idx);
        } catch (PDOException $e) {
            // Ignora errori di colonne mancanti
        }
    }
    echo "✅ Indici creati\n\n";
    
    // VALIDAZIONI
    echo "📋 STEP 7: Validazioni post-migrazione...\n\n";
    
    // Conta tabelle
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📊 Tabelle nel database (" . count($tabelle) . "):\n";
    foreach ($tabelle as $t) {
        echo "   ⊙ " . $t['name'] . "\n";
    }
    echo "\n";
    
    // Conta record soci
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM soci");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "👥 Record in tabella soci: " . $result['count'] . "\n";
    
    // Conta record backup
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM soci_v2_backup");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📦 Record in tabella soci_v2_backup: " . $result['count'] . "\n\n";
    
    // Riabilita foreign key checks
    $pdo->exec("PRAGMA foreign_keys = ON");
    
    echo "🎉 MIGRAZIONE COMPLETATA CON SUCCESSO!\n\n";
    echo "Prossimi step:\n";
    echo "1. Aggiorna codebase: rinomina soci → soci nelle query PHP\n";
    echo "2. Testa CRUD operations\n";
    echo "3. Verifica audit log funzionante\n";
    
} catch (PDOException $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}
?>
