# 📐 WIREFRAME E MOCKUP PAGINE CRITICHE - MUSICALL v3.0

## 1. PAGINA NUOVA ISCRIZIONE (Wizard Multi-Step)

### Step 1/4 - Dati Anagrafici

```
┌─────────────────────────────────────────────────────────────┐
│ NUOVA ISCRIZIONE - MUSICALL                          [X]    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Step 1 di 4: Dati Anagrafici                               │
│ ████░░░░░░░░░░░░░░░░░░░░░░░░░░░ 25%                       │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ SEZIONE: Dati Personali                                    │
│                                                              │
│ Nome *                                                       │
│ [____________________________________________]              │
│                                                              │
│ Cognome *                                                    │
│ [____________________________________________]              │
│                                                              │
│ Codice Fiscale *                                            │
│ [____________]  (es: RSCMRA80A01H501V)                     │
│                                                              │
│ Data di Nascita *                                           │
│ [__________]  ← calendario picker                           │
│                                                              │
│ Genere                                                       │
│ ○ Maschio  ○ Femmina  ○ Altro                              │
│                                                              │
│                                                              │
│ SEZIONE: Contatti                                           │
│                                                              │
│ Telefono Principale *                                       │
│ [+39] [_______________]                                     │
│                                                              │
│ Telefono Secondario                                         │
│ [+39] [_______________]                                     │
│                                                              │
│ Email *                                                      │
│ [____________________________________________]              │
│                                                              │
│ Indirizzo                                                    │
│ [____________________________________________]              │
│                                                              │
│ CAP                                                          │
│ [_______]                                                    │
│                                                              │
│ Città *                                                      │
│ [__________________________]                                 │
│                                                              │
│ Provincia                                                    │
│ [__]  (dropdown)                                            │
│                                                              │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│ [← INDIETRO] [AVANTI →]                                    │
└─────────────────────────────────────────────────────────────┘
```

### Step 2/4 - Sconto Familiare

```
┌─────────────────────────────────────────────────────────────┐
│ NUOVA ISCRIZIONE - MUSICALL                          [X]    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Step 2 di 4: Sconto Familiare (opzionale)                 │
│ ████████░░░░░░░░░░░░░░░░░░░░░░░░░░ 50%                    │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ ☐ Ho familiari già iscritti che mi danno diritto a sconto  │
│                                                              │
│   Se checked ↓                                              │
│   ┌───────────────────────────────────────────────────────┐│
│   │ Ricerca Familiare                                     ││
│   │ [Cognome + Nome: ________________________]             ││
│   │ [🔍 CERCA]                                            ││
│   │                                                        ││
│   │ Risultati:                                            ││
│   │ ⊙ Rossi Marco (Chitarra, attivo)                    ││
│   │ ⊙ Rossi Anna (Pianoforte, sospeso)                  ││
│   │ ⊙ Rossi Luca (nessun corso)                         ││
│   │                                                        ││
│   │ * Selezionare il familiare che ha corso attivo        ││
│   └───────────────────────────────────────────────────────┘│
│                                                              │
│ INFO SCONTO FAMILIARE:                                     │
│ ✓ Costo iscrizione: GRATUITA (€0 anziché €150)            │
│ ✓ Sconto 10% su tutti i corsi (€120 → €108)              │
│ ⚠ Lo sconto non si applica se il familiare termina corsi  │
│   (Revoca prospettica)                                      │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│ [← INDIETRO] [AVANTI →]                                    │
└─────────────────────────────────────────────────────────────┘
```

### Step 3/4 - Riepilogo

