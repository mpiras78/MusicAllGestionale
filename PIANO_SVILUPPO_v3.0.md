# 📋 PIANO DI SVILUPPO MUSICALL v3.0
## Strategia Consolidata: Stato Progetto + Roadmap Implementazione

---

## 📊 OVERVIEW GENERALE

**Data Creazione**: 15 Novembre 2026
**Versione**: 3.0 Enterprise-Grade
**Repository**: C:\git\ct-poc\MusicAll

### Obiettivi Globali
Trasformare MusicAll da sistema v2.x (gestione base calendario + soci) a v3.0 con:
- ✅ Sistema pagamenti completo con sconto familiare
- ✅ Gestione iscrizioni annuali con numero tessera
- ✅ Pro-rata automatico per modifiche corso
- ✅ Audit trail completo
- ✅ Automazione email batch
- 📌 **MISSING**: Funzionalità cambio password utente (vedi sezione Feature Non Pianificate)

**Tempo Totale Stima**: 138-176 ore (13 settimane half-time)
**Linee di Codice Nuovo**: ~3000-4000 PHP
**Nuove Tabelle**: 12
**Pagine Nuove**: ~15
**API Nuove**: ~8-10
**Controller Nuovi**: ~5-6

---

## 🏗️ ARCHITETTURA DATABASE v3.0

### Schema Completo (12 Tabelle)

| ID | Tabella | Scopo | Stato | Migrazione |
|----|---------|-------|-------|-----------|
| 1 | `dati_associazione` | Info scuola per ricevute/email | Design ✅ | CREATE TABLE |
| 2 | `soci` | Anagrafica soci + numeri telefono | Design ✅ | ALTER TABLE (ADD telefono_2, cap, citta) |
| 3 | `iscrizioni_annuali` | Membership annuale con numero tessera | Design ✅ | CREATE TABLE |
| 4 | `corsi_soci` | Corsi per socio (MODIFICATO) | Design ✅ | ALTER TABLE |
| 5 | `pagamenti` | Transazioni pagamento | Design ✅ | CREATE TABLE |
| 6 | `famiglia` | Relazioni familiari per sconto | Design ✅ | CREATE TABLE |
| 7 | `modalita_pagamento` | CONTANTI/BONIFICO/CARTA | Design ✅ | CREATE TABLE |
| 8 | `chiusure_attivita` | Festività e giorni chiusi | Design ✅ | CREATE TABLE |
| 9 | `alert` | Conflitti lezioni (permanenti) | Design ✅ | CREATE TABLE |
| 10 | `batch_runs` | Log esecuzione batch email | Design ✅ | CREATE TABLE |
| 11 | `audit_log` | Traccia modifiche | Design ✅ | CREATE TABLE |
| 12 | `sospensioni_corso` | Sospensioni temporanee/definitive | Design ✅ | CREATE TABLE |

**Documentazione Database**: Vedi `SCHEMA_ER_DATABASE.md` (~1800 linee)

---

## 🛣️ FASI DI IMPLEMENTAZIONE (Sequenziale)

### **FASE 1: Setup e Rinomina** 
**Settimana**: 1-2  
**Ore**: 18-22  
**Priorità**: 🔴 CRITICA

#### Task Principali
- Database setup: nuove tabelle + colonne
- Rinomina variabili codebase (soci → soci in tutta l'app)
- Implementazione AuditLogController
- Pagina admin dati associazione
- Helper functions calcolo costi

#### Deliverable
- ✅ Database rinominato e aggiornato
- ✅ UI rinominata
- ✅ Pagina Dati Associazione funzionante
- ✅ Sistema Audit Trail implementato

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 1)

---

### **FASE 2: Iscrizioni Annuali**
**Settimana**: 3-4  
**Ore**: 20-26  
**Priorità**: 🔴 CRITICA

#### Task Principali
- Tabelle iscrizioni_annuali
- IscrizioniAnnualiController
- Wizard 4-step nuovo socio
- Numero tessera auto-generato (YYYYN)
- Stampa domanda iscrizione

