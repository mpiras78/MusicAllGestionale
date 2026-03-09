# Fase 2 - Preparazione e Istruzioni

## Fase 1 ✅ Completata - Migrazione Database Schema

**Data Inizio Fase 2**: Pronto per iniziare
**Stato**: In attesa di esecuzione

---

## Fase 2 - Aggiornamento Test Suite e Validazione

### Obiettivi Fase 2
1. Aggiornare tutti i test file (25+ file) per usare schema `soci`
2. Validare CRUD operations completamente
3. Aggiornare script di generazione dati
4. Verificare integrità referenziale
5. Test funzionalità calendario + recuperi

### File da Aggiornare in Fase 2

#### Test Files (8 file)
```
❌ tests/test_api_garau.php
❌ tests/test_sqlite.php
❌ tests/update_diaconita_lezioni.php
❌ tests/trova_socio_con_lezioni.php
❌ tests/test_api_lezioni.php
❌ tests/run_migration_anagrafica.php
❌ tests/Integration/DatabaseTest.php
❌ tests/test_api_browser.php
```

#### Python Scripts (4 file)
```
❌ tests/generate_week_sql.py
❌ tests/generate_week_simple.py
❌ tests/generate_mercoledi_sql.py
❌ tests/generate_martedi_sql.py
```

#### PHP Helper Scripts (5 file)
```
❌ tests/generate_lezioni_from_excel.php
❌ tests/fix_diaconita_materia.php
❌ tests/debug_garau.php
❌ tests/test_api_browser.php
❌ tests/test_check_tipologie.php
```

#### Documentation (2 file)
```
❌ SCHEMA_ER_DATABASE.md
❌ STATO_PROGETTO.md
```

### Istruzioni per Fase 2

#### Step 1: Aggiornare Test Files
```bash
# Per ogni file test:
# 1. Sostituisci: FROM soci → FROM soci
# 2. Sostituisci: socio_id → socio_id
# 3. Sostituisci: getSoci() → getSoci()
# 4. Verifica query SQL: SELECT * FROM soci WHERE...
#    → SELECT * FROM soci WHERE...
```

#### Step 2: Aggiornare Python Scripts
```python
# Pattern di sostituzione:
# 1. (SELECT id FROM soci WHERE UPPER(cognome)...)
#    → (SELECT id FROM soci WHERE UPPER(cognome)...)
# 2. UPDATE soci SET... → UPDATE soci SET...
# 3. INSERT INTO soci → INSERT INTO soci
```

#### Step 3: Validazione Database
```bash
# Verifica integrità
sqlite3 database/musicall.sqlite << EOF
  SELECT COUNT(*) as totale_soci FROM soci;
  SELECT COUNT(*) as backup_soci FROM soci_v2_backup;
  SELECT COUNT(*) as nuove_tabelle FROM sqlite_master 
    WHERE type='table' AND name LIKE '%iscrizioni%';
EOF
```

#### Step 4: Test CRUD Operations
```php
// Test script da creare
$sociCtrl = new SociController();

// CREATE
$new_id = $sociCtrl->createSocio([
    'nome' => 'Test',
    'cognome' => 'Socio'
]);

// READ
$socio = $sociCtrl->getSocioById($new_id);

// UPDATE
$sociCtrl->updateSocio($new_id, [
    'nome' => 'Test Updated'
]);

// DELETE (soft delete)
$sociCtrl->disattivaSocio($new_id);
```

### Comando per Avviare Fase 2

```bash
# 1. Checkout branch di feature
git checkout -b feature/fase2-test-suite

# 2. Aggiornare test file uno per uno
# (usa pattern di sostituzione sopra)

# 3. Eseguire test
php vendor/bin/phpunit

# 4. Commit incrementale
git add tests/
git commit -m "refactor(fase2): Aggiorna test file - pattern 1"

# 5. Merge in develop
git checkout develop
git merge feature/fase2-test-suite
```

### Retrocompatibilità

**Nota**: Fase 2 manterrà retrocompatibilità:
- `api_soci.php` continuerà a funzionare
- `SociController` avrà alias `getSoci()`
- Test vecchi potranno essere aggiornati gradualmente

### Metriche di Successo Fase 2

| Metrica | Target |
|---------|--------|
| Test coverage | >90% |
| Passati test | 100% |
| Data loss | 0 record |
| API response time | <200ms |
| Database consistency | ✓ Validated |

### Timeline Suggerito

| Fase | Tempo Stimato |
|------|--------------|
| Aggiornare 8 test PHP | 30 min |
| Aggiornare 4 script Python | 20 min |
| Aggiornare 5 helper script | 25 min |
| Aggiornare documentazione | 15 min |
| Eseguire test suite | 20 min |
| Fix eventuali errori | 30 min |
| **TOTALE** | **~2.5 ore** |

### Risk Assessment

🟡 **Medio** - Fase 2 è principalmente refactoring, non modifiche logica:
- ✅ Database già migrato (sicuro)
- ✅ Controller già aggiornato (sicuro)
- ⚠️ Test file potrebbero avere query complesse
- ⚠️ Python script potrebbero avere sintassi specifiche

### Rollback Plan

Se necessario rollback:
```bash
# 1. Revert commit
git revert HEAD~5..HEAD

# 2. Ripristina backup
# File backup: soci_v2_backup.sql (generabile da soci_v2_backup table)

# 3. Ripristina codebase
git checkout <commit-pre-fase1>
```

### Prossima Esecuzione

**Pronto per**: Fase 2 Aggiornamento Test Suite
**Prerequisiti**: Nessuno (Fase 1 ✅ completata)
**Comando per iniziare**:
```bash
cd c:\git\ct-poc\MusicAll
git log --oneline -5  # Verifica Fase 1 completata
# Poi procedi con Fase 2
```

---

## Summary Stato Attuale (Post-Fase 1)

```
✅ Database Migration: COMPLETATO
   - 245 record migrati soci → soci
   - 12 nuove tabelle create
   - 9 viste ricreate
   - 5 nuove colonne aggiunte

✅ Codebase Refactoring: COMPLETATO
   - SociController creato
   - 18 file aggiornati
   - 6 file API creati/aggiornati
   - 4 commit effettuati

❌ Test Suite: IN ATTESA
   - 25+ file da aggiornare
   - Stimato: 2.5 ore

❌ Documentazione Utente: IN ATTESA
❌ Validazione Produzione: IN ATTESA
```

---

## Contatti & Support

Per domande o problemi durante Fase 2:
- Verificare FASE_1_COMPLETED.md per dettagli Fase 1
- Consultare DATABASE_SCHEMA.md per schema aggiornato
- Controllare DEVELOPMENT_GUIDELINES.md per patterns

---

**Ready to Continue? → Start Fase 2** 🚀
