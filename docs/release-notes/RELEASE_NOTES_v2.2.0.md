# Release Notes v2.2.0 - Calendario Ultra-Compatto con Rowspan

**Data Rilascio:** 14 Febbraio 2026  
**Tipo:** Minor Release (Miglioramenti UI/UX)

---

## 🎯 Obiettivo Release

Ottimizzazione drastica del layout calendario con implementazione sistema rowspan dinamico per visualizzazione precisa delle durate lezioni. Compattazione generale dell'interfaccia per massimizzare lo spazio disponibile.

---

## ✨ Nuove Funzionalità

### 1. Sistema Rowspan Dinamico per Calendario

**Implementazione:**
- Slot orari ridotti a **15 minuti** (da 45 minuti)
- Calcolo automatico rowspan in base alla durata lezione
- Tracking celle occupate per evitare sovrapposizioni
- Funzione `calcolaRowspan()` per calcolo dinamico

**Risultati:**
- Lezione 30' = rowspan 2 (2 × 15')
- Lezione 45' = rowspan 3 (3 × 15')
- Lezione 60' = rowspan 4 (4 × 15')
- Lezione 90' = rowspan 6 (6 × 15')

**Codice PHP:**
```php
function calcolaRowspan($ora_inizio, $ora_fine) {
    $start = strtotime($ora_inizio);
    $end = strtotime($ora_fine);
    $durata_minuti = ($end - $start) / 60;
    $rowspan = ceil($durata_minuti / 15);
    return max(1, $rowspan);
}
```

### 2. Annullamento Recuperi/Prenotazioni

**Fix Critico:**
- Rimosso blocco errato che impediva annullamento recuperi
- API `api_annulla_prenotazione.php` ora permette annullamento di TUTTI gli eventi da `eventi_calendario`
- Workflow: Evento → Annulla → Disattiva (`attivo = 0`) + Log

---

## 🎨 Miglioramenti UI/UX

### Layout Ultra-Compatto

**Celle Calendario:**
```css
.calendario-cell {
    height: 10px;        /* Da 60px (-83%) */
    min-height: 10px;
    padding: 0;
}

.lezione-slot {
    padding: 2px;        /* Da 0.35rem (-75%) */
    margin: 0px;         /* Da 2px (-100%) */
    font-size: 9px;      /* Da 0.7rem */
}
```

**Header e Navigazione:**
- Padding header tabella: 4px (da 8px, -50%)
- Padding aula header: 4px (da 12px, -67%)
- Card navigazione: `mb-2` (da `mb-4`, -50%)
- Card body: `py-1` (da `py-2`, -50%)
- Gap legenda: `g-1` (da `g-2`, -50%)

**Risultato Finale:**
- **83% riduzione altezza celle**
- **Molto più lezioni visibili** senza scroll
- Layout **professionale e compatto**

---

## 🔧 Modifiche Tecniche

### File Modificati

1. **config/config.php**
   - `DURATA_SLOT_DEFAULT`: 45 → 15 minuti

2. **calendario.php**
   - Sistema tracking `$celle_occupate` per gestione rowspan
   - Loop con `continue` per celle occupate
   - Compattazione layout navigazione settimana
   - Riduzione padding/margin card

3. **assets/css/style.css**
   - Riduzione dimensioni celle calendario
   - Ottimizzazione padding/margin globale
   - Font-size ridotti mantenendo leggibilità

4. **api_annulla_prenotazione.php**
   - Rimossa verifica categoria che bloccava recuperi
   - Semplificata logica annullamento

---

## 📊 Metriche Performance

| Metrica | Prima | Dopo | Miglioramento |
|---------|-------|------|---------------|
| Altezza slot | 60px | 10px | **-83%** |
| Lezioni visibili | ~8 | ~25+ | **+212%** |
| Padding complessivo | 48px | 12px | **-75%** |
| Precisione durate | ±45min | ±15min | **+300%** |

---

## 🐛 Bug Fix

1. **Recuperi non annullabili** (#CRITICAL)
   - Errore: API bloccava annullamento recuperi
   - Fix: Rimosso controllo errato su categoria
   - Impact: Tutti gli eventi ora annullabili

2. **Proporzioni lezioni errate**
   - Errore: Box 45' diventato 90px invece di 60px
   - Fix: Ridotto slot da 20px a 16px poi a 10px
   - Result: Proporzioni corrette

---

## ⚠️ Breaking Changes

**Nessuno** - Release backward compatible

---

## 📝 Note per Sviluppatori

### Sistema Rowspan
```php
// Array tracking celle occupate
$celle_occupate[$aula_id][$slot_index] = rowspan_rimanente;

// Durante rendering
if (isset($celle_occupate[$aula_id][$slot_index]) && 
    $celle_occupate[$aula_id][$slot_index] > 0) {
    $celle_occupate[$aula_id][$slot_index]--;
    continue; // Skip <td>
}
```

### Calcolo Rowspan
- Durata minuti = (ora_fine - ora_inizio) / 60
- Rowspan = ceil(durata_minuti / 15)
- Minimo rowspan = 1

---

## 🚀 Prossimi Step (v2.3.0)

1. Scroll fisso header calendario
2. Sticky navigation giorni settimana
3. Filtri avanzati calendario
4. Export PDF calendario

---

## 👥 Contributors

- Marco Piras (@developer)

---

## 📦 Deployment

```bash
git add .
git commit -m "Release v2.2.0: Calendario ultra-compatto con rowspan dinamico"
git checkout master
git merge develop
git tag -a v2.2.0 -m "Release v2.2.0"
git push origin master --tags
git checkout develop
```

---

**Versione Precedente:** v2.1.0  
**Prossima Versione:** v2.3.0