#### Deliverable
- ✅ Iscrizioni annuali funzionanti
- ✅ Numero tessera automatico
- ✅ Wizard completo
- ✅ Stampa domanda iscrizione

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 2)

---

### **FASE 3: Sconto Familiare**
**Settimana**: 5  
**Ore**: 10-14  
**Priorità**: 🟡 ALTA

#### Task Principali
- Tabella famiglia + logica collegamento bidirezionale
- FamigliaController
- UI sconto familiare nel wizard iscrizione
- Check automatico revoca sconto (quando familiare termina corsi)

#### Deliverable
- ✅ Sistema sconto familiare completo
- ✅ Controllo automatico dello stato
- ✅ Revoca prospettica dello sconto

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 3)

---

### **FASE 4: Configurazione Costi**
**Settimana**: 6  
**Ore**: 6-8  
**Priorità**: 🟢 MEDIA

#### Task Principali
- Costi biperiodici: Agosto-Febbraio vs Marzo-Luglio
- Pagina admin gestione costi
- Helper function `getCostoIscrizioneMese($mese)`

#### Deliverable
- ✅ Costi biperiodici configurabili
- ✅ Pagina gestione costi funzionante

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 4)

---

### **FASE 5: Gestione Corsi (Modifiche Existing)**
**Settimana**: 7-8  
**Ore**: 23-27  
**Priorità**: 🔴 CRITICA

#### Task Principali
- Modifiche corso con pro-rata automatico
- Sospensioni temporanee/definitive
- Alert sovrapposizioni lezioni
- Pagina `gestione_corsi_dettagli.php`

#### Deliverable
- ✅ Modifiche corso con pro-rata
- ✅ Sospensioni temporanee/definitive
- ✅ Sistema alert sovrapposizioni

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 5)

---

### **FASE 6: Pagamenti**
**Settimana**: 9-10  
**Ore**: 35-42  
**Priorità**: 🔴 CRITICA

#### Task Principali
- PagamentiController con calcoli pro-rata e sconti
- Pagina pagamenti con calcolo totale
- Stampa ricevute + email automatica
- 3 sconti: Familiare (10%), Compleanno (5%), Mattutini (50% iscrizione + 10% corsi)

#### Logica Sconti
- **Sconto Familiare**: 10% su corsi + iscrizione gratis (se familiare ha corso attivo)
- **Sconto Compleanno**: 5% su corsi (solo mese nascita)
- **Sconto Mattutini**: 50% iscrizione + 10% corsi (inizio lezione < 11:00)
- **Esclusione Mutua**: Solo uno attivabile per volta

#### Deliverable
- ✅ Sistema pagamenti completo
- ✅ Sconti funzionanti
- ✅ Stampa ricevute

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 6), `FLUSSI_PAGAMENTO.md`

---

### **FASE 7: Assenze e Recuperi (Modifiche)**
**Settimana**: 11  
**Ore**: 8-12  
**Priorità**: 🟢 MEDIA

#### Task Principali
- Limite recuperi garantiti (default 3)
- Email reminder al 2° recupero
- Badge "Recupero Obbligatorio" vs "Opzionale"

#### Deliverable
- ✅ Limite recuperi garantiti funzionante
- ✅ Email reminder al 2° recupero

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 7)

---

### **FASE 8: Batch Email Scheduler**
**Settimana**: 12  
**Ore**: 11-15  
**Priorità**: 🟢 MEDIA

#### Task Principali
- Libreria php-scheduler (pmill/php-scheduler)
- API batch email ogni 26 del mese
- Promemoria pagamento automatico per tutti soci con corsi attivi
- Pagina admin batch management

#### Logica Batch
- Trigger: 26 di ogni mese
- Per ogni socio: genera email promemoria
- Include: corsi del mese + numero lezioni + costo
- Traccia esecuzione in `batch_runs`

