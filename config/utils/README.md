# 🛠️ MusicAll - Utility Database Scripts

Script bash per gestione database SQLite del progetto MusicAll.

## 📋 Prerequisiti

- **Git Bash** (Windows) o terminale bash (Linux/Mac)
- **PHP 8.4.5** installato in `/c/portable/php-8.4.5/`
- Cartella progetto: `c:/git/ct-poc/MusicAll`

## 🚀 Quick Start

```bash
# Import completo database (schema + docenti + allievi)
bash config/utils/db-import-all.sh

# Avvia server PHP
php -S localhost:8000

# Login con:
# Username: admin
# Password: admin123
```

## 📁 Script Disponibili

### 🚀 Gestione Applicativo

#### `app-start.sh` - Avvio Server
**Cosa fa:**
- ✅ Avvia server PHP built-in
- ✅ Verifica porta disponibile
- ✅ Controlla presenza database
- ✅ Mostra URL e credenziali

**Uso:**
```bash
# Avvio standard (porta 8000)
bash config/utils/app-start.sh

# Avvio su porta personalizzata
bash config/utils/app-start.sh 8080
```

---

#### `app-stop.sh` - Stop Server
**Cosa fa:**
- ✅ Trova processi PHP sulla porta
- ✅ Termina processi
- ✅ Cleanup automatico

**Uso:**
```bash
bash config/utils/app-stop.sh
```

---

#### `user-activate.sh` - Attivazione Utente
**Cosa fa:**
- ✅ Genera token attivazione (24h)
- ✅ Mostra link attivazione

**Uso:**
```bash
bash config/utils/user-activate.sh mario.rossi
```

---

#### `user-reset-password.sh` - Reset Password
**Cosa fa:**
- ✅ Reimposta password utente
- ✅ Attiva utente se necessario

**Uso:**
```bash
bash config/utils/user-reset-password.sh mario.rossi
```

---

### 💾 Gestione Database

### 1️⃣ `db-import-all.sh` - Import Completo

**Cosa fa:**
- ✅ Backup automatico DB esistente
- ✅ Reset e ricreazione database
- ✅ Import schema (13 tabelle)
- ✅ Import 25 docenti + 30+ relazioni materie
- ✅ Import 230+ allievi
- ✅ Statistiche finali

**Uso:**
```bash
bash config/utils/db-import-all.sh
```

**Output:**
```
╔════════════════════════════════════════════╗
║   MusicAll - Import Completo Database     ║
╚════════════════════════════════════════════╝

[STEP 1/4] Backup database esistente
[STEP 2/4] Reset database e import schema
[STEP 3/4] Import docenti e relazioni materie
[STEP 4/4] Import allievi

📊 Statistiche Database:
  👥 Utenti:              1 (admin pronto)
  👨‍🏫 Docenti:             25
  🔗 Relazioni D-M:       30+
  🎓 Allievi:             230+
  🏫 Aule:                6
  📚 Materie:             18
  ⏰ Slot Orari:          17
  💾 Dimensione DB:       XXX KB
```

---

### 2️⃣ `db-backup.sh` - Backup Database

**Cosa fa:**
- Crea backup versionato con timestamp `YYYYMMDDHHMMSS`
- Salva in `database/backups/`
- Mostra ultimi 5 backup
- Avvisa se troppi backup (>20)

**Uso:**
```bash
bash config/utils/db-backup.sh
```

**Nome file backup:**
```
musicall_backup_20260204012530.sqlite
                 ^^^^^^^^^^^^^^^^
                 Anno Mese Giorno Ora Min Sec
```

**Output:**
```
============================================
  MusicAll - Database Backup
============================================

[INFO] Database da backuppare: musicall.sqlite (160 KB)
[INFO] Creazione backup...
[SUCCESS] Backup creato: musicall_backup_20260204012530.sqlite

[INFO] Ultimi 5 backup disponibili:
  musicall_backup_20260204012530.sqlite (160K)
  musicall_backup_20260204010000.sqlite (158K)
  ...

[INFO] Totale backup: 5
```

---

### 3️⃣ `db-reset.sh` - Reset Database

**Cosa fa:**
- Backup automatico (opzionale)
- Elimina database esistente
- Ricrea da schema SQLite
- Importa solo dati iniziali (aule, materie, slot, admin)

**Uso:**
```bash
bash config/utils/db-reset.sh
```

**Interattivo:**
```
[WARNING] Database esistente trovato!

Vuoi creare un backup prima di resettare? (Y/n): y
[INFO] Creazione backup automatico...
```

---

### 4️⃣ `db-restore.sh` - Ripristina Backup

**Cosa fa:**
- Mostra lista backup disponibili
- Selezione interattiva
- Backup automatico DB corrente prima del restore
- Ripristino backup selezionato

**Uso:**
```bash
bash config/utils/db-restore.sh
```

**Interattivo:**
```
[INFO] Backup disponibili:

  [1] musicall_backup_20260204012530.sqlite (160K) - 2026-02-04 01:25:30
  [2] musicall_backup_20260204010000.sqlite (158K) - 2026-02-04 01:00:00
  [3] musicall_backup_20260203230000.sqlite (155K) - 2026-02-03 23:00:00

Seleziona il backup da ripristinare [1-3]: 1

[WARNING] ATTENZIONE: Il database corrente verrà SOVRASCRITTO!
Vuoi continuare? (y/N): y
```

