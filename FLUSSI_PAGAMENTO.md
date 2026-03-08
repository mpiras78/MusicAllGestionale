# 💰 FLUSSI DI PAGAMENTO - MUSICALL v3.0

## Panoramica Sistema Pagamenti

```
ISCRIZIONE ANNUALE
    ↓
SCONTI APPLICABILI
    ↓
CORSI MENSILI
    ↓
CALCOLO PRO-RATA (se modifica)
    ↓
PAGAMENTO SINGOLO MESE
    ↓
STAMPA RICEVUTA + EMAIL
```

---

## 1. FLUSSO ISCRIZIONE ANNUALE

### 1.1 - Iscrizione Primo Anno Accademico

```
SCENARIO: Nuovo socio si iscrive a settembre 2026

FLOW:
1. Socio accede a "Nuova Iscrizione"
2. Wizard passo 1: inserisce dati anagrafici (nome, cognome, cf, data nascita, telefono, etc)
3. Wizard passo 2: seleziona sconto familiare (opzionale, con ricerca)
4. Wizard passo 3: riepilogo e conferma
5. Sistema genera:
   - numero_tessera = "2026" + progressivo (es: 20261, 20262, ...)
   - iscrizione_annuale con costo_iscrizione = €X basato su mese corrente
   - Se sconto familiare: costo_iscrizione = €0
6. Generazione PDF "Domanda di Iscrizione" (con firma)
7. Stato iscrizione: PAGAMENTO_PENDENTE

ATTORI:
- Socio (compila wizard)
- Sistema (genera tessera + email)

CRITERI CALCOLO COSTO ISCRIZIONE:
- Agosto-Febbraio: €X1 (es: €150)
- Marzo-Luglio: €X2 (es: €100)

ESEMPIO TEMPISTICHE:
- 1 Settembre 2026 → costo €150 (agosto-febbraio)
- 1 Novembre 2026 → costo €150 (agosto-febbraio)
- 1 Marzo 2027 → costo €100 (marzo-luglio)
```

### 1.2 - Rinnovo Iscrizione

```
SCENARIO: Socio rinnova iscrizione anno accademico nuovo

FLOW:
1. Ad agosto dell'anno successivo, socio rinnova
2. Sistema verifica: ultima iscrizione anno 2025 (scaduta luglio 2026)
3. Crea nuova iscrizione_annuale per anno 2026
4. Numero tessera aggiornato: "2026" + progressivo
5. Costo iscrizione = €150 (agosto, categoria agosto-febbraio)
6. Stato: PAGAMENTO_PENDENTE

ATTORI:
- Socio (accede a "Rinnova Iscrizione")
- Sistema (crea nuova riga iscrizioni_annuali)
```

### 1.3 - Pagamento Iscrizione

```
SCENARIO: Pagamento della quota di iscrizione annuale

FLOW:
1. Socio accede a "Pagamenti"
2. Visualizza: "Iscrizione Annuale - € 150 - NON PAGATO"
3. Pulsante "Paga Iscrizione"
4. Modal per selezionare modalità pagamento (Contanti / Bonifico)
5. Se Contanti:
   - Ricevuta immediata
   - Email ricevuta al socio
   - stato_pagamento = PAGATO
6. Se Bonifico:
   - Email con dati bonifico
   - Stato = IN ATTESA
   - Admin verifica e conferma
7. Stampa ricevuta

DATI RICEVUTA:
- Data pagamento
- Numero tessera
- Descrizione: "Iscrizione Annuale 2026"
- Importo: €150
- Modalità pagamento
- Firma admin (stampa)

NOTA IMPORTANTE:
- Iscrizione è prerequisito per iscriversi a corsi
- Se iscrizione non pagata, non può iscriversi a nuovi corsi
```

---

## 2. FLUSSO SCONTO FAMILIARE

### 2.1 - Applicazione Sconto Familiare