#### Deliverable
- ✅ Batch email automatico funzionante
- ✅ Pagina gestione batch

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 8)

---

### **FASE 9: UI Final Touches**
**Settimana**: 13  
**Ore**: 7-10  
**Priorità**: 🔵 BASSA

#### Task Principali
- Colori sala nel calendario
- Colonna lezioni in elenco soci
- Validazioni UI generali
- Toast notifications + gestione errori

#### Deliverable
- ✅ UI completa e polished

**File Dettagli**: `ROADMAP_IMPLEMENTAZIONE_bck.md` (sezione Fase 9)

---

## 💰 FLUSSI PAGAMENTO DOCUMENTATI

### 1. Iscrizione Annuale
- Una tantum per anno accademico (settembre-luglio)
- Costo biperiodico: €150 (agosto-feb) o €100 (marzo-luglio)
- Numero tessera auto-generato: YYYYN
- Prerequisito per iscriversi a corsi

### 2. Sconto Familiare
- 10% applicato se familiare ha corso attivo
- Revoca prospettica quando familiare termina corsi
- Esclusivo con altri sconti

### 3. Corsi Mensili
- Conteggio lezioni mese
- Costo = lezioni × €/lezione
- Pro-rata automatico se modifica mid-month

### 4. Pro-Rata (Modifica Mid-Month)
- Calcola lezioni rimanenti vecchio corso
- Calcola lezioni nuove nuovo corso
- Delta = differenza pagamento
- Se delta positivo → addebito, negativo → accredito

### 5. Sconti Applicabili
- Sconto Familiare: 10% su corsi
- Sconto Compleanno: 5% su corsi (solo mese nascita)
- Sconto Mattutini: 50% iscrizione + 10% corsi
- Esclusione mutua

### 6. Batch Email (26 del mese)
- Promemoria pagamento
- Include corsi + costi + sconti
- Email ricevuta pagamento
- Traccia in batch_runs

**Documentazione Completa**: Vedi `FLUSSI_PAGAMENTO.md` (~1200 linee)

---

## 📱 WIREFRAME PAGINE CRITICHE

8 pagine mockup create per UI/UX:
1. Nuova Iscrizione (Wizard 4-step)
2. Gestione Corsi (Dettagli socio)
3. Pagamenti (Calcolo totale + sconti)
4. Stampa Ricevuta
5. Gestione Costi (Admin)
6. Batch Management (Admin)
7. Dati Associazione (Admin)
8. Alert Sovrapposizioni (Admin)

**Documentazione Completa**: Vedi `WIREFRAME_PAGINE_CRITICHE.md` (~1000 linee)

---

## 🔐 SECURITY CHECKLIST

- ✅ CSRF token in tutte le form (GIÀ IMPLEMENTATO v2.x)
- [ ] SQL Injection prevention (PDO prepared statements) - VERIFICARE
- [ ] Input validation su tutti i form
- [ ] Rate limiting su API pagamenti
- [ ] Hash password admin (vedi sezione Feature Non Pianificate)
- [ ] Session timeout (vedi sezione Feature Non Pianificate)
- [ ] HTTPS in produzione
- [ ] Audit log obbligatorio su CREATE/UPDATE/DELETE
- [ ] Backup automatico database

---

## 🧪 TESTING STRATEGY

### Unit Test Necessari
- [ ] Calcolo pro-rata (vari scenari)
- [ ] Logica sconto familiare (attivo/revocato)
- [ ] Conteggio lezioni mese
- [ ] Selezione sconto esclusivo
- [ ] Numero tessera generation

### Integration Test
- [ ] Flusso completo iscrizione → pagamento
- [ ] Modifica corso con salvaggi multi-tabella
- [ ] Email batch (mock SMTP)

### UAT (User Acceptance Test)
- [ ] Validazione con cliente
- [ ] Test scenari reali (50+ soci)
- [ ] Stampe ricevute
- [ ] Email delivery

