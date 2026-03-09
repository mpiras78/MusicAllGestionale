# Release Notes - MusicAll v2.1.0

**Data Rilascio:** 14 Febbraio 2026  
**Tipo:** Minor Release - Miglioramenti Prenotazioni Soci Esterni

---

## 🎯 Nuove Funzionalità

### Sistema Gestione Soci Esterni
- ✅ **Registrazione soci esterni nel database**
  - Nuova tabella `soci_esterni` per anagrafica centralizzata
  - Campi: nome, cognome, email, telefono, note
  - Flag `attivo` per gestione stato
  - Timestamp automatici (created_at, updated_at)

- ✅ **Interfaccia prenotazioni migliorata**
  - Select con elenco soci esterni già registrati
  - Opzione "➕ Nuovo Socio Esterno" per registrazione rapida
  - Campi dinamici (mostrati solo quando necessario)
  - Validazione differenziata per socio esistente vs nuovo

- ✅ **API per gestione soci**
  - `api_get_soci_esterni.php` - Lista soci attivi
  - Integrazione con `api_salva_prenotazione.php`
  - Creazione automatica nuovo socio se necessario

---

## 🐛 Bug Fix Critici

### Fix Visualizzazione Eventi
- ✅ **Nome esterno corretto nel calendario**
  - Aggiunto JOIN con tabella `soci_esterni` nella query SQL
  - Visualizzazione nome reale invece di "ESTERNO" generico

- ✅ **Click recupero apre modal corretta**
  - Tutti gli eventi (recuperi e prenotazioni) usano `mostraInfoEvento()`
  - Rimossa logica condizionale errata che apriva modal info socio

### Fix UI e Layout
- ✅ **Sovrapposizione icona/testo risolta**
  - Padding-left applicato SOLO alle prenotazioni
  - Lezioni e recuperi mantengono allineamento sinistro
  - CSS specifico per classi `.tipo-prenotazione-*`

- ✅ **Backdrop modal bloccante**
  - Event listener globale per pulizia backdrop
  - Ripristino scrollbar e interattività pagina
  - Fix funziona con ESC, click X, click fuori, chiusura programmatica

- ✅ **Reload rapido dopo salvataggio**
  - Timeout ridotto da 1500ms a 800ms
  - Prevenzione blocco UI da chiusura toast

---

## 🔧 Miglioramenti Tecnici

### Database
- Tabella `soci_esterni` con indici ottimizzati
- Relazione `eventi_calendario.socio_occasionale_id` → `soci_esterni.id`
- Migration script: `database/migration_soci_esterni.sql`

### Backend
- Query JOIN ottimizzata per caricamento eventi
- Validazione server-side per dati soci esterni
- Gestione anti-duplicati (ricerca per email)

### Frontend
- Caricamento dinamico select soci esterni
- Toggle automatico campi nuovo socio
- Validazione client-side migliorata
- Event listener Bootstrap per cleanup modal

---

## 📊 Impatto Utente

### User Experience
- ✅ **Prenotazioni più veloci**: Select soci esistenti
- ✅ **Meno duplicati**: Riutilizzo anagrafica esistente
- ✅ **UI più pulita**: Layout allineato e professionale
- ✅ **Nessun blocco**: Modal chiudono correttamente

### Workflow
1. **Socio già registrato**: 2 click (select + crea)
2. **Nuovo socio**: Compila form + crea (auto-registrazione)
3. **Visualizzazione**: Nome reale visibile nel calendario
4. **Annullamento**: Modal corrette per ogni tipo evento

---

## 🔄 Compatibilità

- ✅ **Database**: Compatibile con v2.0.0 (richiede migration)
- ✅ **API**: Retrocompatibile
- ✅ **Browser**: Testato su Chrome, Firefox, Edge, Safari
- ✅ **Bootstrap**: 5.x (fix backdrop compatibile)

---

## 📦 File Modificati

### Nuovi File
- `api_get_soci_esterni.php` - API lista soci
- `database/migration_soci_esterni.sql` - Schema database
- `tests/run_soci_esterni_migration.php` - Script migrazione

### File Aggiornati
- `calendario.php` - UI prenotazioni + fix modal
- `assets/css/style.css` - Padding selettivo prenotazioni
- `api_salva_prenotazione.php` - Gestione soci esterni (già presente)

---

## ⚠️ Note di Upgrade

### Installazione
```bash
# 1. Esegui migration database
php tests/run_soci_esterni_migration.php

# 2. Verifica creazione tabella
sqlite3 database/musicall.sqlite "SELECT COUNT(*) FROM soci_esterni;"

# 3. Testa funzionalità
# Apri calendario → Click su slot vuoto → Prenotazione Esterno
```

### Verifica
- Tabella `soci_esterni` creata
- Select soci esterni popolato al caricamento modal
- Opzione "➕ Nuovo Socio" presente
- Nome esterno visualizzato nei box calendario
- Modal chiudono senza blocchi

---

## 🎉 Conclusioni

Versione **2.1.0** migliora significativamente:
- **Gestione soci esterni** con anagrafica centralizzata
- **User Experience** con UI più pulita e responsive
- **Stabilità** con fix backdrop modal e layout

Sistema **100% funzionante** e pronto per produzione! 🚀

---

## 👥 Credits

**Sviluppo:** MusicAll Development Team  
**Testing:** Internal QA  
**Data:** 14 Febbraio 2026