# Release Notes - Versione 2.3.0

**Data di rilascio:** 16 Febbraio 2026

## 🎯 Nuove Funzionalità

### Logica Recuperi Assenze Migliorata
- **Nuova regola di business per recuperi obbligatori**:
  - Assenze **docente**: sempre da recuperare (obbligatorio)
  - Assenze **socio**: 
    - Prime 3 assenze per lezione nell'anno scolastico → recupero obbligatorio
    - Dalla 4ª assenza in poi → recupero a discrezione (direzione/insegnante)

### Inversione Icone Prenotazioni
- **Scambiate le icone** per prenotazioni nel calendario:
  - Prenotazione Soci: ora usa `bi-person-badge` (badge persona)
  - Prenotazione Docente: ora usa `bi-door-open` (porta aperta)
  - Prenotazione Esterno: rimane `bi-calendar-event` (invariata)

## 🔧 Modifiche Tecniche

### API e Controller
- **`api_salva_assenza_calendario.php`**:
  - Implementata logica automatica per determinare `da_recuperare`
  - Utilizza `getContatoriAnnoScolastico()` per contare assenze precedenti
  - Applica automaticamente la regola delle 3 assenze obbligatorie

### Database
- **Script di migrazione** `tests/fix_da_recuperare_logic.php`:
  - Aggiorna tutte le assenze esistenti secondo la nuova logica
  - Processa assenze per anno scolastico corrente (settembre-giugno)
  - Raggruppa per socio-lezione e applica la regola posizionale

- **Script inversione icone** `tests/swap_icone_prenotazioni.php`:
  - Inverte icone tra PREN_SALA_SOCI e PREN_DOCENTE
  - Mantiene traccia del prima/dopo per verifica

### Script di Utilità
- **`tests/check_icone_db.php`**: Verifica icone presenti nel database e nel codice

## 📊 Statistiche Migrazione

### Assenze Processate
- **Assenze Docente**: tutte impostate a `da_recuperare = 1`
- **Assenze Socio**: 6 processate nell'anno scolastico 2025/2026
  - Tutte prime assenze per le rispettive lezioni
  - Tutte impostate a `da_recuperare = 1`

### Icone Aggiornate
- 2 tipologie prenotazione con icone invertite
- Nessun impatto su prenotazioni esistenti

## 🔄 Compatibilità

### Backward Compatibility
- ✅ Completamente retrocompatibile
- ✅ Nessuna modifica schema database
- ✅ Dati esistenti aggiornati automaticamente tramite script migrazione

### Breaking Changes
- ❌ Nessuno

## 📝 Note di Upgrade

### Passi Consigliati
1. **Eseguire backup** del database prima dell'upgrade
2. **Eseguire script migrazione**: `php tests/fix_da_recuperare_logic.php`
3. **Eseguire script icone**: `php tests/swap_icone_prenotazioni.php`
4. **Verificare** che le icone nel calendario siano corrette
5. **Testare** la creazione di nuove assenze dal calendario

### Verifica Post-Upgrade
```bash
# Verifica flag da_recuperare
php tests/fix_da_recuperare_logic.php

# Verifica icone
php tests/check_icone_db.php
```

## 🐛 Bug Fix
- Corretta logica errata che impostava `da_recuperare = 0` per tutte le assenze socio
- Allineato comportamento API con policy scuola

## 📖 Documentazione

### Regole Business Implementate
1. **Anno scolastico**: settembre anno N → giugno anno N+1
2. **Conteggio assenze**: per coppia socio-lezione nell'anno scolastico
3. **Soglia recupero obbligatorio**: prime 3 assenze
4. **Recuperi docente**: sempre obbligatori indipendentemente dal numero

### File Modificati
- `api_salva_assenza_calendario.php` - Logica recuperi automatica
- `tests/fix_da_recuperare_logic.php` - Script migrazione dati esistenti
- `tests/swap_icone_prenotazioni.php` - Script inversione icone
- `tests/check_icone_db.php` - Script verifica icone
- Database: campo `icona` in `tipologie_evento` (PREN_SALA_SOCI, PREN_DOCENTE)

## 🎓 Impatto Utenti

### Utenti Finali
- ✅ Registrazione assenze dal calendario più intelligente
- ✅ Icone più intuitive nel modal prenotazioni
- ✅ Filtro "Non necessario" in Gestione Assenze ora funziona correttamente

### Amministratori
- ✅ Migrazione automatica dati esistenti
- ✅ Nuovi script di verifica e manutenzione
- ✅ Log dettagliati delle operazioni di migrazione

## 🔮 Prossimi Sviluppi
- Possibile aggiunta configurazione soglia recuperi (attualmente hardcoded a 3)
- Dashboard statistiche recuperi per socio/lezione
- Notifiche automatiche al raggiungimento soglia recuperi

---

**Versione precedente:** 2.2.0  
**Versione successiva:** TBD