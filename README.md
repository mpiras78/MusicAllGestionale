# MusicAll - Sistema di Gestione Scuola di Musica

**Versione:** 2.2.1 (6 Marzo 2026)

Sistema completo per la gestione di scuole di musica con calendario settimanale, gestione allievi, docenti, lezioni, assenze e iscrizioni accademiche.

## 🎵 Caratteristiche Principali

- **Calendario Settimanale Visuale**: Interfaccia simile a un foglio Excel con visualizzazione per aule e orari
- **Gestione Iscrizioni**: Filtro per mese accademico (Settembre-Luglio) con gestione automatica delle date
- **Gestione Completa**: Allievi, Docenti, Lezioni, Aule, Materie, Iscrizioni
- **Sistema Assenze/Recuperi**: Tracciamento assenze e programmazione recuperi
- **Report e Statistiche**: Dashboard con statistiche in tempo reale
- **Autenticazione Sicura**: Sistema di login con ruoli utente
- **Sistema Logging**: Log dettagliati per debugging e auditing
- **Responsive Design**: Interfaccia Bootstrap 5 ottimizzata per tutti i dispositivi

## 📋 Requisiti di Sistema

- **Web Server**: Apache 2.4+ o Nginx
- **PHP**: 7.4 o superiore (consigliato PHP 8.0+)
- **Database**: MySQL 5.7+, MariaDB 10.3+, SQLite 3+, o PostgreSQL 12+
- **Composer**: Per gestione dipendenze
- **Estensioni PHP richieste**:
  - PDO
  - pdo_mysql (o pdo_sqlite, pdo_pgsql)
  - mbstring
  - session
  - json

## 🚀 Installazione

### 1. Installazione Dipendenze (Composer)

Il progetto usa **Eloquent ORM** per supportare MySQL, SQLite, PostgreSQL e altri database.

```bash
# Entra nella directory del progetto
cd musicall

# Installa le dipendenze
composer install
```

**Cosa include:**
- `illuminate/database`: Eloquent ORM
- `illuminate/events`: Sistema eventi
- PHPUnit per testing (opzionale)

### 2. Download e Posizionamento File

```bash
# Clona o scarica il progetto nella directory del web server
cd /var/www/html  # Linux/Mac
```
# oppure
cd C:\xampp\htdocs  # Windows con XAMPP

# Copia i file del progetto
cp -r musicall ./
```

### 2. Configurazione Database

**Crea il database:**

```sql
CREATE DATABASE musicall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Importa lo schema:**

```bash
mysql -u root -p musicall < database/schema.sql
```

Oppure tramite phpMyAdmin:
1. Accedi a phpMyAdmin
2. Seleziona il database `musicall`
3. Vai su "Importa"
4. Seleziona il file `database/schema.sql`
5. Clicca su "Esegui"

### 3. Configurazione Applicazione

Modifica il file `config/config.php` con i tuoi parametri:

```php
// Configurazione Database
define('DB_HOST', 'localhost');        // Host del database
define('DB_NAME', 'musicall');         // Nome database
define('DB_USER', 'root');             // Username database
define('DB_PASS', '');                 // Password database

// Configurazione Applicazione
define('BASE_URL', 'http://localhost/musicall');  // URL base dell'applicazione
```

### 4. Permessi Directory

```bash
# Linux/Mac - assicurati che il web server possa scrivere nelle directory necessarie
chmod 755 -R musicall/
chmod 777 musicall/uploads/  # Se esiste
chmod 777 musicall/logs/     # Se esiste

# Crea le directory se non esistono
mkdir -p musicall/uploads
mkdir -p musicall/logs
```

### 5. Accesso Iniziale

**URL**: `http://localhost/musicall/login.php`

**Credenziali Default**:
- Username: `admin`
- Password: `admin123`

⚠️ **IMPORTANTE**: Cambia immediatamente la password dopo il primo accesso!

## 📁 Struttura del Progetto

```
musicall/
├── assets/
│   ├── css/
│   │   └── style.css           # Stili personalizzati
│   └── js/
│       └── app.js              # JavaScript personalizzato
├── config/
│   └── config.php              # Configurazione principale
├── database/
│   └── schema.sql              # Schema database
├── includes/
│   ├── Auth.php                # Classe autenticazione
│   ├── Database.php            # Classe database
│   ├── bootstrap.php           # Bootstrap applicazione
│   ├── footer.php              # Footer template
│   ├── header.php              # Header template
│   └── helpers.php             # Funzioni helper
├── allievi/                    # Gestione allievi (da implementare)
├── docenti/                    # Gestione docenti (da implementare)
├── lezioni/                    # Gestione lezioni (da implementare)
├── assenze/                    # Gestione assenze (da implementare)
├── aule/                       # Gestione aule (da implementare)
├── materie/                    # Gestione materie (da implementare)
├── report/                     # Report e statistiche (da implementare)
├── admin/                      # Amministrazione (da implementare)
├── index.php                   # Dashboard principale
├── login.php                   # Pagina login
├── logout.php                  # Logout
├── calendario.php              # Calendario settimanale
└── README.md                   # Questo file
```