```
SCENARIO: Secondo figlio si iscrive, ha diritto a sconto famiglia

FLOW A - AL MOMENTO DELL'ISCRIZIONE:
1. Socio compila wizard iscrizione
2. Passo 2: checkbox "Ho familiari già iscritti"
3. Se checked: mostra ricerca socio
4. Digita cognome + nome del familiare
5. Sistema mostra familiari trovati
6. Seleziona uno
7. Sistema collega in tabella `famiglia`:
   - socio_id = nuovo socio
   - familiare_id = socio esistente
   - data_collegamento = oggi
   - stato = ATTIVO
8. Automaticamente: costo_iscrizione = €0
9. Salvataggio e genera PDF

DATI TABELLA FAMIGLIA:
- id
- socio_id
- familiare_id
- data_collegamento
- stato (ATTIVO / REVOCATO)
- data_revoca
- motivo_revoca

ATTORI:
- Nuovo socio (seleziona familiare)
- Sistema (verifica + collega)
```

### 2.2 - Revoca Prospettica Sconto Familiare

```
SCENARIO: Familiare smette di fare lezioni, sconto viene revocato

TRIGGER: Nell'ultima lezione del familiare viene raggiunto il "fine corso"

FLOW:
1. Corso del familiare raggiunge fine mese
2. Sistema verifica: ci sono ancora corsi attivi per il familiare?
3. Se NO:
   - Tabella `famiglia`: stato = REVOCATO
   - data_revoca = mese prossimo
4. Nel mese prossivo, nuovo socio perde sconto
5. Nel calendario pagamenti del nuovo socio:
   - Da prossimo mese non mostra più sconto applicabile
6. Admin riceve alert

LOGICA IN PAGINA PAGAMENTI:
- Checkbox "Sconto Familiare": ABILITATO se:
  * Socio ha collegamento famiglia ATTIVO
  * E almeno 1 familiare ha corso attivo in questo mese
- Checkbox "Sconto Familiare": DISABILITATO se:
  * Nessun familiare ha corso attivo
  * Famiglia già revocata

EMAIL ALERT:
- Destinatario: Admin
- Soggetto: "Revoca sconto familiare per socio XXXX"
- Corpo: nome nuovo socio, nome familiare che ha terminato, data revoca
```

### 2.3 - Tabella Famiglia

```
CREATE TABLE famiglia (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  familiare_id INT NOT NULL,
  data_collegamento DATE NOT NULL,
  stato ENUM('ATTIVO', 'REVOCATO') DEFAULT 'ATTIVO',
  data_revoca DATE,
  motivo_revoca VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  FOREIGN KEY (familiare_id) REFERENCES soci(id) ON DELETE CASCADE,
  UNIQUE KEY unique_coppia (socio_id, familiare_id)
);
```
---

## 3. FLUSSO CORSI MENSILI

### 3.1 - Iscrizione a Nuovo Corso

```
SCENARIO: Socio si iscrive al corso di Chitarra nel mese di Settembre

FLOW:
1. Socio accede a "Gestione Corsi"
2. Pulsante "Aggiungi Nuovo Corso"
3. Modal: seleziona corso (dropdown con materia, insegnante, sala, orario)
4. Seleziona se ricorrente (settimanale fino a quale mese?)
5. Sistema verifica:
   - Iscrizione annuale pagata? (Se NO → errore)
   - Orario libero? (Se NO → alert sovrapposizione, chiedi conferma)
   - Lezioni docente disponibili? (Se NO → errore)
6. Genera automaticamente:
   - Ricorrenze nel calendario (ogni settimana per N mesi)
   - Stato di ogni lezione = PROGRAMMATA
7. Salvataggio e conferma

CALCOLO COSTO AUTOMATICO:
- Conta lezioni nel mese (es: 4 lezioni)
- Legge costo_corso da configurazione (es: €30/lezione)
- Costo totale mese = 4 × €30 = €120
- Nel pagamenti di settembre: compare €120 per questo corso

ATTORI:
- Socio (seleziona corso)
- Sistema (verifica conflitti + crea ricorrenze)
```

