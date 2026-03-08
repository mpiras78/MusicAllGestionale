# 🗺️ ROADMAP DI IMPLEMENTAZIONE - MUSICALL v3.0

## Fasi di Sviluppo

### **FASE 1: Setup e Rinomina** (Settimana 1-2)
**Obiettivo**: Preparare la base per le nuove features

#### 1.1 - Database Setup
- [ ] Creare tabella `dati_associazione`
- [ ] Creare tabella `audit_log`
- [ ] Rinominare tabella `allievi` → `soci`
- [ ] Aggiungere colonne a `soci`: `telefono_2`, `cap`, `citta`, `updated_at`
- [ ] Aggiungere `created_at`, `updated_at` a tutte le tabelle principali
- [ ] Creare tabelle di supporto: `chiusure_attivita`, `modalita_pagamento`
- **Tempo stimato**: 3-4 ore
- **File**: `database/migrations/001_fase1_setup.sql`

#### 1.2 - Rinomina Variabili Codebase
- [ ] Rinominare tutte le occorrenze di `allievi` → `soci` in PHP
- [ ] Aggiornare label UI: "Allievo" → "Socio"
- [ ] Aggiornare label menu: "Gestione → Allievi" → "Gestione → Elenco Soci"
- [ ] Aggiornare label menu: "Gestione → Iscrizioni" → "Gestione → Corsi"
- **Tempo stimato**: 4-5 ore
- **File**: Tutti i file PHP (grep search + replace)

#### 1.3 - Controller Audit Trail
- [ ] Creare `AuditLogController.php`
- [ ] Implementare metodo `log($tipo, $tabella, $record_id, $vecchio, $nuovo)`
- [ ] Integrare nei controller esistenti
- **Tempo stimato**: 2-3 ore
- **File**: `includes/controllers/AuditLogController.php`

#### 1.4 - Pagina Dati Associazione
- [ ] Creare `admin/associazione.php` (solo admin)
- [ ] Form CRUD per dati_associazione
- [ ] Salvataggio su DB
- [ ] Audit trail integrato
- **Tempo stimato**: 2 ore
- **File**: `admin/associazione.php`

#### 1.5 - Helper Functions
- [ ] Funzioni di calcolo costi pro-rata
- [ ] Funzioni di conteggio lezioni per mese
- [ ] Funzioni di gestione festività
- **Tempo stimato**: 3 ore
- **File**: `includes/helpers/cost_calculations.php`

**Deliverable Fase 1**: 
- ✅ Database rinominato e aggiornato
- ✅ UI rinominata (Allievo → Socio)
- ✅ Pagina Dati Associazione funzionante
- ✅ Sistema Audit Trail implementato

---

### **FASE 2: Iscrizioni Annuali** (Settimana 3-4)
**Obiettivo**: Implementare sistema iscrizioni annuali con numero tessera

#### 2.1 - Tabelle Iscrizioni Annuali
- [ ] Creare tabella `iscrizioni_annuali`
- [ ] Creare logica auto-increment numero_tessera
- [ ] Creare tabella `sospensioni_corso`
- **Tempo stimato**: 2 ore

#### 2.2 - Controller Iscrizioni
- [ ] Creare `IscrizioniAnnualiController.php`
- [ ] Metodo `creaIscrizione($socio_id, ...)`
- [ ] Metodo `generaNumerTessera($anno_accademico)`
- [ ] Metodo `getIscrizioneAttiva($socio_id)`
- **Tempo stimato**: 3-4 ore

#### 2.3 - Pagina Nuova Iscrizione (Wizard)
- [ ] Modificare `gestione_iscrizioni.php` → aggiungi passo iscrizione annuale
- [ ] Step 1: Dati anagrafici + 2 telefoni + cap + città
- [ ] Step 2: Sconto familiare (con ricerca socio)
- [ ] Step 3: Riepilogo e conferma
- [ ] Step 4: Stampa domanda iscrizione
- **Tempo stimato**: 6-8 ore

#### 2.4 - Stampa Domanda Iscrizione
- [ ] Creare `stampe/domanda_iscrizione.php`
- [ ] Include dati associazione
- [ ] Include dati socio + numero tessera
- [ ] Include area firma
- **Tempo stimato**: 2-3 ore