---

## 📈 METRICHE STIMATE v3.0

| Metrica | Valore |
|---------|--------|
| **Nuove Linee Codice** | ~3000-4000 PHP |
| **Nuove Tabelle Database** | 12 |
| **Pagine Nuove** | ~15 |
| **API Nuove** | ~8-10 |
| **Controller Nuovi** | ~5-6 |
| **Email Templates** | 3 (ricevuta, promemoria, alert) |
| **Tempo Totale Dev** | 138-176 ore |
| **Settimane Stimate** | 13 (half-time) |

---

## ⏰ TIMELINE DI IMPLEMENTAZIONE

```
FASE 1: Setup            [Sett 1-2]   18-22h   ████
FASE 2: Iscrizioni       [Sett 3-4]   20-26h   █████
FASE 3: Sconto Fam       [Sett 5]     10-14h   ██
FASE 4: Costi Config     [Sett 6]     6-8h     █
FASE 5: Gestione Corsi   [Sett 7-8]   23-27h   █████
FASE 6: Pagamenti        [Sett 9-10]  35-42h   ████████
FASE 7: Assenze          [Sett 11]    8-12h    ██
FASE 8: Batch Email      [Sett 12]    11-15h   ██
FASE 9: Polish UI        [Sett 13]    7-10h    █

TOTALE: 138-176 ore su 13 settimane
```

---

## 📌 FEATURE NON PIANIFICATE (TODO)

### 1. **Funzionalità Cambio Password Utente** ✅ IMPLEMENTATA
**Status**: ✅ DONE (Opzione A: Inclusa in Fase 9)  
**Locazione**: `/profile.php` (13,550 caratteri)  
**Impatto**: Media (feature standard)  
**Tempo Impiegato**: 1.5 ore

#### Implementazione Completata
- ✅ File `/profile.php` creato
- ✅ Visualizzazione profilo utente (username, email, ruolo, ultimo login)
- ✅ Form cambio password con validazione completa
- ✅ Verifica password attuale prima di cambio
- ✅ Integrazione con `Auth::changePassword()` esistente
- ✅ Validazione complessità password (sec-005: minuscole, maiuscole, numeri, simboli)
- ✅ CSRF token protection
- ✅ Messaggi feedback (success/error)
- ✅ Activity log integrato (sec-009: password_change event)
- ✅ Consigli di sicurezza visualizzati
- ✅ Responsive design Bootstrap 5

#### Requisiti Implementati
- ✅ Password minima 12 caratteri (PASSWORD_MIN_LENGTH)
- ✅ Richiede maiuscole (PASSWORD_REQUIRE_UPPERCASE)
- ✅ Richiede minuscole (PASSWORD_REQUIRE_LOWERCASE)
- ✅ Richiede numeri (PASSWORD_REQUIRE_NUMBERS)
- ✅ Richiede simboli speciali (PASSWORD_REQUIRE_SYMBOLS)
- ✅ Conferma password con controllo coincidenza
- ✅ CSRF token validation
- ✅ Input sanitization via InputValidator

#### Note di Sicurezza
- Integrato con le mejoraciones di sicurezza Fase 1-2 (sec-001 a sec-009)
- Password MIN_LENGTH aumentata da 6 a 12 (sec-005)
- Validazione complessità integrata in InputValidator::password()
- Activity log registra ogni cambio password (audit trail - sec-009)
- HTTPOnly session cookies (sec-002)

#### User Experience
- Alert dismissible per feedback
- Badge per visualizzazione ruolo (admin/docente/segreteria)
- Tooltip con requisiti password
- Confirma dialog prima di submit
- Link "Annulla" per tornare a index
- Consigli di sicurezza in alert info

#### File Correlati
- `includes/Auth.php` - Metodo `changePassword()` riga 177-199
- `includes/InputValidator.php` - Metodo `password()` per validazione
- `includes/bootstrap.php` - Security headers già configurati
- `config/config.php` - PASSWORD_MIN_LENGTH, PASSWORD_REQUIRE_* costanti