### 3.2 - Modifica Corso Mid-Month

```
SCENARIO: Socio vuole cambiare corso a metà mese (es: da Chitarra a Pianoforte il 15/9)

FLOW:
1. Socio accede a "Gestione Corsi"
2. Seleziona corso "Chitarra"
3. Pulsante "Modifica"
4. Modal: nuovo corso (es: Pianoforte)
5. Sistema calcola PRO-RATA:
   - Lezioni Chitarra erogate fino a oggi = 2 (costo €60)
   - Lezioni Chitarra rimanenti = 2 (costo €60)
   - Nuove lezioni Pianoforte = 2 (costo €50)
   - Calcolo delta:
     * Costo Chitarra totale mese: €120
     * Già pagato/accantonato: €0 (mese non ancora pagato)
     * Se già pagato: delta = (€50) - (€120) = -€70 (accredito)
     * Se non pagato: da pagare €50 anziché €120

6. Mostra riepilogo:
   - Vecchio corso: Chitarra €120
   - Nuovo corso: Pianoforte €100
   - Differenza: -€20 (accredito) O +€20 (addebito)

7. Se differenza > 0: 
   - Crea pagamento "PRO-RATA Modifica Corso Chitarra → Pianoforte" con importo delta
   - Mese mensilita_riferimento = settembre 2026
   
8. Se differenza < 0:
   - Accredito (reso)
   - Crea nota in audit log

9. Salvataggio modifiche
```

### 3.3 - Sospensione Temporanea Corso

```
SCENARIO: Socio sospende corso per 2 mesi (febbraio-marzo)

FLOW:
1. Socio accede a "Gestione Corsi"
2. Pulsante "Sospendi Corso"
3. Modal:
   - Radio: "Temporanea" / "Definitiva"
   - Se temporanea: inserire mesi sospensione (es: febbraio-marzo)
   - Se definitiva: conferma cancellazione

4. SE TEMPORANEA:
   - Tabella sospensioni_corso:
     * corso_id = ID
     * tipo = TEMPORANEA
     * data_inizio = 1 febbraio
     * data_fine = 31 marzo
     * motivo = campo opzionale
   - Nel calendario: lezioni durante sospensione mostrate con colore grigio + icona "sospeso"
   - Nel pagamenti febbraio: corso NON compare
   - Nel pagamenti aprile: corso torna visibile

5. SE DEFINITIVA:
   - Tabella sospensioni_corso:
     * tipo = DEFINITIVA
     * data_inizio = oggi
   - Nel calendario: lezioni future cancellate
   - Nel pagamenti: corso NON compare più

ATTORI:
- Socio (seleziona sospensione)
- Sistema (aggiorna calendario + pagamenti)
```

### 3.4 - Alert Sovrapposizioni

```
SCENARIO: Socio tenta di iscriversi a 2 corsi nello stesso orario

FLOW:
1. Socio tenta di aggiungere corso A (lunedì 17:00-18:00)
2. Socio ha già corso B (lunedì 16:45-18:00)
3. Sistema rileva sovrapposizione
4. Modal di WARNING:
   - "Attenzione: Sovrapposizione oraria con corso B"
   - "Confermi comunque?"
5. Se CONFERMO:
   - Sistema crea ALERT in tabella alert
   - Tabella alert:
     * socio_id
     * corso1_id
     * corso2_id
     * tipo = SOVRAPPOSIZIONE
     * descrizione = "Lunedì 17:00-18:00 coincide con corso B"
     * visibile = 1 (da vedere)
     * data_creazione
   - Email alert all'admin

6. Admin accede a "Gestione Alert"
7. Vede elenco alert non visti (visibile=1)
8. Può:
   - Contatta socio
   - Marcare come visto (visibile=0)
   - NON PUÒ ELIMINARE (alert sono permanenti per audit trail)

NOTA: Alert non ha scadenza, rimane storico. Se visibile=0, nascosto da vista predefinita.
```