---

### 5️⃣ `db-cleanup-backups.sh` - Pulizia Backup

**Cosa fa:**
- Mantiene ultimi N backup (default: 10)
- Elimina backup più vecchi
- Conferma interattiva
- Mostra spazio liberato

**Uso:**
```bash
bash config/utils/db-cleanup-backups.sh
```

**Configurazione:**
Modifica `KEEP_LAST` nello script per cambiare numero backup da mantenere:
```bash
KEEP_LAST=10  # Cambia questo valore
```

**Output:**
```
[INFO] Backup totali: 25
[INFO] Backup da mantenere: 10
[WARNING] Backup da eliminare: 15

[INFO] File che verranno eliminati:
  - musicall_backup_20260101120000.sqlite (155K)
  - musicall_backup_20260102130000.sqlite (156K)
  ...

Confermi l'eliminazione? (y/N): y
```

---

## 📂 Struttura Directory

```
MusicAll/
├── config/
│   └── utils/                    # ← Script utility qui
│       ├── README.md             # Questa guida
│       ├── db-backup.sh
│       ├── db-reset.sh
│       ├── db-import-all.sh
│       ├── db-restore.sh
│       └── db-cleanup-backups.sh
├── database/
│   ├── musicall.sqlite           # Database principale
│   ├── schema_sqlite.sql         # Schema SQLite
│   ├── schema.sql                # Schema MySQL
│   ├── insert_docenti.sql        # 25 docenti + relazioni
│   ├── insert_allievi.sql        # 230+ allievi
│   └── backups/                  # ← Backup automatici qui
│       ├── musicall_backup_20260204012530.sqlite
│       ├── musicall_backup_20260204010000.sqlite
│       └── ...
```

---

## 🔧 Configurazione

### Percorsi Script

Se il tuo PHP è installato altrove, modifica il percorso negli script:

```bash
# Cambia questa riga in ogni script:
PHP_BIN="/c/portable/php-8.4.5/php.exe"

# Con il tuo percorso:
PHP_BIN="/c/tuopercorso/php.exe"
```

### Numero Backup da Mantenere

In `db-cleanup-backups.sh`:
```bash
KEEP_LAST=10  # Mantieni ultimi 10 backup
```

---

## 🎯 Workflow Consigliato

### Sviluppo Normale
```bash
# Backup prima di modifiche importanti
bash config/utils/db-backup.sh

# Lavora sul database...

# Se qualcosa va storto:
bash config/utils/db-restore.sh
```

### Reset Completo
```bash
# Quando vuoi ricominciare da capo
bash config/utils/db-import-all.sh
```

### Manutenzione Settimanale
```bash
# Pulisci backup vecchi
bash config/utils/db-cleanup-backups.sh
```

---

## ⚠️ Note Importanti

1. **Backup Automatici**: `db-import-all.sh` e `db-restore.sh` creano sempre un backup prima di modifiche distruttive

2. **Versionamento Backup**: Il timestamp `YYYYMMDDHHMMSS` garantisce ordinamento cronologico corretto

3. **Conferme Interattive**: Tutti gli script che modificano/eliminano dati richiedono conferma esplicita

4. **Colori Output**: Gli script usano colori ANSI:
   - 🔵 **[INFO]** = Blu - Informazioni
   - 🟢 **[SUCCESS]** = Verde - Operazione riuscita
   - 🟡 **[WARNING]** = Giallo - Attenzione
   - 🔴 **[ERROR]** = Rosso - Errore

5. **Exit Codes**:
   - `0` = Successo
   - `1` = Errore

---

## 🐛 Troubleshooting

### Script non eseguibile
```bash
# Rendi eseguibili tutti gli script
chmod +x config/utils/*.sh
```

### PHP non trovato
```bash
# Verifica percorso PHP
which php
# O su Windows Git Bash:
/c/portable/php-8.4.5/php.exe --version

# Aggiorna PHP_BIN negli script
```

### Database locked
```bash
# Chiudi tutte le connessioni al database
# Poi riprova
```

### Permessi negati
```bash
# Su Linux/Mac potrebbe servire sudo
sudo bash config/utils/db-backup.sh
```

---

## 📞 Supporto

Per problemi o domande:
- Controlla i log degli script (output dettagliato)
- Verifica permessi file/cartelle
- Assicurati che PHP sia accessibile

---

## 🎓 Credenziali Admin

Dopo ogni import completo:
```
Username: admin
Password: admin123
```

**⚠️ IMPORTANTE:** Cambia la password admin in produzione!

---

## 📝 Changelog

### v1.0 - 2026-02-04
- ✅ Creazione script iniziali
- ✅ Backup versionati con timestamp
- ✅ Import completo automatizzato
- ✅ Restore interattivo
- ✅ Cleanup backup automatico
- ✅ Documentazione completa

---

**Buon lavoro con MusicAll! 🎸🎹🎤**