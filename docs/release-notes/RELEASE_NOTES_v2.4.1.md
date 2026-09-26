# Release Notes - Versione 2.4.1

**Data di rilascio:** 17 Febbraio 2026

## 🐛 Bug Fix

### Miglioramento Controllo Sovrapposizione Lezioni di Prova
- **Problema**: Il controllo sovrapposizione verificava solo `eventi_calendario`, ignorando le lezioni ricorrenti settimanali
- **Impatto**: Lezioni di prova potevano sovrapporsi a lezioni regolari, causando conflitti e scomparsa eventi dal calendario
- **Soluzione**: Controllo completo su TUTTE le tipologie di lezioni/eventi

## 🔧 Modifiche Tecniche

### API Lezioni di Prova (`api_lezioni_prova.php`)

#### Controllo Sovrapposizione Completo
1. **Verifica Eventi Calendario**:
   - Controlla `eventi_calendario` per recuperi, prenotazioni, altre lezioni di prova
   - Query con overlap detection: `(ora_inizio < ora_fine_nuova) AND (ora_fine > ora_inizio_nuova)`

2. **Verifica Lezioni Ricorrenti**:
   - Calcola giorno settimana dalla data (`DateTime->format('N')`)
   - Mappa numero → nome giorno (1=lunedì, 7=domenica)
   - Controlla `lezioni` per lezioni settimanali programmate
   - JOIN con `assenze` per escludere slot con assenze registrate

3. **Gestione Intelligente Assenze**:
   - Se lezione ricorrente ha assenza per quella data → slot disponibile
   - Se lezione ricorrente attiva senza assenza → conflitto bloccante

#### Messaggi di Errore Dettagliati
```
Conflitto con evento:
"Conflitto orario: [titolo] già presente dalle HH:MM alle HH:MM"

Conflitto con lezione:
"Conflitto orario: lezione di [Cognome Nome] ([Materia]) già presente dalle HH:MM alle HH:MM"
```

## 📊 Logica Implementata

```php
// 1. Calcola giorno settimana
$giornoNumero = (new DateTime($data))->format('N');
$giornoSettimana = ['lunedi', 'martedi', ...];

// 2. Verifica eventi_calendario
SELECT * FROM eventi_calendario 
WHERE data_evento = ? AND aula_id = ? 
AND (overlap condition)

// 3. Verifica lezioni ricorrenti
SELECT l.*, ass.id as ha_assenza
FROM lezioni l
LEFT JOIN assenze ass ON ass.lezione_id = l.id AND ass.data_assenza = ?
WHERE giorno_settimana = ? AND aula_id = ?
AND (overlap condition)

// 4. Blocca solo se lezione attiva SENZA assenza
IF (conflitto_lezione AND ha_assenza == 0) → ERRORE
```

## 🔄 Compatibilità

- ✅ Retrocompatibile al 100%
- ✅ Nessuna modifica schema database
- ✅ Nessun impatto su funzionalità esistenti

## 📝 Note di Upgrade

### Installazione
1. Sostituire `api_lezioni_prova.php`
2. Nessuna migrazione database richiesta
3. Testare creazione lezioni di prova

### Test Post-Upgrade
- ✅ Lezione prova su slot occupato → blocco con errore
- ✅ Lezione prova su slot con assenza → creazione permessa
- ✅ Lezione prova su slot libero → creazione permessa
- ✅ Eventi non scompaiono dal calendario

## 🎯 Impatto Utenti

### Utenti Finali
- ✅ Prevenzione sovrapposizioni
- ✅ Messaggi errore chiari
- ✅ Calendario sempre consistente

### Amministratori
- ✅ Integrità dati garantita
- ✅ Nessuna manutenzione richiesta

## 📖 File Modificati

- `api_lezioni_prova.php` - Controllo sovrapposizione completo
- `composer.json` - Versione 2.4.0 → 2.4.1

---

**Versione precedente:** 2.4.0  
**Versione successiva:** TBD