---

## 4. FLUSSO PAGAMENTI MENSILI

### 4.1 - Visualizzazione Pagamenti (Pagina Principale)

```
SCENARIO: Socio accede a "Pagamenti" il 20 novembre 2026

PAGINA MOSTRA:

┌─────────────────────────────────────────────────────┐
│ NOVEMBRE 2026                                        │
├─────────────────────────────────────────────────────┤
│                                                     │
│ ISCRIZIONE ANNUALE (pagato a settembre)             │
│ □ Numero Tessera: 20261                            │
│ [INFO NASCOSTO]                                    │
│                                                     │
├─────────────────────────────────────────────────────┤
│ CORSI NOVEMBRE 2026                                 │
│                                                     │
│ 1) Chitarra - 4 lezioni - € 120                    │
│    Insegnante: Marco Rossi, Martedì 17:00-18:00   │
│    [MODIFICA] [SOSPENDI]                           │
│                                                     │
│ 2) Pianoforte - 2 lezioni - € 60                   │
│    Insegnante: Anna Verdi, Venerdì 19:00-20:00    │
│    [MODIFICA] [SOSPENDI]                           │
│                                                     │
├─────────────────────────────────────────────────────┤
│ SCONTI APPLICABILI                                  │
│                                                     │
│ ☑ Sconto Familiare (-€20) *ABILITATO*              │
│   Familiare: Marco Rossi (attivo)                  │
│                                                     │
│ ○ Sconto Compleanno (-€15) *DISABILITATO*          │
│   (Compleanno non questo mese)                     │
│                                                     │
│ ○ Sconto Mattutini (-30% corso) *DISABILITATO*     │
│   (Nessun corso con inizio prima 11:00)            │
│                                                     │
├─────────────────────────────────────────────────────┤
│ RIEPILOGO PAGAMENTO                                 │
│                                                     │
│ Sottotonale corsi:          € 180                  │
│ - Sconto Familiare:         - € 20                 │
│ ──────────────────────────────────────             │
│ TOTALE DA PAGARE:           € 160                  │
│                                                     │
│ Nota (opzionale):                                   │
│ [...text area...]                                   │
│                                                     │
│ [STAMPA RICEVUTA] [PAGA BONIFICO] [PAGA CONTANTI]│
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 4.2 - Salvataggio Pagamento

```
FLOW PAGAMENTO CONTANTI:
1. Socio clicca "PAGA CONTANTI"
2. Seleziona modalità: CONTANTI
3. Sistema salva in tabella pagamenti:
   - socio_id
   - importo = €160
   - mensilita_riferimento = "2026-11"
   - modalita_pagamento_id = 1 (CONTANTI)
   - stato = PAGATO
   - data_pagamento = oggi
   - admin_id = NULL (pagato da socio direttamente)
   - note = eventuale nota
   - created_at = timestamp
   
4. Genera ricevuta:
   - Numero ricevuta auto-incrementante (es: RIC-2026-001)
   - Include tutti i dati pagamento
   - Includi dati dati_associazione (nome scuola, etc)
   
5. Invia email al socio:
   - Allegato: PDF ricevuta
   - Corpo: "Pagamento confermato per novembre 2026 - €160"
   - Include dati pagamento (corsi, sconti, totale)
   
6. Mostra feedback: "Pagamento registrato! Ricevuta stampata."

FLOW PAGAMENTO BONIFICO:
1. Socio clicca "PAGA BONIFICO"
2. Sistema salva in tabella pagamenti:
   - stato = IN_ATTESA (anziché PAGATO)
   - data_attesa = oggi
   
