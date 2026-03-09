# 📊 STATO PROGETTO - MUSICALL v3.0

## Data: 15 Novembre 2026
**Aggiornamento**: Documentazione Tecnica Completa + Roadmap Implementazione

---

## 🎯 Obiettivo Globale

Trasformare MusicAll da sistema v2.x (gestione base calendario + soci) a sistema v3.0 enterprise-grade con:
- Sistema pagamenti completo con sconto familiare
- Gestione iscrizioni annuali con numero tessera
- Pro-rata automatico per modifiche corso
- Audit trail completo
- Automazione email batch

---

## 📚 Documentazione Creata ✅

### Fase 0: Analisi Requisiti (COMPLETATA)
- ✅ **ModificheSistema.md** - Documento business (aggiornato 4 volte, 100% verificato)
- ✅ **SCHEMA_ER_DATABASE.md** - Schema database (12 tabelle, migration strategy)
- ✅ **ROADMAP_IMPLEMENTAZIONE.md** (NUOVO)
  - 9 fasi sequenziali
  - 138-176 ore stimate
  - Dipendenze tra fasi mappate
  - Priorità e sequenza definite
- ✅ **FLUSSI_PAGAMENTO.md** (NUOVO)
  - 8 flussi completi (iscrizione, sconto, corsi, pagamenti, sconti, batch, etc)
  - Diagrammi ASCIi
  - Calcoli pro-rata dettagliati
  - SQL di supporto
- ✅ **WIREFRAME_PAGINE_CRITICHE.md** (NUOVO)
  - 8 pagine mockup
  - Layout responsive
  - Interattività descritta
  - Note di design

**Totale Documentazione**: ~12,000 linee di contenuto tecnico

---

## 🔧 Bugfix Completati ✅

### Bugfix 1: CSRF Token Non Passato
- **Errore**: "Token CSRF non valido quando registro nuova assenza"
- **File**: `calendario.php`, `includes/header.php`
- **Fix**: Aggiunto token retrieval e passing in confermaAssenza()
- **Commit**: 69da7c9
- **Status**: ✅ RISOLTO

### Bugfix 2: Database Method Error
- **Errore**: "Fatal error: Call to undefined method Database::lastInsertId()"
- **File**: `includes/controllers/RecuperiController.php`
- **Fix**: Cambiato execute() → insert(), rimosso lastInsertId()
- **Commit**: f5c9cfa
- **Status**: ✅ RISOLTO

---

## 📊 Architettura Database v3.0

### Nuove Tabelle (12 totali)

| Tabella | Scopo | Stato |
|---------|-------|-------|
| `dati_associazione` | Info scuola per ricevute/email | Design: ✅ |
| `soci` | Rename di `soci` | Design: ✅ |
| `iscrizioni_annuali` | Membership annuale con numero tessera | Design: ✅ |
| `corsi_soci` | Corsi per socio (modificato) | Design: ✅ |
| `pagamenti` | Transazioni pagamento | Design: ✅ |
| `famiglia` | Relazioni familiari per sconto | Design: ✅ |
| `modalita_pagamento` | CONTANTI/BONIFICO/CARTA | Design: ✅ |
| `chiusure_attivita` | Festività e giorni chiusi | Design: ✅ |
| `alert` | Conflitti lezioni (permanenti) | Design: ✅ |
| `batch_runs` | Log esecuzione batch email | Design: ✅ |
| `audit_log` | Traccia modifiche | Design: ✅ |
| `sospensioni_corso` | Sospensioni temporanee/definitive | Design: ✅ |

**Migration Strategy**: Definita in SCHEMA_ER_DATABASE.md (ALTER TABLE per rename soci → soci)

---

## 🛣️ Roadmap Implementazione

### Fase 1: Setup e Rinomina (Settimana 1-2) 
**Status**: 📋 PIANIFICATA
- [ ] Database migrations (soci → soci, nuove tabelle)
- [ ] Rinomina variabili codebase
- [ ] AuditLogController
- [ ] Pagina dati associazione
- [ ] Helper functions costi
**Tempo stimato**: 18-22 ore
**Priorità**: 🔴 CRITICA

### Fase 2: Iscrizioni Annuali (Settimana 3-4)
**Status**: 📋 PIANIFICATA
- [ ] Controller IscrizioniAnnuali
- [ ] Wizard 4-step nuovo socio
- [ ] Numero tessera auto-generato (YYYYN)
- [ ] Stampa domanda iscrizione
**Tempo stimato**: 20-26 ore
**Priorità**: 🔴 CRITICA

### Fase 3: Sconto Familiare (Settimana 5)
**Status**: 📋 PIANIFICATA
- [ ] Tabella famiglia
- [ ] UI collegamento familiari
- [ ] Check automatico stato famiglia
- [ ] Revoca prospettica
**Tempo stimato**: 10-14 ore
**Priorità**: 🟡 ALTA

### Fase 4: Configurazione Costi (Settimana 6)
**Status**: 📋 PIANIFICATA
- [ ] Costi biperiodici (agosto-febbraio vs marzo-luglio)
- [ ] Pagina admin gestione costi
**Tempo stimato**: 6-8 ore
**Priorità**: 🟢 MEDIA

### Fase 5: Gestione Corsi (Settimana 7-8)
**Status**: 📋 PIANIFICATA
- [ ] Modifiche corso con pro-rata
- [ ] Sospensioni temporanee/definitive
- [ ] Alert sovrapposizioni
**Tempo stimato**: 23-27 ore
**Priorità**: 🔴 CRITICA