#### 2.5 - Menu Changes
- [ ] Aggiungere voce "Nuova Iscrizione" nel menù principale
- [ ] Modificare voce "Gestione → Iscrizioni" → "Gestione → Corsi"
- **Tempo stimato**: 1 ora

**Deliverable Fase 2**:
- ✅ Iscrizioni annuali funzionanti
- ✅ Numero tessera generato automaticamente
- ✅ Wizard nuova iscrizione completo
- ✅ Stampa domanda iscrizione

---

### **FASE 3: Sconto Familiare** (Settimana 5)
**Obiettivo**: Sistema sconto familiare con tracciamento

#### 3.1 - Tabella Famiglia
- [ ] Creare tabella `famiglia`
- [ ] Logica collegamento bidirezionale
- **Tempo stimato**: 1 ora

#### 3.2 - Controller Famiglia
- [ ] Creare `FamigliaController.php`
- [ ] Metodo `collegaFamiliare($socio_id, $familiare_id)`
- [ ] Metodo `getFamiliari($socio_id)`
- [ ] Metodo `haFamiliareConCorsoAttivo($socio_id)`
- **Tempo stimato**: 2-3 ore

#### 3.3 - UI Sconto Familiare
- [ ] Nel wizard iscrizione: checkbox "Sconto Familiare"
- [ ] Se checked: mostra ricerca socio
- [ ] Collegamento automatico in tabella `famiglia`
- [ ] Iscrizione gratuita (costo_iscrizione = 0)
- **Tempo stimato**: 3-4 ore

#### 3.4 - Check Automatico Pagamenti
- [ ] In pagina pagamenti: controllo se familiare ha ancora corsi attivi
- [ ] Se no → revoca sconto dal mese successivo
- [ ] Se sì → mantieni sconto
- **Tempo stimato**: 2-3 ore

**Deliverable Fase 3**:
- ✅ Sistema sconto familiare completo
- ✅ Controllo automatico dello stato familiare
- ✅ Revoca prospettica dello sconto

---

### **FASE 4: Configurazione Costi** (Settimana 6)
**Obiettivo**: Sistema biperiodico costi iscrizione

#### 4.1 - Tabella Configurazione Costi
- [ ] Aggiungere colonne a tabella config: `costo_iscrizione_agosto_febbraio`, `costo_iscrizione_marzo_luglio`
- **Tempo stimato**: 1 ora

#### 4.2 - Controller Configurazione
- [ ] Metodo `getCostoIscrizioneMese($mese)`
- [ ] Logica: agosto-febbraio vs marzo-luglio
- **Tempo stimato**: 1-2 ore

#### 4.3 - Pagina Configurazione Costi
- [ ] Creare `admin/configurazione_costi.php`
- [ ] Form per costo Agosto-Febbraio
- [ ] Form per costo Marzo-Luglio
- [ ] Salvataggio su DB
- **Tempo stimato**: 2-3 ore

#### 4.4 - Menu Changes
- [ ] Aggiungere voce "Gestione Costi Iscrizione" in Gestione
- **Tempo stimato**: 1 ora

**Deliverable Fase 4**:
- ✅ Costi biperiodici configurabili
- ✅ Pagina gestione costi funzionante

---

### **FASE 5: Gestione Corsi (Modifiche Existing)** (Settimana 7-8)
**Obiettivo**: Ampliare gestione corsi con modifiche e sospensioni

#### 5.1 - Controller Modifiche
- [ ] Modificare `IscrizioniController` per supportare modifiche corso
- [ ] Metodo `modificaCorso($corso_id, $dati)` con pro-rata
- [ ] Logica: SE pagamento già effettuato → calcola delta
- **Tempo stimato**: 4-5 ore

#### 5.2 - Pagina Modifica Corsi
- [ ] Nuova pagina: `gestione_corsi_dettagli.php`
- [ ] Elenco corsi socio con modifica/cancellazione
- [ ] Pulsante "Aggiungi Nuovo Corso"
- [ ] Calcolo pro-rata mostrato in pagina
- **Tempo stimato**: 5-6 ore