3. Invia email al socio:
   - IBAN della scuola (da dati_associazione)
   - Causale: "Pagamento lezioni novembre 2026 - Socio 20261"
   - Importo: €160
   - Istruzioni per la verifica
   
4. Email admin:
   - "Nuovo pagamento in attesa - Socio XXXX - €160"
   - Admin accede a "Gestione Pagamenti" e verifica
   
5. Admin marca come "Verificato":
   - stato = PAGATO
   - data_pagamento = data effettiva
   - admin_id = ID admin che ha verificato
```

### 4.3 - Pro-Rata: Modifica Corso con Pagamento Effettuato

```
SCENARIO: Socio ha pagato Chitarra (€120), il 15/11 cambia a Pianoforte (€100)

TABELLA PAGAMENTI PER NOVEMBRE:

┌─────────────────────────────────────────────────┐
│ Pagamento 1: Iscrizione + Chitarra               │
│ - Data: 5 novembre                               │
│ - Importo: €150 (iscrizione) + €120 (chitarra)  │
│ - Totale: €270                                   │
│ - mensilita_riferimento: 2026-09 (iscrizione)   │
│ - mensilita_riferimento: 2026-11 (chitarra)     │
│                                                   │
│ Pagamento 2: PRO-RATA Modifica Chitarra→Pianoforte│
│ - Data: 15 novembre                              │
│ - Descrizione: "Pro-rata modifica corso"         │
│ - Costo Chitarra già pagato: €120                │
│ - Lezioni Chitarra rimanenti (15/11-30/11): 1  │
│ - Costo rimanente Chitarra: €30                 │
│ - Lezioni Pianoforte (15/11-30/11): 1           │
│ - Costo Pianoforte: €25                          │
│ - DELTA: €25 - €30 = -€5 (ACCREDITO)            │
│ - Importo: -€5 (mostrato come accredito)        │
│ - mensilita_riferimento: 2026-11                │
│                                                   │
│ TOTALE PAGATO NOVEMBRE: €270 - €5 = €265       │
└─────────────────────────────────────────────────┘

LOGICA DETTAGLIATA:
1. Lezioni rimanenti Chitarra:
   - Fine corso: 30 novembre
   - Data modifica: 15 novembre
   - Lezioni da 15/11 a 30/11: supponiamo martedì → 1 lezione
   - Costo: 1 × €30/lezione = €30

2. Lezioni nuove Pianoforte:
   - Inizio corso: 15 novembre
   - Fine mese: 30 novembre
   - Lezioni da 15/11 a 30/11: supponiamo venerdì → 1 lezione (il 20/11)
   - Costo: 1 × €25/lezione = €25

3. DELTA = €25 - €30 = -€5
4. Siccome delta < 0: ACCREDITO di €5
5. Se delta > 0: ADDEBITO aggiuntivo

RECORD IN AUDIT LOG:
- Azione: MODIFICA_CORSO_PRO_RATA
- Tabella: corsi_soci
- Vecchio valore: {"corso_id": 5, "stato": "ATTIVO"}
- Nuovo valore: {"corso_id": 8, "stato": "ATTIVO"}
- Delta: -€5
- Data: 15 novembre 2026
- Admin_id: NULL (modificato da socio)
```

---

## 5. FLUSSO SCONTI APPLICABILI

### 5.1 - Sconto Familiare

```
CONDIZIONI DI ABILITAZIONE:
- Socio ha un collegamento ATTIVO in tabella famiglia
- Almeno uno dei familiari ha lezioni attive nel mese corrente

CALCOLO SCONTO:
- Percentuale: configurabile (default 10%)
- Applicato a: importo totale corsi del mese
- NON applicato a: iscrizione annuale

DISABILITAZIONE AUTOMATICA:
- Quando ultimo familiare finisce tutti i corsi
- Sistema: stato famiglia → REVOCATO, data_revoca = prossimo mese

