# 📅 Calendario.php - Logica di Rendering

## Panoramica

Il file `calendario.php` gestisce la visualizzazione settimanale delle lezioni e eventi in aule multiple, con gestione di:
- Lezioni ricorrenti (dalla tabella `lezioni`)
- Eventi specifici (dalla tabella `eventi_calendario`: recuperi, prenotazioni)
- Assenze (JOIN con tabella `assenze`)
- Rowspan dinamico per lezioni di durata variabile

---

## 🔄 Flusso Principale

### 1. Caricamento Dati

```php
// 1. Lezioni ricorrenti per il giorno selezionato (con JOIN assenze per data)
$lezioni = $lezioniCtrl->getLezioniPerGiorno($giorno_selezionato, true, $data_selezionata);

// 2. Eventi specifici dalla tabella eventi_calendario
$eventi = $eventiCtrl->getEventiPerData($data_selezionata);

// 3. Merge: lezioni + eventi
foreach ($lezioni as &$lez) {
    $lez['source_type'] = 'lezione';
}
$lezioni = array_merge($lezioni, $eventi);

// 4. Organizza per aula
$calendario = []; // $calendario[aula_id][] = lezione/evento
foreach ($lezioni as $lezione) {
    $calendario[$lezione['aula_id']][] = $lezione;
}
```

**Nota**: `LezioniController::getLezioniPerGiorno()` fa un LEFT JOIN con `assenze` e se trova un'assenza per quella data, setta `attiva = 0` nel risultato.

---

## 📊 Rendering Griglia

### Loop Principale

```php
foreach ($slots as $slot_index => $slot) {
    foreach ($aule as $aula) {
        // Gestione rowspan e celle occupate
        // Trova lezione/evento per questo slot
        // Renderizza celle
    }
}
```

### Gestione Rowspan

Ogni lezione che inizia in uno slot occupa N celle verticali (rowspan):

```php
$rowspan = calcolaRowspan($ora_inizio, $ora_fine);
// Marca celle successive come occupate
for ($i = 1; $i < $rowspan; $i++) {
    $celle_occupate[$aula['id']][$slot_index + $i] = $rowspan - $i;
}
```

Le celle occupate vengono saltate:

```php
if (isset($celle_occupate[$aula['id']][$slot_index]) && 
    $celle_occupate[$aula['id']][$slot_index] > 0) {
    $celle_occupate[$aula['id']][$slot_index]--;
    continue; // Salta rendering <td>
}
```

---

## 🎯 Logica di Assegnazione Slot

Per ogni slot+aula, si cerca quale lezione/evento mostrare:

### Step 1: Filtra Elementi Rilevanti

```php
$slot_start = strtotime($slot['inizio']);  // Es: 18:15:00
$slot_end = strtotime($slot['fine']);      // Es: 18:30:00

$elementi_rilevanti = array_filter($calendario[$aula['id']], function($lez) use ($slot_start, $slot_end) {
    $lez_start = strtotime($lez['ora_inizio']);
    $lez_end = strtotime($lez['ora_fine']);
    // Considera solo se c'è sovrapposizione temporale
    return ($lez_start < $slot_end && $lez_end > $slot_start);
});
```

**Esempio**:
- Slot: 18:15-18:30
- Lezione A: 18:15-19:15 → **INCLUSA** (inizia prima della fine slot)
- Evento B: 18:30-19:00 → **INCLUSO** (finisce dopo inizio slot)

### Step 2: Trova Lezione che INIZIA nello Slot

```php
foreach ($elementi_rilevanti as $lez) {
    $lezione_start = strtotime($lez['ora_inizio']);
    $inizia_in_slot = ($lezione_start >= $slot_start && $lezione_start < $slot_end);
    
    if (!isset($lez['source_type']) || $lez['source_type'] != 'evento') {
        if ($inizia_in_slot) {
            $lezione_slot = $lez;
            break;
        }
    }
}
```

### Step 3: Cerca Evento Sovrapposto DENTRO la Lezione

**IMPORTANTE**: Questa è la logica per il **Caso 3** (evento sopra lezione annullata).

```php
if ($lezione_slot) {
    $lezione_start = strtotime($lezione_slot['ora_inizio']);
    $lezione_end = strtotime($lezione_slot['ora_fine']);
    
    foreach ($elementi_rilevanti as $lez) {
        if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
            $evento_start = strtotime($lez['ora_inizio']);
            $evento_end = strtotime($lez['ora_fine']);
            
            // Evento sovrapposto se cade dentro la lezione
            if ($evento_start >= $lezione_start && $evento_start < $lezione_end) {
                $evento_slot = $lez;
                break;
            }
        }
    }
}
```