### Fase 6: Pagamenti (Settimana 9-10)
**Status**: 📋 PIANIFICATA
- [ ] PagamentiController (calcoli pro-rata, sconti)
- [ ] Pagina pagamenti
- [ ] Stampe ricevute + email
- [ ] 3 sconti: Familiare, Compleanno, Mattutini
**Tempo stimato**: 35-42 ore
**Priorità**: 🔴 CRITICA

### Fase 7: Assenze (Settimana 11)
**Status**: 📋 PIANIFICATA
- [ ] Limite recuperi garantiti (default 3)
- [ ] Email reminder
**Tempo stimato**: 8-12 ore
**Priorità**: 🟢 MEDIA

### Fase 8: Batch Email (Settimana 12)
**Status**: 📋 PIANIFICATA
- [ ] Integrazione php-scheduler
- [ ] Batch ogni 26 del mese
- [ ] Email promemoria pagamento
- [ ] Pagina admin gestione batch
**Tempo stimato**: 11-15 ore
**Priorità**: 🟢 MEDIA

### Fase 9: Polish UI (Settimana 13)
**Status**: 📋 PIANIFICATA
- [ ] Colori sala nel calendario
- [ ] Lezioni in elenco soci
- [ ] Validazioni UI
- [ ] Toast notifications
**Tempo stimato**: 7-10 ore
**Priorità**: 🔵 BASSA

---

## 💰 Flussi Pagamento Documentati

### 1. Iscrizione Annuale ✅
- Una tantum per anno accademico (settembre-luglio)
- Costo biperiodico: €150 (agosto-feb) o €100 (marzo-lug)
- Numero tessera auto-generato: YYYYN
- Prerequisito per iscriversi a corsi

### 2. Sconto Familiare ✅
- 10% applicato se familiare ha corso attivo
- Revoca prospettica quando familiare termina corsi
- Esclusivo con altri sconti

### 3. Corsi Mensili ✅
- Conteggio lezioni mese
- Costo = lezioni × €/lezione
- Pro-rata automatico se modifica mid-month

### 4. Pro-Rata (Modifica Mid-Month) ✅
- Calcola lezioni rimanenti vecchio corso
- Calcola lezioni nuove nuovo corso
- Delta = differenza pagamento
- Se delta positivo → addebito, negativo → accredito

### 5. Sconti Applicabili ✅
- Sconto Familiare: 10% su corsi (+ iscrizione gratis)
- Sconto Compleanno: 5% su corsi (solo mese nascita)
- Sconto Mattutini: 50% iscrizione + 10% corsi (inizio < 11:00)
- Esclusione mutua

### 6. Batch Email (26 del mese) ✅
- Promemoria pagamento
- Include corsi + costi + sconti
- Email ricevuta pagamento
- Traccia in batch_runs

---

## 🧪 Testing Strategy

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

## 🔐 Security Checklist

- ✅ CSRF token in tutte le form (GIÀ IMPLEMENTATO fase precedente)
- [ ] SQL Injection prevention (PDO prepared statements) - VERIFICARE
- [ ] Input validation su tutti i form
- [ ] Rate limiting su API pagamenti
- [ ] Hash password admin
- [ ] Session timeout
- [ ] HTTPS in produzione
- [ ] Audit log obbligatorio su CREATE/UPDATE/DELETE
- [ ] Backup automatico database

---

## 📈 Metriche Stimate v3.0

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

## ✅ Checklist Pre-Implementazione

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

## 🚨 Blockers / Dipendenze Esterne

1. **Email Service**: User deve fornire credenziali SMTP o SendGrid
2. **php-scheduler**: Richiede webhook endpoint pubblico
3. **Accesso Cliente**: Per validazione UAT su scenari reali

---

## 📞 Contatti e Supporto

**Developer**: [Nome]
**Client**: [MusicAll Owner]
**Repository**: c:\git\ct-poc\MusicAll
**Documentation**: See ROADMAP_IMPLEMENTAZIONE.md, FLUSSI_PAGAMENTO.md, WIREFRAME_PAGINE_CRITICHE.md

---

## 📝 Changelog

### 15 Novembre 2026
- ✅ Creato ROADMAP_IMPLEMENTAZIONE.md (9 fasi, 138-176 ore)
- ✅ Creato FLUSSI_PAGAMENTO.md (8 flussi completi)
- ✅ Creato WIREFRAME_PAGINE_CRITICHE.md (8 pagine mockup)
- ✅ Documentazione tecnica completata

### 14 Novembre 2026 (sessione precedente)
- ✅ Creato SCHEMA_ER_DATABASE.md (12 tabelle + migration)
- ✅ Risolti 20+ dubbi nel ModificheSistema.md
- ✅ Confirmed 100% requisiti completeness

### 13 Novembre 2026 (sessione precedente)
- ✅ Bugfix: CSRF token in calendario.php
- ✅ Bugfix: RecuperiController::creaRecupero()

---

## 🎯 Prossimi Step

1. **Conferma Cliente**: Revisione roadmap + wireframe
2. **Setup Ambiente**: Git branch + testing framework
3. **Inizio Fase 1**: Database migrations + rinomina soci → soci
4. **Settimanale**: Review + commit dopo ogni sottosezione

**Data Target Inizio Implementazione**: 20 Novembre 2026

---

## 📄 File di Riferimento Rapido

| File | Scopo | Linee |
|------|-------|-------|
| SCHEMA_ER_DATABASE.md | Database design | ~1800 |
| ROADMAP_IMPLEMENTAZIONE.md | Fasi + timeline | ~400 |
| FLUSSI_PAGAMENTO.md | Business logic | ~1200 |
| WIREFRAME_PAGINE_CRITICHE.md | UI mockup | ~1000 |
| ModificheSistema.md | Business req (reference) | ~920 |

**Documentazione Totale**: ~5,320 linee di specifica tecnica