ESEMPIO:
- Sconto 10%
- Corsi novembre: €180
- Sconto familiare: -€18
- Nuovo totale: €162
```

### 5.2 - Sconto Compleanno

```
CONDIZIONI DI ABILITAZIONE:
- Socio ha compleanno nel mese corrente
- Data nascita registrata nel database

CALCOLO SCONTO:
- Percentuale: configurabile (default 5%)
- Applicato a: importo totale corsi del mese

LOGICA VERIFICA:
- In pagina pagamenti, query verifica:
  SELECT * FROM soci 
  WHERE MONTH(data_nascita) = MONTH(NOW())
  AND YEAR(data_nascita) != YEAR(NOW())

NOTA IMPORTANTE:
- Sconto NON cumulativo con altri sconti
- Se ha sconto familiare E compleanno → scegli uno
- Logica: "Seleziona sconto applicabile" (radio button)
```

### 5.3 - Sconto Mattutini (Iscrizione + Corso)

```
CONDIZIONI:
- Iscrizione annuale a prezzo ridotto (50% del prezzo normale)
- PLUS: Sconto 10% su tutti i corsi con inizio PRIMA delle 11:00

CALCOLO ISCRIZIONE:
- Normale: €150 / €100
- Mattutina: €75 / €50

CALCOLO CORSI:
- Ad ogni corso: verificare se inizio < 11:00
- Se sì: applica 10% sconto

LOGICA APPLICAZIONE:
- Al momento dell'iscrizione: socio seleziona "Mattutina"
- Automaticamente: costo_iscrizione dimezzato
- Nel pagamenti: corsi mattutini hanno sconto 10% automatico

ESEMPIO:
- Iscrizione mattutina: €75
- Corsi settembre: Pianoforte mattutino €30/lezione (4 lezioni) → €120
- Con sconto: 120 - (120 × 10%) = €108
- Totale pagamento: €75 + €108 = €183

ESCLUSIONE MUTUA:
- Sconto mattutini esclude sconto familiare
- Sconto mattutini esclude sconto compleanno
```

---

## 6. FLUSSO BATCH EMAIL (26 DEL MESE)

### 6.1 - Trigger e Scheduling

```
TRIGGER: Ogni 26 del mese alle 08:00 (via webhook php-scheduler)

FLOW:
1. Viene richiamato endpoint: /api/api_batch_email.php?action=send_monthly_reminder
2. Sistema verifica:
   - Oggi è 26 del mese? Se no → esci
   - Ultimo run è stato ieri? Se sì → esci (protezione doppi invii)

3. Query: trovare TUTTI i soci con corsi attivi nel mese successivo
   ```sql
   SELECT DISTINCT s.id, s.email
   FROM soci s
   JOIN corsi_soci cs ON s.id = cs.socio_id
   WHERE cs.stato = 'ATTIVO'
   AND MONTH(cs.data_inizio) = MONTH(DATE_ADD(NOW(), INTERVAL 1 MONTH))
   AND YEAR(cs.data_inizio) = YEAR(DATE_ADD(NOW(), INTERVAL 1 MONTH))
   AND s.email IS NOT NULL
   ```

4. Per ogni socio trovato:
   - Calcola corsi mese prossimo
   - Conteggia lezioni
   - Calcola totale pagamento (con sconti)
   - Genera email personalizzata
   - Invia email

5. Log esecuzione:
   - Tabella batch_runs:
     * batch_tipo = "EMAIL_PROMEMORIA_PAGAMENTO"
     * data_esecuzione = 26/11/2026
     * numero_email_inviate = 45
     * numero_email_fallite = 0
     * status = SUCCESSO
     * log_dettagli = NULL (o dettagli errori)
```

### 6.2 - Contenuto Email Promemoria

```
DESTINATARIO: email@socialemail.it

SUBJECT: Promemoria pagamento lezioni - Novembre 2026

BODY (HTML):

