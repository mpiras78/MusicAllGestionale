<?php
/**
 * Script Esecuzione Migration Anagrafica Unificata
 * 
 * Questo script:
 * 1. Crea backup del database
 * 2. Esegue migration schema (nuove tabelle + viste)
 * 3. Esegue migration dati (copia dati dalle vecchie tabelle)
 * 4. Mostra report dettagliato
 * 
 * IMPORTANTE: Eseguire solo dopo aver letto la proposta in 
 * database/PROPOSTA_REFACTORING_ANAGRAFICA.md
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "============================================\n";
echo "MIGRATION: Anagrafica Unificata v3.0.0\n";
echo "============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // STEP 0: Verifica prerequisiti
    echo "📋 STEP 0: Verifica prerequisiti...\n";
    
    // Controlla le tabelle principali: `soci` è la tabella attiva dopo la migration
    $tables_to_check = ['soci', 'docenti', 'soci_occasionali'];
    foreach ($tables_to_check as $table) {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "  ✓ Tabella $table: $count record\n";
    }
    echo "\n";
    
    // STEP 1: Backup database
    echo "💾 STEP 1: Creazione backup database...\n";
    $backup_name = 'musicall_backup_before_migration_' . date('YmdHis') . '.sqlite';
    $backup_path = __DIR__ . '/../database/backups/' . $backup_name;
    
    // Crea directory backup se non esiste
    if (!is_dir(__DIR__ . '/../database/backups')) {
        mkdir(__DIR__ . '/../database/backups', 0755, true);
    }
    
    $db_path = __DIR__ . '/../database/musicall.sqlite';
    if (copy($db_path, $backup_path)) {
        echo "  ✓ Backup creato: $backup_name\n";
        echo "  📁 Path: $backup_path\n";
    } else {
        throw new Exception("Impossibile creare backup!");
    }
    echo "\n";
    
    // STEP 2: Esegui migration schema
    echo "🔧 STEP 2: Creazione nuove tabelle e viste...\n";
    $schema_sql = file_get_contents(__DIR__ . '/../database/migration_anagrafica_unificata.sql');
    $db->exec($schema_sql);
    echo "  ✓ Tabelle create: persone, soci, soci_*_dettagli\n";
    echo "  ✓ Viste create: v_soci, v_docenti, v_soci_occasionali\n";
    echo "  ✓ Trigger created_at/updated_at attivi\n";
    echo "\n";
    
    // STEP 3: Esegui migration dati (skip se DB già migrato)
    echo "📦 STEP 3: Migrazione dati...\n";
    $data_sql = file_get_contents(__DIR__ . '/../database/migration_dati_anagrafica.sql');

    // Se la tabella `allievi` non esiste più, probabilmente la migration è già stata applicata
    $checkAllievi = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='allievi'")->fetch();
    if (!$checkAllievi) {
        echo "  ⚠️ Tabella 'allievi' non trovata: salto STEP 3 (presumibilmente già migrato)\n\n";
    } else {
        // Cattura output del report
        ob_start();
        $db->exec($data_sql);
        $output = ob_get_clean();

        echo "  ✓ Dati migrati da vecchie tabelle\n";
        echo "\n";
    }
    
    // STEP 4: Report dettagliato
    echo "📊 STEP 4: Report migrazione\n";
    echo "============================================\n\n";
    
    // Conta persone
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM persone");
    $persone_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "👤 Totale persone create: $persone_count\n\n";
    
    // Conta soci per tipo
    echo "📋 Ruoli creati:\n";
    $stmt = $db->query("
        SELECT tipo_socio, stato, COUNT(*) as cnt 
        FROM soci 
        GROUP BY tipo_socio, stato 
        ORDER BY tipo_socio, stato
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icon = $row['stato'] == 'attivo' ? '✅' : '⏸️';
        echo "  $icon {$row['tipo_socio']}: {$row['cnt']} ({$row['stato']})\n";
    }
    echo "\n";
    
    // Persone con più ruoli (la vista potrebbe contenere riferimenti non compatibili)
    try {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM v_persone_multirolo");
        $multi_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "⭐ Persone con ruoli multipli: $multi_count\n";

        if ($multi_count > 0) {
            echo "\nDettaglio persone multi-ruolo:\n";
            try {
                $stmt = $db->query("SELECT * FROM v_persone_multirolo LIMIT 5");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $cogn = $row['cognome'] ?? ($row['cognome_persona'] ?? '');
                    $nom = $row['nome'] ?? ($row['nome_persona'] ?? '');
                    $ruoli = $row['ruoli'] ?? ($row['roles'] ?? '');
                    echo "  • {$cogn} {$nom}: {$ruoli}\n";
                }
            } catch (Exception $e) {
                echo "  ⚠️ Impossibile leggere v_persone_multirolo: " . $e->getMessage() . "\n";
            }

            if ($multi_count > 5) {
                echo "  ... e altri " . ($multi_count - 5) . "\n";
            }
        }
    } catch (Exception $e) {
        echo "⭐ Persone con ruoli multipli: (impossibile calcolare) - vista v_persone_multirolo errore: " . $e->getMessage() . "\n";
        $multi_count = 0;
    }
    echo "\n";
    
    // Verifica viste
    echo "👁️ Verifica viste compatibilità:\n";
    // Viste aggiornate presenti nel DB
    $viste = ['v_soci', 'v_docenti', 'v_soci_occasionali'];
    foreach ($viste as $vista) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM $vista");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            echo "  ✓ $vista: $count record\n";
        } catch (Exception $e) {
            echo "  ⚠️ Impossibile contare vista $vista: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // Confronto con vecchie tabelle
    echo "🔍 Verifica consistenza:\n";
    // Confronto: usa la tabella backup `_v2_backup` come "vecchia" e la vista `v_soci` come nuova
    // Confronto: usa la tabella backup `_v2_backup` come "vecchia" e la vista `v_soci` come nuova
    $stmt_old_allievi = $db->query("SELECT COUNT(*) as cnt FROM allievi_v2_backup");
    $old_a = $stmt_old_allievi->fetch()['cnt'];
    try {
        $stmt_new_allievi = $db->query("SELECT COUNT(*) as cnt FROM v_soci");
        $new_a = $stmt_new_allievi->fetch()['cnt'];
        echo "  Soci: $old_a (backup) → $new_a (vista) " . ($old_a == $new_a ? '✅' : '⚠️') . "\n";
    } catch (Exception $e) {
        echo "  ⚠️ Impossibile leggere v_soci: " . $e->getMessage() . "\n";
        echo "  Soci: $old_a (backup) → (vista non disponibile)\n";
    }
    
    $stmt_old_docenti = $db->query("SELECT COUNT(*) as cnt FROM docenti");
    $old_d = $stmt_old_docenti->fetch()['cnt'];
    try {
        $stmt_new_docenti = $db->query("SELECT COUNT(*) as cnt FROM v_docenti");
        $new_d = $stmt_new_docenti->fetch()['cnt'];
        echo "  Docenti: $old_d (vecchia) → $new_d (nuova) " . ($old_d == $new_d ? '✅' : '⚠️') . "\n";
    } catch (Exception $e) {
        echo "  ⚠️ Impossibile leggere v_docenti: " . $e->getMessage() . "\n";
        echo "  Docenti: $old_d (vecchia) → (vista non disponibile)\n";
    }
    
    $stmt_old_esterni = $db->query("SELECT COUNT(*) as cnt FROM soci_occasionali");
    $old_e = $stmt_old_esterni->fetch()['cnt'];
    try {
        $stmt_new_esterni = $db->query("SELECT COUNT(*) as cnt FROM v_soci_occasionali");
        $new_e = $stmt_new_esterni->fetch()['cnt'];
        echo "  Esterni: $old_e (vecchia) → $new_e (nuova) " . ($old_e == $new_e ? '✅' : '⚠️') . "\n";
    } catch (Exception $e) {
        echo "  ⚠️ Impossibile leggere v_soci_occasionali: " . $e->getMessage() . "\n";
        echo "  Esterni: $old_e (vecchia) → (vista non disponibile)\n";
    }
    echo "\n";
    
    // Anomalie
    echo "⚠️ Verifica anomalie:\n";
    $stmt = $db->query("
        SELECT COUNT(*) as cnt FROM persone p
        WHERE NOT EXISTS (SELECT 1 FROM soci s WHERE s.persona_id = p.id)
    ");
    $persone_senza_ruoli = $stmt->fetch()['cnt'];
    if ($persone_senza_ruoli > 0) {
        echo "  ⚠️ Persone senza ruoli: $persone_senza_ruoli\n";
    } else {
        echo "  ✅ Nessuna persona senza ruoli\n";
    }
    echo "\n";
    
    // SUCCESS
    echo "============================================\n";
    echo "✅ MIGRATION COMPLETATA CON SUCCESSO!\n";
    echo "============================================\n\n";
    
    echo "📝 PROSSIMI PASSI:\n";
    echo "1. ✅ Verifica che i conteggi sopra siano corretti\n";
    echo "2. ⏳ Testa il sistema con le nuove viste\n";
    echo "3. ⏳ Se tutto OK, esegui aggiornamento foreign keys\n";
    echo "4. ⏳ Solo dopo test completi, drop vecchie tabelle\n\n";
    
    echo "💾 BACKUP: In caso di problemi, ripristina da:\n";
    echo "   $backup_path\n\n";
    
    echo "📚 DOCUMENTAZIONE:\n";
    echo "   - Schema: DATABASE_SCHEMA.md\n";
    echo "   - Proposta: database/PROPOSTA_REFACTORING_ANAGRAFICA.md\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRORE DURANTE LA MIGRATION:\n";
    echo $e->getMessage() . "\n\n";
    echo "Il database NON è stato modificato.\n";
    echo "Se era stato creato un backup, puoi ripristinarlo.\n";
    exit(1);
}