#### 5.3 - Sospensione Corsi
- [ ] Creare pagina sospensione: `sospendi_corso.php`
- [ ] Modal per scelta: temporanea o definitiva
- [ ] Se temporanea: richiedi mesi di sospensione
- [ ] Se definitiva: elimina ricorrenze future
- [ ] Icona nel calendario per sospensione temporanea
- **Tempo stimato**: 5-6 ore

#### 5.4 - Alert Sovrapposizioni
- [ ] Creare tabella `alert`
- [ ] Logic: se creo corso in slot sospeso → crea ALERT
- [ ] Pagina visualizzazione alert (solo admin)
- [ ] Flag visibile/nascosto
- **Tempo stimato**: 4-5 ore

**Deliverable Fase 5**:
- ✅ Modifiche corso con pro-rata
- ✅ Sospensioni temporanee/definitive
- ✅ Sistema alert sovrapposizioni

---

### **FASE 6: Pagamenti** (Settimana 9-10)
**Obiettivo**: Sistema pagamenti completo con sconti

#### 6.1 - Tabelle Pagamenti
- [ ] Creare tabelle: `pagamenti`, `modalita_pagamento`, `batch_runs`
- [ ] Popolare modalita_pagamento: Contanti, Bonifico
- **Tempo stimato**: 2 ore

#### 6.2 - Controller Pagamenti
- [ ] Creare `PagamentiController.php`
- [ ] Metodo `calcolaCostatoMese($socio_id, $mese)`
- [ ] Metodo `determinaScontiApplicabili($socio_id)`
- [ ] Metodo `savaPagamento($dati)`
- [ ] Metodo `modificaPagamento($pagamento_id, $dati)`
- [ ] Metodo `eliminaPagamento($pagamento_id)` (solo admin, solo contanti)
- **Tempo stimato**: 8-10 ore

#### 6.3 - Pagina Pagamenti
- [ ] Creare `pagine/pagamenti.php`
- [ ] Mostra iscrizione annuale (se non pagata)
- [ ] Mostra corsi del mese con conteggio lezioni
- [ ] Mostra sconti applicabili (con flag da attivare)
- [ ] Calcolo totale
- [ ] Pulsante "Paga" oppure "Stampa Ricevuta"
- [ ] Nota opzionale
- [ ] Email automatica al salvataggio
- **Tempo stimato**: 8-10 ore

#### 6.4 - Sconti Applicabili
- [ ] Logica: Sconto Familiare
- [ ] Logica: Sconto Compleanno
- [ ] Logica: Sconto Mattutini (iscrizione metà + 10% mensili)
- [ ] Esclusione mutua (solo uno attivabile)
- **Tempo stimato**: 4-5 ore

#### 6.5 - Stampa Ricevuta
- [ ] Creare `stampe/ricevuta_pagamento.php`
- [ ] Include dati socio + tessera
- [ ] Include corsi con costo
- [ ] Include totale pagato
- [ ] Include firma
- [ ] Invio mail automatica
- **Tempo stimato**: 3-4 ore

**Deliverable Fase 6**:
- ✅ Sistema pagamenti completo
- ✅ Sconti funzionanti
- ✅ Stampa ricevute

---

### **FASE 7: Assenze e Recuperi (Modifiche)** (Settimana 11)
**Obiettivo**: Ampliare sistema assenze con numero garantito

#### 7.1 - Config Recuperi Garantiti
- [ ] Aggiungere campo config: `numero_recuperi_garantiti` (default 3)
- [ ] Pagina admin per modifica
- **Tempo stimato**: 1-2 ore

#### 7.2 - Controller Assenze Modificato
- [ ] Aggiungere logica: conta recuperi per socio/anno
- [ ] Se < limite → obbligatorio
- [ ] Se >= limite → opzionale
- [ ] Invio email quando 2a assenza
- **Tempo stimato**: 3-4 ore

#### 7.3 - UI Assenze
- [ ] Mostra limite recuperi nella pagina assenze
- [ ] Badge "Recupero Obbligatorio" vs "Opzionale"
- [ ] Email promemoria al raggiungimento limite
- **Tempo stimato**: 2-3 ore