┌─────────────────────────────────────────────────────┐
│ Caro Marco,                                         │
│                                                      │
│ Ti ricordiamo che il mese di NOVEMBRE 2026 è quasi │
│ terminato. Ecco il riepilogo dei tuoi corsi:        │
│                                                      │
│ ────────────────────────────────────────────────    │
│ NOVEMBRE 2026                                       │
│ ────────────────────────────────────────────────    │
│                                                      │
│ Chitarra (Marco Rossi)                              │
│ Martedì 17:00-18:00                                 │
│ 4 lezioni - € 120                                   │
│                                                      │
│ Pianoforte (Anna Verdi)                             │
│ Venerdì 19:00-20:00                                 │
│ 2 lezioni - € 60                                    │
│                                                      │
│ ────────────────────────────────────────────────    │
│ TOTALE: € 180                                       │
│                                                      │
│ Puoi pagare online accedendo al tuo profilo oppure │
│ contattarci per modalità diverse.                   │
│                                                      │
│ [PAGA ORA] (link)                                   │
│                                                      │
│ Grazie,                                             │
│ Scuola di Musica XYZ                                │
│                                                      │
│ Dati contatto: ...                                  │
└─────────────────────────────────────────────────────┘

EMAIL RICEVUTA PAGAMENTO:

SUBJECT: Ricevuta pagamento lezioni - Novembre 2026

BODY (HTML):

