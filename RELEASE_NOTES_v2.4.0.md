# Release Notes - Versione 2.4.0

**Data di rilascio:** 17 Febbraio 2026

## 🐛 Bug Fix Critici

### Controllo Sovrapposizione Lezioni di Prova
- **Problema risolto**: Le lezioni di prova si sovrapponevano ad altri eventi causando la scomparsa dal calendario
- **Causa**: Il controllo verificava solo la tabella `eventi_calendario`, ignorando le lezioni ricorrenti in `lezioni`
- **Soluzione**: Implementato controllo completo su TUTTE le tipologie di eventi

## 🔧 Modifiche Tecniche

### API Lezioni di Prova
- **`api_lezioni_prova.php`**:
  - Aggiunto controllo sovrapposizione con **eventi_calendario** (recuperi, prenotazioni, altre lezioni di prova)
  - Aggiunto controllo sovrapposizione con **lezioni ricorrenti** (lezioni settimanali programmate)
  - Gestione intelligente delle assenze: se una lezione ricorrente ha un'assenza registrata per quella data, lo slot è considerato disponibile
  - Calcolo automatico del giorno della settimana dalla data per verificare lezioni ricorrenti

### Logica di Controllo
```php
// 1. Verifica eventi specifici (eventi_calendario)
// 2. Verifica lezioni ricorrenti (lezioni) per quel giorno della settimana
// 3. Esclude lezioni con assenza registrata (slot disponibile)
```

## 📊 Dettagli Implementazione

### Query di Controllo
1. **Eventi Calendario**: Verifica sovrapposizioni orarie nella stessa aula per la data specifica
2. **Lezioni Ricorrenti**: 
   - Calcola giorno settimana dalla data (lunedì-domenica)
   - Verifica lezioni programmate per quel giorno/aula/orario
   - JOIN con tabella assenze per escludere slot con assenze registrate

### Messaggi di Errore
- Conflitto con evento: `"Conflitto orario: [titolo evento] già presente dalle HH:MM alle HH:MM"`
- Conflitto con lezione: `"Conflitto orario: lezione di [allievo] ([materia]) già presente dalle HH:MM alle HH:MM"`

## 🔄 Compatibilità

### Backward Compatibility
- ✅ Completamente retrocompatibile
- ✅ Nessuna modifica schema database
- ✅ Nessun impatto su funzionalità esistenti

### Breaking Changes
- ❌ Nessuno

## 📝 Note di Upgrade

### Passi Richiesti
1. **Aggiornare file**: Sostituire `api_lezioni_prova.php` con la nuova versione
2. **Nessuna migrazione database richiesta**
3. **Testare**: Creare una lezione di prova in uno slot già occupato per verificare il blocco

### Verifica Post-Upgrade
- Tentare di creare lezione di prova su slot occupato → deve mostrare errore
- Tentare di creare lezione di prova su slot con assenza → deve permettere la creazione
- Verificare che gli eventi non scompaiano più dal calendario

## 🎯 Impatto Utenti

### Utenti Finali
- ✅ Impossibile creare lezioni di prova sovrapposte
- ✅ Messaggi di errore chiari indicano il conflitto
- ✅ Eventi non scompaiono più dal calendario
- ✅ Slot con assenze sono correttamente disponibili per lezioni di prova

### Amministratori
- ✅ Integrità dati garantita
- ✅ Nessuna sovrapposizione possibile
- ✅ Log errori più informativi

## 🔍 Test Consigliati

1. **Test Conflitto Evento**: Creare lezione di prova su recupero esistente → deve bloccare
2. **Test Conflitto Lezione**: Creare lezione di prova su lezione ricorrente → deve bloccare
3. **Test Slot Assenza**: Creare lezione di prova su lezione con assenza → deve permettere
4. **Test Slot Libero**: Creare lezione di prova su slot vuoto → deve permettere

## 📖 File Modificati

- `api_lezioni_prova.php` - Controllo sovrapposizione completo

## 🔮 Prossimi Sviluppi

- Estendere controllo sovrapposizione anche a prenotazioni rapide
- Suggerire slot alternativi disponibili in caso di conflitto
- Dashboard conflitti per amministratori

---

**Versione precedente:** 2.3.0  
**Versione successiva:** TBD