**Deliverable Fase 7**:
- ✅ Limite recuperi garantiti funzionante
- ✅ Email reminder al 2° recupero

---

### **FASE 8: Batch Email Scheduler** (Settimana 12)
**Obiettivo**: Automazione email promemoria pagamento

#### 8.1 - Libreria PHP-Scheduler
- [ ] Installare `pmill/php-scheduler`
- [ ] Configurare webhook endpoint
- **Tempo stimato**: 2-3 ore

#### 8.2 - API Batch
- [ ] Creare `api/api_batch_email.php`
- [ ] Logica: ogni 26 del mese
- [ ] Genera email promemoria per TUTTI i soci con corsi attivi
- [ ] Include corsi + numero lezioni mese + costo
- [ ] Invia email
- [ ] Traccia esecuzione in `batch_runs`
- **Tempo stimato**: 5-6 ore

#### 8.3 - Pagina Gestione Batch
- [ ] Creare `admin/batch_management.php`
- [ ] Mostra storico esecuzioni batch
- [ ] Pulsante "Esegui Ora" (solo admin)
- [ ] Mostra ultimo invio fatto
- **Tempo stimato**: 2-3 ore

**Deliverable Fase 8**:
- ✅ Batch email automatico funzionante
- ✅ Pagina gestione batch

---

### **FASE 9: UI Final Touches** (Settimana 13)
**Obiettivo**: Polish e completamento UI

#### 9.1 - Colori Sala nel Calendario
- [ ] Nomi soci nel calendario con colore della sala
- [ ] CSS aggiornato
- **Tempo stimato**: 2-3 ore

#### 9.2 - Colonna Lezioni in Elenco Soci
- [ ] Mostra "Chitarra con Insegnante X, Batteria con Insegnante Y"
- **Tempo stimato**: 1-2 ore

#### 9.3 - Controlli UI Generali
- [ ] Validazioni form
- [ ] Toast notifications
- [ ] Errori gestiti
- **Tempo stimato**: 3-4 ore

**Deliverable Fase 9**:
- ✅ UI completa e polished

---

## Riepilogo Tempistiche

| Fase | Durata | Ore Stimate |
|------|--------|------------|
| Fase 1 | Settimana 1-2 | 18-22 |
| Fase 2 | Settimana 3-4 | 20-26 |
| Fase 3 | Settimana 5 | 10-14 |
| Fase 4 | Settimana 6 | 6-8 |
| Fase 5 | Settimana 7-8 | 23-27 |
| Fase 6 | Settimana 9-10 | 35-42 |
| Fase 7 | Settimana 11 | 8-12 |
| Fase 8 | Settimana 12 | 11-15 |
| Fase 9 | Settimana 13 | 7-10 |
| **TOTALE** | **13 Settimane** | **138-176 ore** |

---

## Priorità e Sequenza

✅ **Critica**: Fase 1 (Setup), Fase 2 (Iscrizioni), Fase 6 (Pagamenti)
🟡 **Alta**: Fase 3 (Sconto Familiare), Fase 5 (Corsi)
🟢 **Media**: Fase 4 (Costi), Fase 7 (Assenze), Fase 8 (Batch)
🔵 **Bassa**: Fase 9 (Polish)

---

## Dipendenze tra Fasi

```
Fase 1 (Setup)
    ↓
Fase 2 (Iscrizioni)
    ↓
Fase 3 (Sconto Familiare)
    ↓
Fase 4 (Configurazione Costi)
    ↓
Fase 5 (Gestione Corsi) ←→ Fase 6 (Pagamenti)
    ↓
Fase 7 (Assenze)
    ↓
Fase 8 (Batch Email)
    ↓
Fase 9 (Polish)
```

---

## Note Importanti

1. **Testing**: Dopo ogni fase, eseguire test completi
2. **Backup**: Prima di cada modifica DB, fare backup
3. **Version Control**: Commit frequenti dopo ogni sottosezione
4. **Documentation**: Aggiornare README e documentazione ad ogni fase
5. **User Feedback**: Coinvolgere il cliente dopo fase 2, 6, 9