┌─────────────────────────────────────────────────────┐
│ Caro Marco,                                         │
│                                                      │
│ Grazie per il tuo pagamento!                        │
│                                                      │
│ ────────────────────────────────────────────────    │
│ RICEVUTA PAGAMENTO                                  │
│ ────────────────────────────────────────────────    │
│                                                      │
│ Numero Ricevuta: RIC-2026-001                       │
│ Numero Tessera: 20261                               │
│ Data Pagamento: 20 novembre 2026                    │
│ Importo: € 160                                      │
│ Modalità: Contanti                                  │
│                                                      │
│ Dettagli Pagamento:                                 │
│ - Chitarra (4 lezioni): € 120                      │
│ - Pianoforte (2 lezioni): € 60                     │
│ - Sconto Familiare: - € 20                          │
│ TOTALE: € 160                                       │
│                                                      │
│ La ricevuta è allegata a questa email in formato PDF
│                                                      │
│ Grazie,                                             │
│ Scuola di Musica XYZ                                │
└─────────────────────────────────────────────────────┘
```

---

## 7. TABELLE PAGAMENTI

### 7.1 - Tabella `pagamenti`

```sql
CREATE TABLE pagamenti (
  id INT PRIMARY KEY AUTO_INCREMENT,
  socio_id INT NOT NULL,
  importo DECIMAL(10, 2) NOT NULL,
  mensilita_riferimento VARCHAR(7) NOT NULL, -- 2026-11
  modalita_pagamento_id INT NOT NULL,
  stato ENUM('PAGATO', 'IN_ATTESA', 'ANNULLATO') DEFAULT 'PAGATO',
  data_pagamento DATETIME,
  admin_id INT, -- NULL se pagato da socio, ID admin se verificato
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE,
  FOREIGN KEY (modalita_pagamento_id) REFERENCES modalita_pagamento(id),
  FOREIGN KEY (admin_id) REFERENCES admin_users(id),
  INDEX idx_socio_mese (socio_id, mensilita_riferimento),
  INDEX idx_stato (stato),
  INDEX idx_data_pagamento (data_pagamento)
);
```

### 7.2 - Tabella `modalita_pagamento`

```sql
CREATE TABLE modalita_pagamento (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(50) NOT NULL, -- CONTANTI, BONIFICO, CARTA
  attivo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dati iniziali:
INSERT INTO modalita_pagamento (nome) VALUES
('CONTANTI'),
('BONIFICO'),
('CARTA');
```

### 7.3 - Tabella `batch_runs`

```sql
CREATE TABLE batch_runs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  batch_tipo VARCHAR(100) NOT NULL,
  data_esecuzione DATETIME NOT NULL,
  numero_email_inviate INT DEFAULT 0,
  numero_email_fallite INT DEFAULT 0,
  status ENUM('SUCCESSO', 'ERRORE', 'PARZIALE') DEFAULT 'SUCCESSO',
  log_dettagli TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 8. FLUSSO COMPLETAMENTO E VALIDAZIONI

### 8.1 - Validazioni Pagamento

```
PRIMA DI SALVARE PAGAMENTO, VERIFICARE:

✓ Socio esiste
✓ Iscrizione annuale pagata (se nuovo socio)
✓ Importo > 0
✓ mensilita_riferimento è valida (YYYY-MM)
✓ modalita_pagamento_id esiste
✓ Se stato=PAGATO: data_pagamento è presente
✓ Se stato=IN_ATTESA: data_pagamento è NULL
✓ Se admin_id presente: admin_id esiste in admin_users
```

### 8.2 - Email Fallback

```
SE INVIO EMAIL FALLISCE:
1. Log errore in batch_runs
2. Stato batch = PARZIALE
3. admin@scuola.it riceve alert
4. Pagamento rimane salvato (non viene annullato)
5. Socio può comunque accedere al pagamento in pagina pagamenti
```

---

## Riepilogo Diagramma Completo

```
┌──────────────────────────────────┐
│   ISCRIZIONE ANNUALE             │
│   (una volta all'anno)           │
│   Costo: €150 o €100             │
│   Numero Tessera: YYYYN          │
└──────────────┬────────────────────┘
               │
               ↓
┌──────────────────────────────────┐
│   SCONTO FAMILIARE (opzionale)   │
│   Iscrizione GRATIS              │
│   + Sconto 10% corsi             │
└──────────────┬────────────────────┘
               │
               ↓
┌──────────────────────────────────┐
│   CORSI MENSILI                  │
│   (ricorrenti settimanali)       │
│   Costo: €X per lezione          │
│   Conteggio: numero lezioni mese │
└──────────────┬────────────────────┘
               │
      ┌────────┴────────┐
      ↓                 ↓
┌─────────────┐   ┌──────────────┐
│ MODIFICA    │   │ SOSPENSIONE  │
│ PRO-RATA    │   │ TEMPORANEA   │
│ (delta)     │   │ (ricorrenze) │
└─────────────┘   └──────────────┘
      │                 │
      └────────┬────────┘
               ↓
┌──────────────────────────────────┐
│   SCONTI APPLICABILI             │
│   - Familiare 10%                │
│   - Compleanno 5%                │
│   - Mattutini 50% isc + 10% cors │
│   (mutuamente esclusivi)         │
└──────────────┬────────────────────┘
               │
               ↓
┌──────────────────────────────────┐
│   PAGAMENTO SINGOLO MESE         │
│   (contanti / bonifico)          │
│   Modalità: CONTANTI / BONIFICO  │
│   Stato: PAGATO / IN_ATTESA      │
└──────────────┬────────────────────┘
               │
               ↓
┌──────────────────────────────────┐
│   RICEVUTA + EMAIL               │
│   PDF ricevuta                   │
│   Email conferma                 │
└──────────────────────────────────┘
```

---

## Checklist di Implementazione

- [ ] Tabella pagamenti
- [ ] Tabella modalita_pagamento
- [ ] Tabella batch_runs
- [ ] Tabella famiglia
- [ ] Tabella sospensioni_corso
- [ ] Tabella alert
- [ ] PagamentiController.php
- [ ] FamigliaController.php
- [ ] pagine/pagamenti.php
- [ ] Stampa ricevuta
- [ ] Email promemoria
- [ ] Email ricevuta
- [ ] Batch scheduler
- [ ] Validazioni
- [ ] Audit log integrato
- [ ] Test end-to-end
