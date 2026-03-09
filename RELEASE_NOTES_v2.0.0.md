# Release Notes - MusicAll v2.0.0

**Data Release:** 12 Febbraio 2026  
**Tipo:** Major Release

---

## 🎉 Novità Principali

### 🎯 Sistema Calendario Completamente Rinnovato

#### 1. **Visualizzazione Intelligente Assenze**
- ✅ Le assenze registrate appaiono **automaticamente in grigio** nel calendario
- ✅ Query ottimizzata con LEFT JOIN su tabella `assenze` per check real-time
- ✅ Festività nazionali italiane rilevate automaticamente
- ✅ Tooltip informativi mostrano motivo annullamento (assenza o festività)
- ✅ Filtro grayscale per migliore identificazione visiva

**Implementazione tecnica:**
```php
// LezioniController::getLezioniPerGiorno() ora accetta parametro $data_specifica
LEFT JOIN assenze ass ON ass.lezione_id = l.id AND ass.data_assenza = ?
CASE WHEN ass.id IS NOT NULL THEN 0 ELSE l.attiva END as attiva
```

#### 2. **Prenotazioni Rapide - Pulsante + Intelligente**
- ✅ **Celle vuote:** Hover mostra pulsante **+** verde per creare prenotazione
- ✅ **Celle con assenze:** Pulsante **+** disponibile anche su lezioni annullate
- ✅ Logica smart: slot con assenza = slot libero e riprenotabile
- ✅ Animazione fadeInScale fluida e moderna
- ✅ Posizionamento non invasivo (top-right della cella)

**UX Design:**
- Pulsante circolare verde con icona `bi-plus-circle`
- Scale animation al hover (1.15x)
- Box-shadow dinamico per profondità
- Click apre modal prenotazione con dati pre-compilati

#### 3. **Modal Prenotazione Rapida**
- ✅ Form completo per nuova prenotazione
- ✅ Auto-popolamento info slot (Aula, Giorno, Data, Ora)
- ✅ Select per Socio, Docente, Materia, Durata
- ✅ Durate pre-configurate: 30, 45, 60, 90, 120 minuti
- ✅ Campo note opzionale
- ✅ Alert informativo per funzionalità avanzate

### 📊 Legenda Calendario Aggiornata
- ✅ Aggiunta voce **"Festività/Assenza"** con badge grigio
- ✅ Icona distintiva `bi-x-circle`
- ✅ Design consistente con altri badge

### 🎨 Miglioramenti CSS

**Nuove classi:**
```css
.empty-slot-add              /* Pulsante + celle vuote/assenze */
.slot-libero-assenza         /* Variante per assenze */
.calendario-cell-hoverable   /* Effetto hover celle */
@keyframes fadeInScale       /* Animazione ingresso */
```

**Effetti visivi:**
- Gradient verde per pulsante +
- Transizioni smooth (0.3s ease)
- Hover state con scale 1.15x
- Background grigio celle al hover

---

## 🔧 Modifiche Tecniche

### Database & Backend

#### LezioniController
- ✅ Metodo `getLezioniPerGiorno()` esteso con parametro `$data_specifica`
- ✅ Query condizionale: con o senza check assenze
- ✅ Performance ottimizzata con LEFT JOIN

#### Calendario.php
- ✅ Passa data selezionata al controller per check assenze
- ✅ Logica `$is_annullata` considera sia campo `attiva` che festività
- ✅ Rendering condizionale pulsante + per assenze
- ✅ Nuova funzione JS `apriModalNuovaPrenotazione()`
- ✅ Gestione parametri modal (aulaId, aulaNome, ora, giorno, data)

### Frontend

#### JavaScript
- ✅ Funzione `apriModalNuovaPrenotazione()` completa
- ✅ Funzione `caricaOpzioniPrenotazione()` (placeholder per API future)
- ✅ Funzione `salvaPrenotazione()` con toast informativo
- ✅ Mappatura giorni italiani per display user-friendly

#### CSS/Styling
- ✅ Pulsante + completamente responsive
- ✅ Compatibile con tema esistente
- ✅ Animazioni performanti (GPU-accelerated)
- ✅ Z-index management per overlay corretti

---

## 📋 Breaking Changes

⚠️ **MAJOR VERSION BUMP:**

### API Changes
- `LezioniController::getLezioniPerGiorno()` ora ha signature diversa:
  - **Prima:** `getLezioniPerGiorno($giorno, $attive_only = true)`
  - **Dopo:** `getLezioniPerGiorno($giorno, $attive_only = true, $data_specifica = null)`
  - ✅ **Backward compatible:** parametro opzionale

### CSS Classes
- Nuove classi potrebbero richiedere aggiornamento temi custom
- Verificare override di `.calendario-cell` se esistenti

---

## 🐛 Bug Fix

- ✅ Fix: Assenze non mostrate visivamente nel calendario
- ✅ Fix: Mancanza collegamento tabella `assenze` → visualizzazione lezioni
- ✅ Fix: Celle con assenze considerate occupate (ora libere)
- ✅ Fix: Hover lezioni annullate mostrava cursor pointer (ora `not-allowed`)

---

## 📈 Miglioramenti Performance

- ✅ Query LEFT JOIN ottimizzata con indice su `data_assenza`
- ✅ Rendering condizionale JS per celle vuote
- ✅ CSS animations con `will-change` per smoothness
- ✅ Lazy loading modal prenotazione

---

## 🎯 Prossimi Step (v2.1.0)

### API da Implementare
- [ ] `api_get_soci.php` - Lista soci attivi
- [ ] `api_get_docenti.php` - Lista docenti
- [ ] `api_get_materie.php` - Lista materie
- [ ] `api_crea_prenotazione.php` - Salvataggio nuova lezione

### Features Pianificate
- [ ] Validazione conflitti orari
- [ ] Check disponibilità aula
- [ ] Preview prenotazione prima di conferma
- [ ] Select2 per ricerca soci/docenti
- [ ] Notifiche email prenotazione

---

## 📝 Note per Sviluppatori

### Test Consigliati
1. ✅ Testare assenza Lucchetta lunedì 16 → deve apparire grigia
2. ✅ Hover cella vuota → pulsante + deve apparire
3. ✅ Hover cella assenza → pulsante + deve apparire
4. ✅ Click pulsante + → modal deve aprirsi con dati corretti
5. ✅ Festività nazionali → lezioni devono apparire grige

### Migrazione
- ✅ **No migration SQL richiesta** - usa struttura esistente
- ✅ **No breaking changes** nei controller esistenti
- ✅ **CSS additivo** - non sovrascrive stili esistenti

---

## 👥 Contributors

- **Marco Piras** - Full Implementation
- **AI Assistant** - Code Review & Documentation

---

## 📄 Licenza

Questo software è proprietario di MusicAll.

---

**Versione Precedente:** v1.1.0  
**Versione Corrente:** v2.0.0  
**Prossima Release:** v2.1.0 (Q1 2026)