## 🎯 Utilizzo

### Dashboard

La dashboard mostra:
- Statistiche generali (allievi, docenti, lezioni, assenze)
- Prossime lezioni della giornata
- Assenze da recuperare
- Ultimi allievi aggiunti
- Azioni rapide

### Calendario Settimanale

1. Accedi al calendario dal menu o dalla dashboard
2. Seleziona il giorno della settimana dal menu a tendina
3. Visualizza tutte le lezioni organizzate per aula e orario
4. Click su una lezione per vedere i dettagli (in sviluppo)

**Legenda Colori:**
- 🔵 **Blu**: Lezione regolare
- 🟠 **Arancione**: Lezione custom
- 🟢 **Verde**: Recupero
- 🟣 **Viola**: Laboratorio/Musica d'insieme

### Gestione Dati

Le sezioni di gestione permettono di:
- ➕ Aggiungere nuovi record
- ✏️ Modificare record esistenti
- 🗑️ Eliminare record
- 🔍 Cercare e filtrare
- 📊 Visualizzare statistiche

## 🔐 Ruoli Utente

Il sistema supporta 3 ruoli:

1. **Admin**: Accesso completo a tutte le funzionalità
2. **Docente**: Visualizzazione propri orari e lezioni
3. **Segreteria**: Gestione operativa (allievi, lezioni, assenze)

## 📊 Database

### Tabelle Principali

- `users`: Utenti e autenticazione
- `allievi`: Anagrafica allievi
- `docenti`: Anagrafica docenti
- `aule`: Sale/aule disponibili
- `materie`: Materie/corsi offerti
- `lezioni`: Programmazione lezioni settimanali
- `lezioni_custom`: Lezioni una-tantum
- `assenze`: Tracciamento assenze
- `laboratori`: Corsi di gruppo/musica d'insieme
- `activity_log`: Log attività sistema

### Viste Preconfigurate

- `v_calendario_settimanale`: Vista calendario completo
- `v_statistiche_assenze`: Statistiche assenze per allievo
- `v_statistiche_docenti`: Statistiche per docente

## 🛠️ Personalizzazione

### Modificare Orari

Edita `config/config.php`:

```php
define('ORA_INIZIO_SCUOLA', '09:15');
define('ORA_FINE_SCUOLA', '22:00');
define('DURATA_SLOT_DEFAULT', 45); // minuti
```

### Aggiungere Aule

```sql
INSERT INTO aule (nome, descrizione, ordine_visualizzazione, attiva) 
VALUES ('Nuova Aula', 'Descrizione', 7, TRUE);
```

### Aggiungere Materie

```sql
INSERT INTO materie (nome, categoria, durata_standard, attiva) 
VALUES ('Sassofono', 'strumento', 45, TRUE);
```

## 🔧 Sviluppi Futuri

Le seguenti funzionalità sono pianificate:

- [ ] CRUD completo per tutte le entità
- [ ] Sistema di notifiche email
- [ ] Export report in PDF/Excel
- [ ] Calendario mensile/annuale
- [ ] Sistema di pagamenti
- [ ] App mobile
- [ ] API REST
- [ ] Integrazione calendario Google/Outlook

## 🐛 Troubleshooting

### Errore di connessione al database

```
Errore di connessione al database: Access denied for user...
```

**Soluzione**: Verifica le credenziali in `config/config.php`

### Pagina bianca

**Soluzione**: 
1. Abilita error reporting in `config/config.php`: `define('DEBUG_MODE', true);`
2. Controlla i log di PHP

### CSS/JS non caricati

**Soluzione**: Verifica che `BASE_URL` in `config/config.php` sia corretto

### Sessione non funziona

**Soluzione**: 
```bash
# Verifica permessi directory sessioni
chmod 777 /var/lib/php/sessions  # Linux
```

## 📝 Note di Sicurezza

- ⚠️ Cambia la password admin dopo l'installazione
- 🔒 Non usare in produzione senza HTTPS
- 🛡️ Backup regolari del database
- 🔐 Usa password forti per gli utenti
- 📋 Mantieni PHP e MySQL aggiornati

