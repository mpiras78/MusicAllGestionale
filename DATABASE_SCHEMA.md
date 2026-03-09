# MusicAll - Schema Database

Documentazione completa dello schema del database SQLite per il sistema di gestione della scuola di musica MusicAll.

---

## Indice

1. [Tabelle Principali](#tabelle-principali)
2. [Tabelle Sistema Eventi e Pagamenti](#tabelle-sistema-eventi-e-pagamenti)
3. [Relazioni e Vincoli](#relazioni-e-vincoli)
4. [Viste](#viste)
5. [Indici](#indici)

---

## Tabelle Principali

### 1. **users**
Gestione utenti del sistema (admin, docenti, segreteria)

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco utente |
| username | TEXT | UNIQUE NOT NULL | Nome utente per login |
| password | TEXT | NOT NULL | Password hashata (bcrypt) |
| email | TEXT | - | Email utente |
| role | TEXT | DEFAULT 'segreteria', CHECK | Ruolo: 'admin', 'docente', 'segreteria' |
| created_at | DATETIME | DEFAULT now | Data creazione |
| last_login | DATETIME | - | Ultimo accesso |
| active | INTEGER | DEFAULT 1 | Attivo (1) o disattivato (0) |

**Indici:** username (UNIQUE)

---

### 2. **docenti**
Anagrafica docenti della scuola

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco docente |
| cognome | TEXT | NOT NULL | Cognome |
| nome | TEXT | NOT NULL | Nome |
| email | TEXT | - | Email contatto |
| telefono | TEXT | - | Numero telefono |
| specializzazioni | TEXT | - | Strumenti/materie |
| note | TEXT | - | Note generiche |
| user_id | INTEGER | FK → users(id) | Collegamento account utente |
| attivo | INTEGER | DEFAULT 1 | Attivo (1) o archiviato (0) |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Indici:** 
- idx_docenti_cognome_nome (cognome, nome)
- FK user_id → users(id) ON DELETE SET NULL

---

### 3. **soci**
Anagrafica soci iscritti

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco socio |
| cognome | TEXT | NOT NULL | Cognome |
| nome | TEXT | NOT NULL | Nome |
| email | TEXT | - | Email contatto |
| telefono | TEXT | - | Numero telefono |
| data_nascita | DATE | - | Data di nascita |
| indirizzo | TEXT | - | Indirizzo residenza |
| note | TEXT | - | Note generiche |
| attivo | INTEGER | DEFAULT 1 | Attivo (1) o archiviato (0) |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Indici:**
- idx_soci_cognome_nome (cognome, nome)
- idx_soci_email (email)

---

### 4. **aule**
Sale/aule disponibili per lezioni

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco aula |
| nome | TEXT | NOT NULL UNIQUE | Nome aula |
| descrizione | TEXT | - | Descrizione/caratteristiche |
| capienza | INTEGER | - | Numero max persone |
| attrezzature | TEXT | - | Strumenti/attrezzature disponibili |
| attiva | INTEGER | DEFAULT 1 | Attiva (1) o disabilitata (0) |
| ordine_visualizzazione | INTEGER | DEFAULT 0 | Ordine visualizzazione calendario |

**Indici:** idx_aule_ordine (ordine_visualizzazione)

---

### 5. **materie**
Materie/corsi insegnati

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco materia |
| nome | TEXT | NOT NULL | Nome materia |
| categoria | TEXT | DEFAULT 'strumento', CHECK | Categoria: 'strumento', 'canto', 'teoria', 'insieme', 'laboratorio', 'custom' |
| descrizione | TEXT | - | Descrizione materia |
| durata_standard | INTEGER | DEFAULT 45 | Durata standard in minuti |
| attiva | INTEGER | DEFAULT 1 | Attiva (1) o disabilitata (0) |

---

### 6. **docenti_materie**
Relazione many-to-many tra docenti e materie insegnate

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| docente_id | INTEGER | FK → docenti(id), NOT NULL | Riferimento docente |
| materia_id | INTEGER | FK → materie(id), NOT NULL | Riferimento materia |
| livello | TEXT | CHECK | Livello: 'principiante', 'intermedio', 'avanzato', 'tutti' |
| note | TEXT | - | Note aggiuntive |

**Vincoli:** UNIQUE(docente_id, materia_id)
**Indici:**
- idx_docenti_materie_docente (docente_id)
- idx_docenti_materie_materia (materia_id)

---

### 7. **lezioni**
Lezioni regolari programmate (ricorrenti settimanalmente)

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco lezione |
| socio_id | INTEGER | FK → soci(id), NOT NULL | Socio |
| docente_id | INTEGER | FK → docenti(id), NOT NULL | Docente |
| materia_id | INTEGER | FK → materie(id), NOT NULL | Materia |
| aula_id | INTEGER | FK → aule(id), NOT NULL | Aula |
| giorno_settimana | TEXT | NOT NULL, CHECK | 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica' |
| ora_inizio | TIME | NOT NULL | Ora inizio (HH:MM:SS) |
| ora_fine | TIME | NOT NULL | Ora fine (HH:MM:SS) |
| tipo | TEXT | DEFAULT 'regolare', CHECK | 'regolare', 'custom', 'recupero', 'laboratorio' |
| attiva | INTEGER | DEFAULT 1 | Attiva (1) o sospesa (0) |
| data_inizio | DATE | - | Data inizio validità |
| data_fine | DATE | - | Data fine validità |
| note | TEXT | - | Note |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Indici:**
- idx_lezioni_giorno (giorno_settimana)
- idx_lezioni_orario (ora_inizio, ora_fine)
- idx_lezioni_docente (docente_id)
- idx_lezioni_socio (socio_id)

---

### 8. **assenze**
Registrazione assenze alle lezioni

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco assenza |
| lezione_id | INTEGER | FK → lezioni(id) | Lezione regolare di riferimento |
| socio_id | INTEGER | FK → soci(id), NOT NULL | Socio assente |
| docente_id | INTEGER | FK → docenti(id), NOT NULL | Docente della lezione |
| data_assenza | DATE | NOT NULL | Data dell'assenza |
| tipo | TEXT | NOT NULL, CHECK | 'socio' o 'docente' |
| motivo | TEXT | - | Motivazione assenza |
| da_recuperare | INTEGER | DEFAULT 1 | Richiede recupero (1/0) |
| recuperata | INTEGER | DEFAULT 0 | Già recuperata (1/0) |
| data_recupero | DATE | - | Data recupero effettuato |
| recupero_lezione_id | INTEGER | FK → lezioni(id) | Lezione recupero associata |
| note | TEXT | - | Note aggiuntive |
| created_at | DATETIME | DEFAULT now | Data registrazione |
| created_by | INTEGER | FK → users(id) | Utente che ha registrato |

**Indici:**
- idx_assenze_data (data_assenza)
- idx_assenze_recupero (recuperata, data_recupero)

---

### 9. **lezioni_custom**
Lezioni una tantum (non ricorrenti)

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| socio_id | INTEGER | FK → soci(id), NOT NULL | Socio |
| docente_id | INTEGER | FK → docenti(id), NOT NULL | Docente |
| materia_id | INTEGER | FK → materie(id), NOT NULL | Materia |
| data_lezione | DATE | NOT NULL | Data specifica lezione |
| ora_inizio | TIME | NOT NULL | Ora inizio |
| ora_fine | TIME | NOT NULL | Ora fine |
| aula_id | INTEGER | FK → aule(id) | Aula (opzionale) |
| numero_lezione | INTEGER | - | Es: 3 di 10 |
| totale_lezioni | INTEGER | - | Totale lezioni pacchetto |
| costo | REAL | - | Costo lezione |
| pagata | INTEGER | DEFAULT 0 | Pagata (1/0) |
| note | TEXT | - | Note |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Indici:**
- idx_lezioni_custom_data (data_lezione)
- idx_lezioni_custom_socio (socio_id)
- idx_lezioni_custom_docente (docente_id)

---

## Tabelle Sistema Eventi e Pagamenti

### 10. **tipologie_evento**
Tipologie di eventi gestiti dal calendario

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| categoria | TEXT | NOT NULL, CHECK | 'lezione' o 'prenotazione' |
| codice | TEXT | UNIQUE NOT NULL | Codice univoco (es: PREN_SALA_SOCI) |
| nome | TEXT | NOT NULL | Nome visualizzato |
| descrizione | TEXT | - | Descrizione tipologia |
| colore_bg | TEXT | DEFAULT '#ffffff' | Colore sfondo (hex) |
| colore_border | TEXT | DEFAULT '#000000' | Colore bordo (hex) |
| icona | TEXT | - | Classe icona Bootstrap |
| attiva | INTEGER | DEFAULT 1 | Attiva (1) o disabilitata (0) |
| ordine_visualizzazione | INTEGER | DEFAULT 0 | Ordine visualizzazione |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Codici Predefiniti:**
- **Lezioni:** LEZ_REGOLARE, LEZ_CUSTOM, LEZ_LABORATORIO, LEZ_RECUPERO
- **Prenotazioni:** PREN_SALA_SOCI, PREN_DOCENTE, PREN_ESTERNO

**Indici:**
- idx_tipologie_categoria (categoria, attiva)
- idx_tipologie_codice (codice)

---

### 11. **soci_occasionali**
Soci esterni/occasionali per prenotazioni

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| nome | TEXT | NOT NULL | Nome |
| cognome | TEXT | NOT NULL | Cognome |
| email | TEXT | - | Email |
| telefono | TEXT | - | Telefono |
| tipo | TEXT | DEFAULT 'privato', CHECK | 'privato', 'band', 'associazione', 'altro' |
| partita_iva | TEXT | - | P.IVA |
| codice_fiscale | TEXT | - | Codice Fiscale |
| note | TEXT | - | Note |
| attivo | INTEGER | DEFAULT 1 | Attivo (1/0) |
| created_at | DATETIME | DEFAULT now | Data creazione |
| updated_at | DATETIME | - | Ultimo aggiornamento |

**Indici:**
- idx_soci_occasionali_email (email)
- idx_soci_occasionali_telefono (telefono)
- idx_soci_occasionali_nome (cognome, nome)

---

### 12. **iscrizioni**
Iscrizioni mensili soci

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| socio_id | INTEGER | FK → soci(id), NOT NULL | Socio iscritto |
| mese | INTEGER | NOT NULL, CHECK (1-12) | Mese iscrizione |
| anno | INTEGER | NOT NULL | Anno iscrizione |
| data_inizio | DATE | NOT NULL | Data inizio validità |
| data_fine | DATE | NOT NULL | Data fine validità |
| stato | TEXT | DEFAULT 'attiva', CHECK | 'attiva', 'sospesa', 'conclusa' |
| note | TEXT | - | Note |
| created_at | DATETIME | DEFAULT now | Data creazione |
| created_by | INTEGER | FK → users(id) | Utente che ha creato |

**Vincoli:** UNIQUE(socio_id, mese, anno)
**Indici:**
- idx_iscrizioni_socio (socio_id)
- idx_iscrizioni_periodo (anno, mese)
- idx_iscrizioni_stato (stato)

---

### 13. **eventi_calendario** ⭐
Tabella unificata per tutti gli eventi (lezioni + prenotazioni)

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco evento |
| tipologia_id | INTEGER | FK → tipologie_evento(id), NOT NULL | Tipologia evento |
| ricorrente | INTEGER | DEFAULT 0 | Ricorrente (1) o singolo (0) |
| giorno_settimana | TEXT | CHECK | Giorno se ricorrente |
| data_evento | DATE | - | Data specifica se singolo |
| data_inizio | DATE | - | Data inizio validità |
| data_fine | DATE | - | Data fine validità |
| ora_inizio | TIME | NOT NULL | Ora inizio |
| ora_fine | TIME | NOT NULL | Ora fine |
| aula_id | INTEGER | FK → aule(id), NOT NULL | Aula |
| docente_id | INTEGER | FK → docenti(id) | Docente (per lezioni) |
| materia_id | INTEGER | FK → materie(id) | Materia (per lezioni) |
| socio_id | INTEGER | FK → soci(id) | Socio (lezioni o prenotazioni) |
| socio_occasionale_id | INTEGER | FK → soci_occasionali(id) | Socio esterno (prenotazioni) |
| iscrizione_id | INTEGER | FK → iscrizioni(id) | Iscrizione associata |
| titolo | TEXT | - | Titolo evento |
| descrizione | TEXT | - | Descrizione |
| note | TEXT | - | Note |
| attivo | INTEGER | DEFAULT 1 | Attivo (1/0) |
| confermato | INTEGER | DEFAULT 1 | Confermato (1/0) |
| created_at | DATETIME | DEFAULT now | Data creazione |
| created_by | INTEGER | FK → users(id) | Utente creatore |
| updated_at | DATETIME | - | Ultimo aggiornamento |
| updated_by | INTEGER | FK → users(id) | Utente ultimo update |

**Vincoli CHECK:**
- Se ricorrente=0: data_evento NOT NULL, giorno_settimana NULL
- Se ricorrente=1: data_evento NULL, giorno_settimana NOT NULL
- Uno solo tra socio_id e socio_occasionale_id può essere valorizzato

**Indici:**
- idx_eventi_tipologia (tipologia_id, attivo)
- idx_eventi_data (data_evento)
- idx_eventi_giorno (giorno_settimana, ricorrente)
- idx_eventi_aula (aula_id)
- idx_eventi_docente (docente_id)
- idx_eventi_socio (socio_id)
- idx_eventi_socio (socio_occasionale_id)
- idx_eventi_iscrizione (iscrizione_id)
- idx_eventi_periodo (data_inizio, data_fine)

---

### 14. **listini_prezzi**
Listini prezzi per tipologie evento

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| tipologia_id | INTEGER | FK → tipologie_evento(id), NOT NULL | Tipologia evento |
| destinatario | TEXT | NOT NULL, CHECK | 'socio_iscritto', 'docente', 'socio_occasionale', 'altro' |
| prezzo_orario | REAL | - | Prezzo per ora (€) |
| prezzo_forfait | REAL | - | Prezzo forfait (€) |
| data_inizio_validita | DATE | NOT NULL | Data inizio validità |
| data_fine_validita | DATE | - | Data fine validità |
| attivo | INTEGER | DEFAULT 1 | Attivo (1/0) |
| note | TEXT | - | Note |
| created_at | DATETIME | DEFAULT now | Data creazione |

**Tariffe Predefinite 2026:**
- Soci iscritti: €0/ora (gratuito)
- Docenti: €15/ora
- Esterni: €30/ora

**Indici:**
- idx_listini_tipologia (tipologia_id, attivo)
- idx_listini_destinatario (destinatario)
- idx_listini_validita (data_inizio_validita, data_fine_validita)

---

### 15. **pagamenti**
Gestione pagamenti

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| tipo | TEXT | NOT NULL, CHECK | 'iscrizione_mensile', 'prenotazione_sala', 'altro' |
| iscrizione_id | INTEGER | FK → iscrizioni(id) | Riferimento iscrizione |
| evento_id | INTEGER | FK → eventi_calendario(id) | Riferimento evento |
| socio_id | INTEGER | FK → soci(id) | Pagante (se socio) |
| socio_occasionale_id | INTEGER | FK → soci_occasionali(id) | Pagante (se esterno) |
| importo_totale | REAL | NOT NULL | Importo totale (€) |
| importo_pagato | REAL | DEFAULT 0 | Importo già pagato (€) |
| importo_residuo | REAL | - | Importo ancora da pagare (€) |
| stato | TEXT | DEFAULT 'da_pagare', CHECK | 'da_pagare', 'parzialmente_pagato', 'pagato', 'annullato' |
| data_emissione | DATE | NOT NULL, DEFAULT today | Data emissione |
| data_scadenza | DATE | - | Data scadenza |
| data_pagamento | DATE | - | Data pagamento effettivo |
| modalita_pagamento | TEXT | CHECK | 'contanti', 'bonifico', 'carta', 'paypal', 'altro' |
| numero_fattura | TEXT | - | Numero fattura |
| numero_ricevuta | TEXT | - | Numero ricevuta |
| note | TEXT | - | Note |
| created_at | DATETIME | DEFAULT now | Data creazione |
| created_by | INTEGER | FK → users(id) | Utente creatore |
| updated_at | DATETIME | - | Ultimo aggiornamento |

**Vincoli CHECK:**
- Almeno uno tra iscrizione_id ed evento_id deve essere valorizzato
- Uno solo tra socio_id e socio_occasionale_id può essere valorizzato

**Indici:**
- idx_pagamenti_tipo (tipo, stato)
- idx_pagamenti_iscrizione (iscrizione_id)
- idx_pagamenti_evento (evento_id)
- idx_pagamenti_socio (socio_id)
- idx_pagamenti_socio (socio_occasionale_id)
- idx_pagamenti_scadenza (data_scadenza, stato)

---

### 16. **iscrizioni_dettagli**
Dettagli lezioni incluse in un'iscrizione mensile

| Campo | Tipo | Vincoli | Descrizione |
|-------|------|---------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | ID univoco |
| iscrizione_id | INTEGER | FK → iscrizioni(id), NOT NULL | Iscrizione |
| evento_id | INTEGER | FK → eventi_calendario(id), NOT NULL | Evento/lezione |
| materia_id | INTEGER | FK → materie(id), NOT NULL | Materia |
| docente_id | INTEGER | FK → docenti(id), NOT NULL | Docente |
| costo_mensile | REAL | - | Costo mensile (€) |
| note | TEXT | - | Note |

**Vincoli:** UNIQUE(iscrizione_id, evento_id)
**Indici:**
- idx_iscrizioni_dettagli_iscrizione (iscrizione_id)
- idx_iscrizioni_dettagli_evento (evento_id)

---

## Viste

### v_calendario_unificato
Vista per visualizzazione completa calendario con tutti i dati join

**Colonne principali:**
- Dati evento (id, date, orari, tipo, tipologia)
- Dati aula
- Dati docente
- Dati partecipante (socio o esterno)
- Dati materia
- Stato iscrizione
- Stato pagamento

**Utilizzo:** Query rapide per rendering calendario senza join multipli

---

## Relazioni e Vincoli

### Relazioni Principali

```
users 1──→N docenti (user_id)
users 1──→N activity_log (user_id)

docenti N──→M materie (via docenti_materie)
docenti 1──→N lezioni (docente_id)
docenti 1──→N eventi_calendario (docente_id)

soci 1──→N lezioni (socio_id)
soci 1──→N assenze (socio_id)
soci 1──→N eventi_calendario (socio_id)
soci 1──→N iscrizioni (socio_id)
soci 1──→N pagamenti (socio_id)

aule 1──→N lezioni (aula_id)
aule 1──→N eventi_calendario (aula_id)

materie 1──→N lezioni (materia_id)
materie 1──→N eventi_calendario (materia_id)

lezioni 1──→N assenze (lezione_id)

tipologie_evento 1──→N eventi_calendario (tipologia_id)
tipologie_evento 1──→N listini_prezzi (tipologia_id)

soci_occasionali 1──→N eventi_calendario (socio_occasionale_id)
soci_occasionali 1──→N pagamenti (socio_occasionale_id)

iscrizioni 1──→N eventi_calendario (iscrizione_id)
iscrizioni 1──→N pagamenti (iscrizione_id)
iscrizioni 1──→N iscrizioni_dettagli (iscrizione_id)

eventi_calendario 1──→N pagamenti (evento_id)
eventi_calendario 1──→N iscrizioni_dettagli (evento_id)
```

---

## Indici

### Indici per Performance

Tutti gli indici sono stati creati per ottimizzare le query più frequenti:

1. **Ricerche per nome/cognome** → idx su cognome+nome
2. **Filtri per giorno settimana** → idx su giorno_settimana
3. **Ricerche per orario** → idx su ora_inizio+ora_fine
4. **Join frequenti** → idx su tutte le FK
5. **Ricerche per data** → idx su date principali
6. **Filtri per stato** → idx su campi stato/attivo

---

## Note Implementative

### Convenzioni Nomi
- Tabelle: plurale minuscolo
- Campi: snake_case
- FK: nome_tabella_id
- Booleani: INTEGER (0/1)
- Date: formato 'YYYY-MM-DD'
- Time: formato 'HH:MM:SS'

### Soft Delete
Quasi tutte le entità usano soft delete tramite campo `attivo/attiva` invece di DELETE fisico.

### Audit Trail
Campi comuni per tracciamento:
- created_at: data creazione
- created_by: utente creatore
- updated_at: data ultimo update
- updated_by: utente ultimo update

---

**Versione Schema:** 2.0.0  
**Ultimo Aggiornamento:** 13/02/2026  
**Database Engine:** SQLite 3.x