### Step 4: Se Nessuna Lezione, Cerca Evento che INIZIA nello Slot

```php
else {
    foreach ($elementi_rilevanti as $lez) {
        if (isset($lez['source_type']) && $lez['source_type'] == 'evento') {
            $evento_start = strtotime($lez['ora_inizio']);
            if ($evento_start >= $slot_start && $evento_start < $slot_end) {
                $evento_slot = $lez;
                break;
            }
        }
    }
}
```

---

## 🎨 Scenari di Rendering

### Caso 1: Solo Lezione (Attiva o Annullata)

```php
if ($lezione_slot && !$evento_slot)
```

**Rendering**:
- Card lezione con info socio/docente/materia
- Se `attiva = 0` o festività: classe `.lezione-annullata` (grigio) + pulsante `+` per prenotare

**Determinazione Annullamento**:
```php
$is_annullata = (isset($lezione_slot['attiva']) && $lezione_slot['attiva'] == 0) || $giorno_festivita;
```

### Caso 2: Solo Evento (Recupero o Prenotazione)

```php
elseif ($evento_slot && !$lezione_slot)
```

**Rendering**:
- Card evento con stile diverso in base a `tipo`:
  - `LEZ_RECUPERO` → sfondo verde
  - `PREN_SALA_SOCI` → sfondo giallo
  - `PREN_DOCENTE` → sfondo blu
  - `PREN_ESTERNO` → sfondo viola
- Onclick: `mostraInfoEvento(evento_id)` per dettagli + annullamento

### Caso 3: Evento SOPRA Lezione Annullata

```php
elseif ($lezione_slot && $evento_slot)
```

**Rendering**:
- **Piccola icona X** in alto a sinistra per lezione annullata
- **Evento occupa TUTTO lo spazio** (card piena)
- Evento è cliccabile: `onclick="mostraInfoEvento(...)"`

**CRITICO**: Evento NON ha classe `.slot-sovrapposto` (rimossa per renderlo cliccabile)

### Caso 4: Cella Vuota

```php
else
```

**Rendering**:
- Div vuoto con `+` al hover: `onclick="apriModalNuovaPrenotazione(...)"`

---

## 🐛 Bug Noto: Caso 3 Non Funziona

**Problema**: Nello slot 18:15, il filtro `elementi_rilevanti` NON include l'evento 60 (18:30-19:00).

**Causa**: L'evento 18:30-19:00 dovrebbe essere incluso perché:
```php
// Slot 18:15-18:30
$slot_start = 18:15:00  // 65700
$slot_end = 18:30:00    // 66600

// Evento 18:30-19:00
$evento_start = 18:30:00  // 66600
$evento_end = 19:00:00    // 68400

// Condizione: ($lez_start < $slot_end && $lez_end > $slot_start)
// (66600 < 66600 && 68400 > 65700) = (FALSE && TRUE) = FALSE ❌
```

**LA CONDIZIONE FALLISCE** perché `66600 < 66600` è FALSE!

**Fix**: Cambiare `<` in `<=`:
```php
return ($lez_start <= $slot_end && $lez_end > $slot_start);
```

---

## ✅ Checklist Debug Caso 3

1. ☑ Verificare che esista assenza per lezione nel database
2. ☑ Verificare che `LezioniController` ritorni `attiva = 0`
3. ☑ Verificare che evento esista in `eventi_calendario`
4. ☑ Verificare che `EventiController` ritorni l'evento
5. ☑ Verificare che evento sia nell'array `$calendario[aula_id]`
6. ☐ **FIXARE**: Condizione filtro `elementi_rilevanti` (usa `<=` invece di `<`)
7. ☐ Verificare che log mostri "✓ CASO 3"
8. ☐ Verificare rendering nel browser

---

## 📝 File Correlati

- **calendario.php**: Rendering principale
- **includes/controllers/LezioniController.php**: Caricamento lezioni + JOIN assenze
- **includes/controllers/EventiController.php**: Caricamento eventi
- **assets/css/style.css**: Stili per `.lezione-annullata`, `.tipo-recupero`, ecc.
- **assets/js/app.js**: `mostraInfoEvento()`, `apriModalNuovaPrenotazione()`

---

## 🔧 Modifiche Recenti

### 2026-02-15: Tentativo Fix Caso 3
- Rimosso classe `.slot-sovrapposto` per rendere evento cliccabile
- Modificata logica per cercare eventi sovrapposti in TUTTA la durata della lezione
- **PROBLEMA**: Filtro `elementi_rilevanti` esclude evento 18:30 dallo slot 18:15
- **PROSSIMO FIX**: Cambiare `<` in `<=` nella condizione del filtro