## 📞 Supporto

Per problemi o domande:
- Apri una issue su GitHub
- Consulta la documentazione
- Contatta il team di sviluppo

## 📄 Licenza

Questo software è fornito "as is" senza garanzie di alcun tipo.

## 🙏 Crediti

- **Frontend**: Bootstrap 5, Bootstrap Icons, jQuery
- **Backend**: PHP, MySQL
- **Ispirato da**: Sistema Excel originale MusicAll

## 🔄 Eloquent ORM - Database Agnostico (v1.1.0)

### Supporto Multi-Database

Il progetto usa **Eloquent ORM** per supportare diversi database senza cambiare codice:

**Database Supportati:**
- ✅ MySQL / MariaDB (produzione)
- ✅ SQLite (test e sviluppo)
- ✅ PostgreSQL
- ✅ SQL Server

### Configurazione Database

Modifica `config/database.php` per cambiare database:

```php
// Cambia qui per switchare database
'default' => 'mysql',  // o 'sqlite', 'pgsql', etc.
```

**Esempio MySQL:**
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'musicall',
    'username' => 'root',
    'password' => '',
]
```

**Esempio SQLite (in-memory per test):**
```php
'sqlite' => [
    'driver' => 'sqlite',
    'database' => ':memory:',  // o percorso file
]
```

### Uso dei Models

Eloquent ORM fornisce un'API elegante per interagire con il database:

```php
use MusicAll\Models\Allievo;
use MusicAll\Models\Lezione;

// Creare
$allievo = Allievo::create([
    'cognome' => 'Rossi',
    'nome' => 'Mario',
    'email' => 'mario@example.com',
    'attivo' => true
]);

// Leggere
$allievi = Allievo::all();
$allievo = Allievo::find(1);
$attivi = Allievo::attivi()->get();

// Aggiornare
$allievo->email = 'nuovo@email.com';
$allievo->save();

// Eliminare
$allievo->delete();

// Query complesse
$allievi = Allievo::where('attivo', true)
    ->orderBy('cognome')
    ->limit(10)
    ->get();

// Relazioni
$lezioni = $allievo->lezioni;  // Tutte le lezioni dell'allievo
$assenze = $allievo->assenze()->daRecuperare()->get();
```

### Models Disponibili

```php
use MusicAll\Models\Allievo;
use MusicAll\Models\Docente;
use MusicAll\Models\Lezione;
use MusicAll\Models\Aula;
use MusicAll\Models\Materia;
use MusicAll\Models\Assenza;
use MusicAll\Models\LezioneCustom;
use MusicAll\Models\User;
```

### Test con SQLite

Testa l'applicazione senza database reale:

```bash
# Test rapido in-memory
php tests/test_sqlite.php
```

Output atteso:
```
Test SQLite In-Memory con Eloquent ORM

✅ Eloquent ORM caricato
✅ Database: :memory:
✅ Driver: sqlite

Test 1: Creazione Allievi
✅ Creati 3 allievi

Test 2: Query Allievi Attivi
- Rossi Mario (mario.rossi@email.com)
- Bianchi Laura (laura.bianchi@email.com)

...

✅ TUTTI I TEST COMPLETATI CON SUCCESSO!
```

### Variabili d'Ambiente

Usa variabili d'ambiente per configurazione dinamica:

```bash
# Linux/Mac
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:

# Windows
set DB_CONNECTION=sqlite
set DB_DATABASE=:memory:
```

### Vantaggi Eloquent ORM

✅ **Database Agnostico**: Cambia DB senza toccare codice  
✅ **Testing Facile**: SQLite in-memory per test veloci  
✅ **Query Eleganti**: Sintassi fluida e leggibile  
✅ **Relazioni Automatiche**: Gestione relazioni tra tabelle  
✅ **Migrations**: Versionamento schema database  
✅ **Validazione**: Cast automatici e validazione  

### Migration da v1.0 a v1.1

Se hai già la v1.0 installata:

```bash
# 1. Installa Composer dependencies
composer install

# 2. Il codice esistente continua a funzionare
#    (backward compatibility garantita)

# 3. Inizia ad usare i Models gradualmente
use MusicAll\Models\Allievo;
$allievi = Allievo::all();
```

---

**Versione**: 1.1.0  
**Data**: Febbraio 2026  
**Autore**: Sistema di Gestione Scuola di Musica  
**Novità v1.1**: Eloquent ORM, Supporto Multi-Database, Testing SQLite
