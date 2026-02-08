# 📝 TODO List - Da Fare Domani

## 🎨 UX/UI Improvements

### ✅ COMPLETATO OGGI (Notte 7-8 Feb 2026)
- [x] Assenze dal calendario funzionanti
- [x] Toast notifications (no autohide)
- [x] Lezioni annullate in grigio
- [x] Sistema festività italiane automatico
- [x] Fix conflitto Laravel helpers
- [x] Fix flash messages mancanti

---

## 🔧 DA FARE DOMANI

### 🎯 PRIORITÀ ALTA - Calendario

#### **1. Spostare Legenda Calendario**
**Posizione Attuale:** Sotto il calendario (in basso)  
**Posizione Nuova:** Sopra il calendario, prima o dopo i tab giorni settimana

**Opzioni:**
- **A)** Subito prima dei tab giorni (dopo navigazione settimana)
- **B)** Subito dopo i tab giorni (prima del calendario)

**File da Modificare:**
- `calendario.php` - Spostare il blocco HTML della legenda

**Codice da Spostare:**
```html
<!-- Legenda -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="card-title"><i class="bi bi-info-circle"></i> Legenda</h6>
        ...
    </div>
</div>
```

---

#### **2. Verificare e Correggere Colori Legenda**

**Problema:** I colori nella legenda potrebbero non corrispondere ai colori effettivi delle lezioni nel calendario.

**Da Verificare:**

**Legenda Attuale:**
```css
Regolare:     #e3f2fd (azzurro chiaro) + border #2196f3 (blu)
Custom:       #fff3e0 (arancione chiaro) + border #ff9800 (arancione)
Recupero:     #e8f5e9 (verde chiaro) + border #4caf50 (verde)
Laboratorio:  #f3e5f5 (viola chiaro) + border #9c27b0 (viola)
```

**CSS Calendario (style.css):**
```css
.lezione-slot.tipo-regolare {
    background-color: #fff5f0;  ← DIVERSO da legenda!
    border-left: 3px solid #ff6b35;  ← DIVERSO da legenda!
}

.lezione-slot.tipo-custom {
    background-color: #fff3e0;  ← OK
    border-left: 3px solid #ff9800;  ← OK
}

.lezione-slot.tipo-recupero {
    background-color: #e8f5e9;  ← OK
    border-left: 3px solid #4caf50;  ← OK
}

.lezione-slot.tipo-laboratorio {
    background-color: #f3e5f5;  ← OK
    border-left: 3px solid #9c27b0;  ← OK
}
```

**AZIONE:**
1. Verificare colori effettivi nel calendario
2. Aggiornare legenda con colori corretti
3. Decidere se mantenere colori CSS o legenda

---

#### **3. TODO Opzionale - Aggiungere Badge "Annullata" in Legenda**

Visto che ora le lezioni annullate appaiono grigie, potrebbe essere utile aggiungere alla legenda:

```html
<div class="col-auto">
    <span class="badge" style="background-color: #e0e0e0; color: #757575; border-left: 3px solid #9e9e9e;">
        Annullata/Festività
    </span>
</div>
```

---

## 📋 CHECKLIST IMPLEMENTAZIONE

### Step by Step:

- [ ] **STEP 1:** Aprire `calendario.php`
- [ ] **STEP 2:** Trovare blocco HTML legenda (linea ~280)
- [ ] **STEP 3:** Tagliare il blocco legenda
- [ ] **STEP 4:** Decidere posizione (prima o dopo tab giorni)
- [ ] **STEP 5:** Incollare legenda nella nuova posizione
- [ ] **STEP 6:** Verificare colori nel browser (apri calendario)
- [ ] **STEP 7:** Confrontare colori legenda vs lezioni effettive
- [ ] **STEP 8:** Se diversi, aggiornare legenda per matchare
- [ ] **STEP 9:** (Opzionale) Aggiungere badge "Annullata"
- [ ] **STEP 10:** Testare visivamente tutto il calendario
- [ ] **STEP 11:** Commit finale

---

## 🎨 COLORI DA VERIFICARE DOMANI

### Tabella Confronto:

| Tipo | Legenda Attuale | CSS Effettivo | Match? | Azione |
|------|----------------|---------------|--------|--------|
| **Regolare** | #e3f2fd + #2196f3 | #fff5f0 + #ff6b35 | ❌ NO | Aggiornare legenda |
| **Custom** | #fff3e0 + #ff9800 | #fff3e0 + #ff9800 | ✅ OK | Nessuna |
| **Recupero** | #e8f5e9 + #4caf50 | #e8f5e9 + #4caf50 | ✅ OK | Nessuna |
| **Laboratorio** | #f3e5f5 + #9c27b0 | #f3e5f5 + #9c27b0 | ✅ OK | Nessuna |

**DECISIONE DA PRENDERE:**
- Cambiare legenda per matchare CSS? (consigliato)
- O cambiare CSS per matchare legenda?

---

## 📝 NOTE IMPLEMENTAZIONE

### Posizione Suggerita Legenda:

**OPZIONE A - Prima dei tab giorni:**
```
[Navigazione Settimana: ← Precedente | Corrente | Successiva →]
[📋 LEGENDA QUI]
[Tab Giorni: Lun | Mar | Mer | Gio | Ven | Sab]
[Calendario Orario]
```

**OPZIONE B - Dopo tab giorni:** ✅ CONSIGLIATA
```
[Navigazione Settimana: ← Precedente | Corrente | Successiva →]
[Tab Giorni: Lun | Mar | Mer | Gio | Ven | Sab]
[📋 LEGENDA QUI]
[Calendario Orario]
```

**Motivazione Opzione B:**
- Più logico: prima scegli giorno, poi vedi legenda, poi vedi calendario
- Mantiene navigazione e tab insieme (coerenza visiva)
- Legenda più vicina al calendario da interpretare

---

## 🚀 STIMA TEMPO

- Spostamento legenda: **5 minuti**
- Verifica colori: **10 minuti**
- Aggiornamento colori legenda: **5 minuti**
- Test visivo: **5 minuti**
- Commit: **2 minuti**

**TOTALE:** ~30 minuti

---

## 🎯 OBIETTIVO FINALE

Calendario con:
- ✅ Legenda in posizione migliore (più visibile)
- ✅ Colori legenda 100% accurati
- ✅ UX migliorata
- ✅ Nessuna confusione colori

---

**BUON LAVORO DOMANI! 🌅☕**