---

### 2. **Hash Password Admin** 📌
**Status**: ❌ NOT IN ROADMAP  
**Security**: 🔴 CRITICAL  
**Impatto**: Media (sicurezza)  
**Estimated**: 1-2 ore

#### Cosa Fare
- [ ] Verificare se password admin sono hashate
- [ ] Se plain-text → migrare a password_hash()
- [ ] Aggiornare login controller

#### Raccomandazione
**PRIORITARIO PRIMA DI v3.0 RELEASE** (security baseline)

---

### 3. **Session Timeout** 📌
**Status**: ❌ NOT IN ROADMAP  
**Security**: 🟠 HIGH  
**Impatto**: Media (sicurezza)  
**Estimated**: 2-3 ore

#### Cosa Fare
- [ ] Implementare session timeout (suggested: 30-60 min)
- [ ] Redirect automatico a login se scaduta
- [ ] Message "Session scaduta" all'utente
- [ ] HTTPOnly cookie flags

#### Raccomandazione
**INCLUDI IN FASE 1 (Setup)** oppure **setup pre-v3.0**

---

### 4. **Email Service Configuration** 📌
**Status**: ⚠️ BLOCCER  
**Impatto**: CRITICO (necessario per Fase 6-8)  
**Dipendenza**: SMTP / SendGrid API

#### Cosa Fare
- [ ] Configurare SMTP o SendGrid
- [ ] Testare invio email
- [ ] Email templates (ricevuta, promemoria, batch)

#### Raccomandazione
**COMPLETARE PRIMA DI FASE 6**

---

### 5. **php-scheduler Installation** 📌
**Status**: ⚠️ BLOCCER  
**Impatto**: CRITICO (necessario per Fase 8)  
**Dipendenza**: `pmill/php-scheduler`

#### Cosa Fare
- [ ] `composer require pmill/php-scheduler`
- [ ] Configurare webhook endpoint
- [ ] Testing batch scheduler

#### Raccomandazione
**INSTALLARE PRIMA DI FASE 8**

---

## 📚 DOCUMENTAZIONE CREATA

| File | Scopo | Linee | Stato |
|------|-------|-------|-------|
| `SCHEMA_ER_DATABASE.md` | Schema 12 tabelle + migration | ~1800 | ✅ |
| `ROADMAP_IMPLEMENTAZIONE.md` | Fasi + timeline + task breakdown | ~400 | ✅ (→ _bck.md) |
| `FLUSSI_PAGAMENTO.md` | Business logic 8 flussi | ~1200 | ✅ |
| `WIREFRAME_PAGINE_CRITICHE.md` | UI mockup 8 pagine | ~1000 | ✅ |
| `STATO_PROGETTO_v3.0.md` | Overview + architettura | ~930 | ✅ (→ _bck.md) |
| `ModificheSistema.md` | Business requirements (reference) | ~920 | ✅ |

**Documentazione Totale**: ~6,250 linee di specifica tecnica

---

## ✅ CHECKLIST PRE-IMPLEMENTAZIONE

### Preparazione
- ✅ Documentazione tecnica completa
- ✅ Database schema definito
- ✅ Flussi pagamento mappati
- ✅ UI mockup creati
- ✅ Roadmap con priorità definita
- [ ] Ambiente dev configurato
- [ ] Backup database v2.x creato
- [ ] Testing framework setup (PHPUnit)
- [ ] Email service (SMTP/SendGrid) configurato
- [ ] Git branch per v3.0 creato

### Tecnologie Necessarie
- ✅ PHP 7.4+
- ✅ Bootstrap 5
- ✅ PDO / Eloquent ORM (già in uso)
- ✅ JavaScript ES6
- [ ] php-scheduler (pmill/php-scheduler - da installare)
- [ ] PHPUnit (da installare)

---

## 🔗 DIPENDENZE TRA FASI