```
┌─────────────────────────────────────────────────────────────┐
│ NUOVA ISCRIZIONE - MUSICALL                          [X]    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Step 3 di 4: Riepilogo Dati                                │
│ ██████████████░░░░░░░░░░░░░░░░░░░░░░░░ 75%                │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ DATI PERSONALI                                             │
│ ────────────────────────────────────────────────────────   │
│ Nome:                        Marco Rossi                    │
│ Data Nascita:                01/01/1990                     │
│ Codice Fiscale:              RSCMRA90A01H501V              │
│ Telefoni:                    320/1234567 - 010/123456      │
│ Email:                       marco@email.it                │
│ Indirizzo:                   Via Roma 123, 16100 Genova    │
│                                                              │
│ ISCRIZIONE ANNUALE                                         │
│ ────────────────────────────────────────────────────────   │
│ Anno Accademico:             2026 (Settembre 2026)         │
│ Numero Tessera:              20261                          │
│ Costo Iscrizione:            €150                           │
│ (Determinato dal mese corrente: Agosto-Febbraio)           │
│                                                              │
│ SCONTO FAMILIARE                                           │
│ ────────────────────────────────────────────────────────   │
│ Applicato:                   ☑ SI                           │
│ Familiare:                   Rossi Anna (Pianoforte)       │
│ Costo Iscrizione (gratuita): €0                            │
│ Sconto sui corsi:            10%                            │
│                                                              │
│ TOTALE DOVUTO (AL MOMENTO):  €0 (sconto familiare)        │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│ ☐ Dichiaro di aver letto le condizioni di servizio         │
│ ☑ Accetto l'informativa sulla privacy                      │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│ [← INDIETRO] [GENERA DOMANDA E CONFERMA]                  │
└─────────────────────────────────────────────────────────────┘
```

### Step 4/4 - Domanda Generata

```
┌─────────────────────────────────────────────────────────────┐
│ NUOVA ISCRIZIONE - MUSICALL                          [X]    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Step 4 di 4: Iscrizione Completata                         │
│ ████████████████████████████████████████████ 100%          │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ ✅ ISCRIZIONE REGISTRATA CON SUCCESSO!                     │
│                                                              │
│ Numero Tessera: 20261                                       │
│ Data Iscrizione: 15 novembre 2026                           │
│ Costo Iscrizione: €0 (Sconto Familiare)                    │
│                                                              │
│ La domanda di iscrizione è stata generata e inviata         │
│ alla tua email: marco@email.it                              │
│                                                              │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ DOCUMENTO GENERATO:                                  │   │
│ │                                                       │   │
│ │ 📄 Domanda_Iscrizione_20261.pdf                      │   │
│ │    [SCARICA] [STAMPA] [VISUALIZZA]                   │   │
│ │                                                       │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ PROSSIMI PASSI:                                            │
│ 1. Stampa la domanda di iscrizione                          │
│ 2. Firma la domanda                                         │
│ 3. Consegna all'istituto (o scatta foto per email)         │
│ 4. Accedi a "Pagamenti" per registrare primo pagamento    │
│                                                              │
│ Puoi ora iscriverti ai corsi da "Gestione Corsi"          │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│ [TORNA ALLA HOME] [ACCEDI A PAGAMENTI] [MODIFICA DATI]    │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. PAGINA PAGAMENTI

```
┌──────────────────────────────────────────────────────────────────┐
│ GESTIONE PAGAMENTI - MUSICALL                            [=][-][X]
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ PAGAMENTI - Novembre 2026                                        │
│                                                                   │
│ ← Novembre 2026 → [MESE PRECEDENTE] [OGGI] [MESE PROSSIMO]      │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 📋 ISCRIZIONE ANNUALE                                            │
│ ┌──────────────────────────────────────────────────────────────┐│
│ │ Numero Tessera: 20261                                        ││
│ │ Costo: €150 (Pagato il 05 settembre 2026)                   ││
│ │ Stato: ✅ PAGATO                                            ││
│ │                                                               ││
│ │ [INFO]                                                        ││
│ └──────────────────────────────────────────────────────────────┘│
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 🎵 CORSI NOVEMBRE 2026                                           │
│                                                                   │
│ [1] CHITARRA - Marco Rossi                                       │
│ ────────────────────────────────────────────────────────────────│
│     Insegnante: Marco Rossi                                      │
│     Orario: Martedì 17:00-18:00                                 │
│     Sala: A                                                       │
│     Lezioni nel mese: 4                                          │
│     Costo: €30 × 4 = €120                                       │
│                                                                   │
│     [MODIFICA] [SOSPENDI]                                       │
│                                                                   │
│ [2] PIANOFORTE - Anna Verdi                                      │
│ ────────────────────────────────────────────────────────────────│
│     Insegnante: Anna Verdi                                       │
│     Orario: Venerdì 19:00-20:00                                 │
│     Sala: C                                                       │
│     Lezioni nel mese: 2                                          │
│     Costo: €25 × 2 = €50                                        │
│                                                                   │
│     [MODIFICA] [SOSPENDI]                                       │
│                                                                   │
│ [+ AGGIUNGI NUOVO CORSO]                                        │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 🎁 SCONTI APPLICABILI                                            │
│ ────────────────────────────────────────────────────────────────│
│                                                                   │
│ ☑ SCONTO FAMILIARE (-10%)                    ✅ ABILITATO       │
│   Familiare: Rossi Anna (Pianoforte, attivo fino a giugno)      │
│   Importo sconto: -€17                                           │
│                                                                   │
│ ☐ SCONTO COMPLEANNO (-5%)                     ❌ DISABILITATO   │
│   Il tuo compleanno non è in questo mese                        │
│                                                                   │
│ ☐ SCONTO MATTUTINI (50% isc + 10% corsi)     ❌ DISABILITATO   │
│   Nessun corso con inizio prima delle 11:00                    │
│                                                                   │
│ ℹ Nota: È possibile selezionare solo uno sconto                │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 📊 RIEPILOGO PAGAMENTO                                           │
│ ┌──────────────────────────────────────────────────────────────┐│
│ │                                                               ││
│ │ Subtotale Corsi:                          €170               ││
│ │                                                               ││
│ │ - Sconto Familiare (10%):                - €17                ││
│ │                                                               ││
│ │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  ││
│ │ TOTALE DOVUTO:                            €153               ││
│ │                                                               ││
│ │ Nota (opzionale):                                             ││
│ │ [────────────────────────────────────────────────────]      ││
│ │                                                               ││
│ └──────────────────────────────────────────────────────────────┘│
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 💳 MODALITÀ DI PAGAMENTO                                         │
│                                                                   │
│ ⊙ CONTANTI (Paga in sede)         [PAGA]                       │
│ ⊙ BONIFICO (Trasferimento bancario) [PAGA]                     │
│ ⊙ CARTA (Online)                   [PAGA] (coming soon)        │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 📜 CRONOLOGIA PAGAMENTI NOVEMBRE 2026:                          │
│ ────────────────────────────────────────────────────────────────│
│ • 05 Nov | Iscrizione + Chitarra        | €270  | ✅ Contanti   │
│ • 15 Nov | Pro-rata Modifica Corso      | -€5   | ✅ Contanti   │
│ • 20 Nov | Pianoforte                   | €50   | ⏳ In Attesa  │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ AZIONI DISPONIBILI:                                              │
│ [🖨️ STAMPA RICEVUTA] [💾 SALVA] [📧 EMAIL]                      │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. PAGINA GESTIONE CORSI

