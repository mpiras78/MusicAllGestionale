# 🎵 MusicAll v1.1.0 - Release Notes

**Data Rilascio:** 08 Febbraio 2026  
**Tipo:** Minor Release - Nuove Funzionalità

---

## 🎯 Novità Principali

### 1. ✅ **Segna Assenza dal Calendario**
Ora è possibile registrare assenze direttamente dal calendario settimanale!

**Come funziona:**
1. Click su una lezione nel calendario
2. Si apre modal con info allievo
3. Click su "Segna Assenza"
4. Scegli causale (Allievo/Docente)
5. Conferma → Assenza salvata nel database

**Caratteristiche:**
- ✅ Salvataggio immediato nel database
- ✅ Scelta causale assenza (Allievo/Docente)
- ✅ Campo note opzionale
- ✅ Recupero automatico se causata da docente
- ✅ Validazione duplicati
- ✅ Feedback visivo successo/errore
- ✅ Reload automatico per aggiornare statistiche

**File Coinvolti:**
- `api_salva_assenza_calendario.php` - API REST per salvataggio
- `calendario.php` - Modal e JavaScript
- Integrazione con `AssenzeController` e `RecuperiController`

---

### 2. 🎨 **Miglioramenti Visivi Calendario**

#### **Colori Header Aule Personalizzati:**
- 🔵 **Aula MIDI:** Blu (#2196F3)
- 🟢 **Aula Piano:** Verde (#4CAF50)
- 🟠 **Aula Magna:** Arancione (#FF9800)
- 🩷 **Sala Jazz:** Ciclamino (#E91E63)
- 🟣 **Sala Pop:** Viola (#9C27B0)
- 🔴 **Sala Rock:** Rosso (#F44336)

**Benefici:**
- Identificazione immediata delle aule
- Navigazione visiva più intuitiva
- Design più vivace e professionale

#### **Tab Giorni Settimana Evidenziati:**
- ✅ Bordi visibili su tutti i tab (2px)
- ✅ Giorno selezionato con gradient arancione
- ✅ Effetto sollevato 3D con shadow
- ✅ Hover effect interattivo
- ✅ Badge "OGGI" animato con pulse

**CSS Features:**
- Border-radius arrotondati (8px)
- Transizioni smooth (0.3s)
- Transform translateY per effetto depth
- Shadow dinamiche per interattività

---

### 3. ℹ️ **Versione App e Modal About**

#### **Pagina Login:**
- ✅ Versione visibile sotto pulsante "Accedi"
- ✅ Formato: "ℹ️ Versione 1.1.0"
- ✅ Posizionata sopra credits developer

#### **Dropdown Utente:**
- ✅ Nuova voce "About" nel menu
- ✅ Separata da divider prima di Logout
- ✅ Apre modal informativa centrata

#### **Modal About:**
Contiene:
- 🎵 Logo MusicAll
- 🏷️ Badge versione app (alert-info)
- 📋 Lista caratteristiche principali:
  - Gestione Allievi e Docenti
  - Calendario Settimanale Interattivo
  - Tracciamento Assenze e Recuperi
  - Statistiche e Report
  - Sistema Multi-Utente
- 💻 Credits developer (Marco Piras & Cline)
- © Copyright dinamico con anno corrente

**Disponibilità:**
- Modal accessibile da tutte le pagine
- Include in `footer.php` per disponibilità globale

---

## 🔧 Miglioramenti Tecnici

### **API REST:**
- Nuovo endpoint `api_salva_assenza_calendario.php`
- Validazione parametri rigorosa
- Check duplicati assenze
- Creazione automatica recuperi
- Response JSON strutturate
- Error handling completo

### **JavaScript:**
- Gestione modal Bootstrap + fallback vanilla JS
- Fetch API per chiamate asincrone
- Loading states con spinner
- Alert user-friendly
- Reload automatico post-salvataggio

### **CSS:**
- Classi specifiche per aule (`.aula-midi`, `.aula-piano`, etc.)
- Stili tab giorni (`.nav-tabs .nav-link`)
- Animazioni keyframe (`pulse-badge`)
- Transform e shadow per depth

---

## 📊 Statistiche Versione

```
Files Changed: 7
- api_salva_assenza_calendario.php (NEW)
- calendario.php (MODIFIED)
- config/config.php (MODIFIED)
- assets/css/style.css (MODIFIED)
- login.php (MODIFIED)
- includes/header.php (MODIFIED)
- includes/footer.php (MODIFIED)

Total Lines Added: ~400
Total Lines Modified: ~100
```

---

## 🎯 Commits Principali

```bash
git log --oneline v1.0.0..v1.1.0

5705a89 feat: Aggiunta versione app e modal About
c40ebc7 feat: Migliorata visibilità tab giorni settimana
222da38 feat: Colori personalizzati header aule calendario
ede6600 fix: Rimozione modal assenze da calendario + fix errori
```

---

## 🐛 Bug Fix

- ✅ Risolto errore "Cannot read properties of null" su segnaAssenza()
- ✅ Corretto ID modal da 'modalCreaAssenza' a 'creaAssenzaModal'
- ✅ Rimosso include modal problematico che richiedeva variabile non definita
- ✅ Fix dropdown utente con Bootstrap fallback robusto

---

## 📦 Breaking Changes

**Nessuno!** Questa è una minor release completamente backward-compatible.

---

## 🔄 Migration Notes

**Non richiesta alcuna migrazione.** Basta fare pull del nuovo codice:

```bash
git pull origin main
# Versione aggiornata automaticamente a 1.1.0
```

---

## ✨ Prossime Feature (Roadmap v1.2.0)

- [ ] Modifica lezioni da calendario (drag & drop)
- [ ] Export calendario PDF
- [ ] Notifiche push per assenze
- [ ] Dashboard analytics avanzate
- [ ] Mobile app companion

---

## 👥 Contributors

- **Marco Piras** - Development & Design
- **Cline** - AI Assistant & Code Review

---

## 📞 Support

Per bug report o feature request:
- Email: support@musicall.it
- GitHub Issues: [repository]/issues

---

## 📄 License

© 2026 MusicAll - Tutti i diritti riservati

---

**Enjoy MusicAll v1.1.0! 🎉🎵✨**