```
Fase 1 (Setup)
    ↓
Fase 2 (Iscrizioni Annuali)
    ↓
Fase 3 (Sconto Familiare)
    ↓
Fase 4 (Configurazione Costi)
    ↓
Fase 5 (Gestione Corsi) ←→ Fase 6 (Pagamenti)
    ↓
Fase 7 (Assenze)
    ↓
Fase 8 (Batch Email) [RICHIEDE: Email Service OK]
    ↓
Fase 9 (Polish UI)
```

**Note**: 
- Fase 5 e 6 possono procedere in parallelo dopo Fase 4
- Fase 8 BLOCCANTE: richiede Email Service configurato

---

## 🚨 BLOCKERS / DIPENDENZE ESTERNE

1. **Email Service**: User deve fornire credenziali SMTP o SendGrid
2. **php-scheduler**: Richiede webhook endpoint pubblico o cron job
3. **Accesso Cliente**: Per validazione UAT su scenari reali

---

## 📝 NOTE IMPORTANTI

1. **Testing**: Dopo ogni fase, eseguire test completi
2. **Backup**: Prima di cada modifica DB, fare backup
3. **Version Control**: Commit frequenti dopo ogni sottosezione
4. **Documentation**: Aggiornare README e documentazione ad ogni fase
5. **User Feedback**: Coinvolgere il cliente dopo fase 2, 6, 9
6. **Security**: Verificare checklist sicurezza prima di release

---

## 🎯 PROSSIMI STEP

1. **Conferma Cliente**: Revisione roadmap + wireframe
2. **Setup Ambiente**: Git branch + testing framework
3. **Installare Dipendenze**: `composer require pmill/php-scheduler`, PHPUnit
4. **Configurare Email**: SMTP o SendGrid
5. **Backup Database**: Backup completo v2.x prima di Fase 1
6. **Inizio Fase 1**: Database migrations + rinomina soci → soci

**Data Target Inizio Implementazione**: 20 Novembre 2026

---

## 📞 RIFERIMENTI RAPIDI

| Riferimento | File | Sezione |
|-------------|------|---------|
| Schema Database | `SCHEMA_ER_DATABASE.md` | Intero file |
| Task Dettagli Fase 1 | `ROADMAP_IMPLEMENTAZIONE_bck.md` | Fase 1 |
| Task Dettagli Fase 2-9 | `ROADMAP_IMPLEMENTAZIONE_bck.md` | Fase 2-9 |
| Logica Pagamenti | `FLUSSI_PAGAMENTO.md` | Flussi 1-6 |
| UI/UX | `WIREFRAME_PAGINE_CRITICHE.md` | Pagine 1-8 |
| Requisiti Business | `ModificheSistema.md` | Capitoli 1-10 |
| Stato Storico | `STATO_PROGETTO_v3.0_bck.md` | Changelog |

---

## 📊 SUMMARY

**Progetto MusicAll v3.0** è una **trasformazione enterprise** da sistema base (v2.x) a piattaforma di gestione scolastica full-featured con:
- ✅ 12 nuove tabelle database
- ✅ 9 fasi sequenziali di implementazione
- ✅ 138-176 ore di sviluppo
- ✅ ~3000-4000 linee di codice PHP nuovo
- ✅ Completa documentazione tecnica
- 📌 **TODO**: Feature non pianificate (cambio password, session timeout, ecc.)

Questo documento consolida **STATO_PROGETTO_v3.0.md** e **ROADMAP_IMPLEMENTAZIONE.md** in un'unica vista strategica e tattica del progetto.

**File originali rinominati**: `*_bck.md` per archivio/riferimento

---

**Documento Creato**: 26 Luglio 2026, 18:47  
**Fonte Consolidata**: 
- STATO_PROGETTO_v3.0_bck.md (15 Novembre 2026)
- ROADMAP_IMPLEMENTAZIONE_bck.md (15 Novembre 2026)