```
┌──────────────────────────────────────────────────────────────────┐
│ GESTIONE CORSI - MUSICALL                             [=][-][X] │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 🎵 I MIEI CORSI                                                  │
│                                                                   │
│ [+ AGGIUNGI NUOVO CORSO]                                        │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ [1] CHITARRA - Marco Rossi                                       │
│ ╔══════════════════════════════════════════════════════════════╗│
│ ║ Insegnante: Marco Rossi                                      ║│
│ ║ Orario: Martedì 17:00-18:00                                  ║│
│ ║ Sala: A                                                        ║│
│ ║ Stato: ✅ ATTIVO                                             ║│
│ ║ Inizio: 01 settembre 2026 | Fine: 31 luglio 2027            ║│
│ ║                                                                ║│
│ ║ Lezioni Programmate: 41                                       ║│
│ ║ Lezioni Completate: 9                                         ║│
│ ║ Lezioni Rimanenti: 32                                         ║│
│ ║                                                                ║│
│ ║ [MODIFICA] [SOSPENDI] [CANCELLA]                             ║│
│ ╚══════════════════════════════════════════════════════════════╝│
│                                                                   │
│ [2] PIANOFORTE - Anna Verdi                                      │
│ ╔══════════════════════════════════════════════════════════════╗│
│ ║ Insegnante: Anna Verdi                                       ║│
│ ║ Orario: Venerdì 19:00-20:00                                  ║│
│ ║ Sala: C                                                        ║│
│ ║ Stato: ✅ ATTIVO                                             ║│
│ ║ Inizio: 15 novembre 2026 | Fine: 31 luglio 2027             ║│
│ ║                                                                ║│
│ ║ Lezioni Programmate: 32                                       ║│
│ ║ Lezioni Completate: 1                                         ║│
│ ║ Lezioni Rimanenti: 31                                         ║│
│ ║                                                                ║│
│ ║ [MODIFICA] [SOSPENDI] [CANCELLA]                             ║│
│ ╚══════════════════════════════════════════════════════════════╝│
│                                                                   │
│ [3] BATTERIA - Luigi Bianchi                                     │
│ ╔══════════════════════════════════════════════════════════════╗│
│ ║ Insegnante: Luigi Bianchi                                    ║│
│ ║ Orario: Mercoledì 18:00-19:00                                ║│
│ ║ Sala: B                                                        ║│
│ ║ Stato: 🟡 SOSPESO TEMPORANEAMENTE                            ║│
│ ║ Sospensione: 01 dicembre 2026 - 31 gennaio 2027              ║│
│ ║ Motivo: Pausa invernale                                        ║│
│ ║                                                                ║│
│ ║ [RIPRENDI] [CANCELLA SOSPENSIONE] [MODIFICA]                ║│
│ ╚══════════════════════════════════════════════════════════════╝│
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 📋 RIEPILOGO CORSI ATTIVI:                                       │
│ • Chitarra: Martedì 17:00-18:00 (Sala A)                        │
│ • Pianoforte: Venerdì 19:00-20:00 (Sala C)                      │
│ • Batteria: (SOSPESO fino a 31 gennaio)                         │
│                                                                   │
│ Costo Totale Novembre: €170 (4 Chitarra + 2 Pianoforte)        │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 4. MODAL MODIFICA CORSO (PRO-RATA)

```
┌────────────────────────────────────────────────────────────┐
│ MODIFICA CORSO - CALCOLO PRO-RATA              [X]        │
├────────────────────────────────────────────────────────────┤
│                                                            │
│ Stai modificando il corso di: CHITARRA                    │
│                                                            │
├────────────────────────────────────────────────────────────┤
│                                                            │
│ CORSO ATTUALE:                                            │
│ Chitarra - Marco Rossi                                   │
│ Martedì 17:00-18:00 (Sala A)                             │
│ Costo: €30/lezione                                        │
│                                                            │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ SELEZIONA NUOVO CORSO:                               │ │
│ │                                                       │ │
│ │ Materia: [Pianoforte ▼]                             │ │
│ │ Insegnante: [Anna Verdi ▼]                          │ │
│ │ Orario: [Venerdì 19:00-20:00 ▼]                    │ │
│ │ Sala: [C ▼]                                          │ │
│ │                                                       │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                            │
│ ⚠️  CALCOLO PRO-RATA (modifiche dal 15 novembre)         │
│ ────────────────────────────────────────────────────────  │
│                                                            │
│ Chitarra (fino al 14 novembre):                           │
│ • Lezioni: 3 (6, 13 nov)                                 │
│ • Costo: €30 × 3 = €90                                   │
│                                                            │
│ Pianoforte (da 15 novembre in poi):                      │
│ • Lezioni: 2 (20, 27 nov)                                │
│ • Costo: €25 × 2 = €50                                   │
│                                                            │
│ ┌────────────────────────────────────────────────────────┐│
│ │ RIEPILOGO PAGAMENTO:                                  ││
│ │                                                        ││
│ │ Costo Chitarra (novembre): €120                       ││
│ │ - Pagato a settembre: €90                             ││
│ │ = Rimanente: €30                                       ││
│ │                                                        ││
│ │ Costo Pianoforte (novembre): €50                      ││
│ │                                                        ││
│ │ DELTA: €50 - €30 = +€20                               ││
│ │ (Addebito aggiuntivo)                                  ││
│ │                                                        ││
│ │ NUOVO COSTO NOVEMBRE: €140 (anziché €170)             ││
│ │                                                        ││
│ └────────────────────────────────────────────────────────┘│
│                                                            │
│ Questa modifica creerà un nuovo pagamento di €20          │
│ nel riepilogo di novembre                                 │
│                                                            │
├────────────────────────────────────────────────────────────┤
│ [ANNULLA] [CONFERMA MODIFICA]                            │
└────────────────────────────────────────────────────────────┘
```

---

## 5. MODAL SOSPENSIONE CORSO

```
┌────────────────────────────────────────────────────────────┐
│ SOSPENDI CORSO                                    [X]     │
├────────────────────────────────────────────────────────────┤
│                                                            │
│ Stai sospendendo il corso di: BATTERIA                   │
│                                                            │
├────────────────────────────────────────────────────────────┤
│                                                            │
│ TIPO DI SOSPENSIONE:                                      │
│                                                            │
│ ⊙ TEMPORANEA (Pausa, poi riprendi)                       │
│ ⊙ DEFINITIVA (Abbandona il corso)                        │
│                                                            │
│ Se TEMPORANEA selezionata:                               │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ Sospendi da: [__________] ← data inizio              │ │
│ │ Riprendi da: [__________] ← data fine                │ │
│ │                                                       │ │
│ │ Motivo (opzionale):                                   │ │
│ │ [______________________________________]             │ │
│ │                                                       │ │
│ │ EFFETTO:                                              │ │
│ │ • Nel calendario: lezioni mostrate grigio (icona 🟡) │ │
│ │ • Nel pagamenti: corso NON compare nei mesi sospesi  │ │
│ │ • Lezioni successivamente riattivate automaticamente  │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                            │
│ Se DEFINITIVA selezionata:                               │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ ⚠️  Attenzione!                                       │ │
│ │                                                       │ │
│ │ La sospensione definitiva:                            │ │
│ │ • Elimina tutte le lezioni future                     │ │
│ │ • È IRREVERSIBILE                                     │ │
│ │ • Richiede conferma ulteriore                        │ │
│ │                                                       │ │
│ │ ☑ Confermo di voler cancellare il corso             │ │
│ │                                                       │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                            │
├────────────────────────────────────────────────────────────┤
│ [ANNULLA] [CONFERMA SOSPENSIONE]                         │
└────────────────────────────────────────────────────────────┘
```

---

## 6. PAGINA DATI ASSOCIAZIONE (Admin Only)

```
┌──────────────────────────────────────────────────────────────────┐
│ AMMINISTRAZIONE - DATI ASSOCIAZIONE             [=][-][X]       │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 🏢 DATI ASSOCIAZIONE                                             │
│                                                                   │
│ Questi dati verranno utilizzati in:                              │
│ • Ricevute di pagamento                                          │
│ • Email di comunicazione                                         │
│ • Stampe ufficiali                                               │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ SEZIONE: Informazioni Generali                                   │
│                                                                   │
│ Nome Scuola *                                                     │
│ [Scuola di Musica "MusicAll"________________________]            │
│                                                                   │
│ Indirizzo *                                                       │
│ [Via Roma 123_____________________________]                      │
│                                                                   │
│ CAP *                                                             │
│ [16100]                                                          │
│                                                                   │
│ Città *                                                           │
│ [Genova_____________________________]                            │
│                                                                   │
│ Provincia *                                                       │
│ [GE]                                                             │
│                                                                   │
│ SEZIONE: Contatti                                                │
│                                                                   │
│ Telefono Principale *                                             │
│ [+39 010 123456_____________________________]                    │
│                                                                   │
│ Telefono Secondario                                               │
│ [+39 010 654321_____________________________]                    │
│                                                                   │
│ Email Amministrativa *                                            │
│ [admin@musicall.it_____________________________]                 │
│                                                                   │
│ Email Pagamenti                                                   │
│ [pagamenti@musicall.it_____________________________]              │
│                                                                   │
│ SEZIONE: Dati Bancari (per bonifici)                             │
│                                                                   │
│ IBAN *                                                            │
│ [IT89 A012 3456 7890 1234 5678 901_____]                        │
│                                                                   │
│ Intestatario Conto *                                              │
│ [Scuola di Musica MusicAll_____________________________]          │
│                                                                   │
│ SEZIONE: Configurazione Costi                                    │
│                                                                   │
│ Costo Iscrizione Agosto-Febbraio *                               │
│ [€150_____]                                                      │
│                                                                   │
│ Costo Iscrizione Marzo-Luglio *                                  │
│ [€100_____]                                                      │
│                                                                   │
│ Sconto Familiare (%) *                                            │
│ [10_____]                                                        │
│                                                                   │
│ Numero Recuperi Garantiti *                                       │
│ [3_____]                                                         │
│                                                                   │
│ SEZIONE: Informazioni Aggiuntive                                 │
│                                                                   │
│ Partita IVA                                                       │
│ [12345678901_____________________________]                       │
│                                                                   │
│ Codice Fiscale                                                    │
│ [EXAMPLE1234567891_____________________________]                 │
│                                                                   │
│ Descrizione per Ricevute                                          │
│ [Lezioni di Musica e Strumenti_____________________________]    │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│ 📝 ULTIMA MODIFICA: 10 novembre 2026 da Admin1                   │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│ [ANNULLA] [SALVA MODIFICHE]                                     │
└──────────────────────────────────────────────────────────────────┘
```

---

## 7. PAGINA GESTIONE ALERT (Admin Only)

```
┌──────────────────────────────────────────────────────────────────┐
│ AMMINISTRAZIONE - ALERT CONFLITTI              [=][-][X]        │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ⚠️  ALERT SOVRAPPOSIZIONI ORARIE                                 │
│                                                                   │
│ Filtri: [MOSTRA TUTTO ▼] [SOLO NON VISTI ▼]                    │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ [NEW] 15 novembre 2026 - Marco Rossi                            │
│ ┌──────────────────────────────────────────────────────────────┐│
│ │ ⚠️  Sovrapposizione: Lunedì 17:00-18:00                       ││
│ │                                                                ││
│ │ Corso 1: Chitarra (Marco Rossi, Sala A)                      ││
│ │ Corso 2: Pianoforte (Anna Verdi, Sala C)                     ││
│ │                                                                ││
│ │ Azione: Socio ha confermato comunque l'iscrizione             ││
│ │                                                                ││
│ │ [CONTATTA SOCIO] [MARCHA COME VISTO]                         ││
│ │ [⚠️ DISABILITA ALERTA] (nasconde dalla lista)                 ││
│ │                                                                ││
│ └──────────────────────────────────────────────────────────────┘│
│                                                                   │
│ 12 novembre 2026 - Anna Verdi                                    │
│ ┌──────────────────────────────────────────────────────────────┐│
│ │ ⚠️  Sovrapposizione: Mercoledì 18:30-19:30                    ││
│ │                                                                ││
│ │ Corso 1: Flauto (Luigi Bianchi, Sala B)                      ││
│ │ Corso 2: Violino (Marco Rossi, Sala A)                       ││
│ │                                                                ││
│ │ Azione: Socio ha confermato comunque l'iscrizione             ││
│ │                                                                ││
│ │ [CONTATTA SOCIO] [MARCHA COME VISTO]                         ││
│ │ [⚠️ DISABILITA ALERTA] (nasconde dalla lista)                 ││
│ │                                                                ││
│ └──────────────────────────────────────────────────────────────┘│
│                                                                   │
│ 01 novembre 2026 - Luca Bianchi                                  │
│ ┌──────────────────────────────────────────────────────────────┐│
│ │ ⚠️  Sovrapposizione: Venerdì 16:00-17:00                      ││
│ │                                                                ││
│ │ Corso 1: Tromba (Anna Verdi, Sala D)                         ││
│ │ Corso 2: Trombone (Luigi Bianchi, Sala E)                    ││
│ │                                                                ││
│ │ Azione: Socio ha confermato comunque l'iscrizione             ││
│ │ Stato: ✓ VISTO                                                ││
│ │                                                                ││
│ │ [CONTATTA SOCIO] [MARCHA COME NON VISTO]                     ││
│ │ [⚠️ DISABILITA ALERTA] (nasconde dalla lista)                 ││
│ │                                                                ││
│ └──────────────────────────────────────────────────────────────┘│
│                                                                   │
│ 📊 STATISTICHE ALERT:                                            │
│ • Totali: 42                                                      │
│ • Non visti: 3                                                    │
│ • Visti: 39                                                       │
│ • Disabilitati: 0                                                 │
│                                                                   │
│ ℹ Nota: Gli alert non vengono mai cancellati, rimangono         │
│   come storico. Possono solo essere marcati come visti o        │
│   disabilitati (nascosti dalla visualizzazione).                 │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 8. PAGINA BATCH MANAGEMENT (Admin Only)

```
┌──────────────────────────────────────────────────────────────────┐
│ AMMINISTRAZIONE - BATCH EMAIL                 [=][-][X]         │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 📧 GESTIONE BATCH EMAIL                                          │
│                                                                   │
│ Batch Email di Promemoria Pagamento                              │
│ (Esecuzione automatica: ogni 26 del mese alle 08:00)            │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ULTIMA ESECUZIONE:                                               │
│ • Data: 26 ottobre 2026 - 08:15                                 │
│ • Email inviate: 47                                              │
│ • Email fallite: 1                                               │
│ • Stato: ✅ SUCCESSO (parziale)                                 │
│ • Log: "Email fallita per socio@fallito.it - SMTP error"        │
│                                                                   │
│ [VISUALIZZA LOG COMPLETO]                                       │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ⚠️  Accanto sarà il 26 novembre: BATCH PROGRAMMATO              │
│ • Prossima esecuzione: 26 novembre 2026 - 08:00                 │
│ • Email previste: ~50 soci                                       │
│                                                                   │
│ [ESEGUI MANUALMENTE ORA]                                        │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ CRONOLOGIA ESECUZIONI BATCH:                                     │
│                                                                   │
│ Data Esecuzione | Inviate | Fallite | Stato | Dettagli         │
│ ─────────────────────────────────────────────────────────────  │
│ 26 Ott 2026    │ 47      │ 1       │ ✅    │ [LOG]            │
│ 26 Set 2026    │ 45      │ 0       │ ✅    │ [LOG]            │
│ 26 Ago 2026    │ 42      │ 0       │ ✅    │ [LOG]            │
│ 26 Lug 2026    │ 40      │ 2       │ ⚠️    │ [LOG]            │
│ 26 Giu 2026    │ 38      │ 0       │ ✅    │ [LOG]            │
│ 26 Mag 2026    │ 35      │ 0       │ ✅    │ [LOG]            │
│                                                                   │
│ [PAGINA PRECEDENTE] [1] [2] [3] [PAGINA SUCCESSIVA]            │
│                                                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ℹ INFO:                                                          │
│ • Il batch è completamente automatico                            │
│ • Non richiede intervento manuale                                │
│ • Se fallisce, un alert viene inviato all'admin                 │
│ • Puoi eseguire manualmente per test                            │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## Note di Design UI/UX

### Colori e Iconografia
- ✅ Verde: Stato positivo (PAGATO, ATTIVO)
- ⏳ Blu: In elaborazione (IN_ATTESA)
- 🟡 Giallo: Stato intermedio (SOSPESO, PARZIALE)
- ❌ Rosso: Errore o disabilitato
- ⚠️ Arancione: Attenzione richiesta

### Layout Responsive
- Desktop: Layout full-width con sidebar
- Tablet: Menu collassato, grid 2 colonne
- Mobile: Stack verticale, menu drawer

### Accessibilità
- Tutti i form con label associate
- Codice di colore + icone (non solo colore)
- Font size minimo 14px
- Contrast ratio 4.5:1 WCAG AA

### Interattività
- Validazione real-time nei form
- Toast notifications per azioni
- Conferme prima di azioni critiche
- Loading states durante operazioni async

---

## Prototipo Interattivo

Questi wireframe possono essere convertiti in prototipo interattivo usando:
- **Figma**: Per design e prototipazione
- **Adobe XD**: Per mockup avanzati
- **Bootstrap 5**: Per implementazione HTML/CSS

**Raccomandazione**: Dopo implementazione Fase 1, creare prototipo Figma per validazione con stakeholder.
