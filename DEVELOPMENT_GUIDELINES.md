# MusicAll - Development Guidelines

## 📋 Linee Guida di Sviluppo

Questo documento contiene le convenzioni e best practices da seguire per lo sviluppo del progetto MusicAll.

---

## 🏗️ Architettura

### Pattern MVC
- **Model**: Database + Eloquent ORM (futuro)
- **View**: File PHP in root (index.php, login.php, etc.)
- **Controller**: `includes/controllers/`

### Struttura Directory
```
MusicAll/
├── includes/
│   ├── controllers/     # Controller per logica business
│   ├── Database.php     # Gestione connessione DB
│   ├── Auth.php         # Autenticazione
│   └── helpers.php      # Funzioni utility
├── config/              # Configurazioni
├── database/            # SQL, migrations, seed
├── assets/              # CSS, JS, immagini
└── tests/               # Script di test
```

---

## 💾 Database

### Principio Fondamentale: DATABASE-AGNOSTIC
**SEMPRE scrivere query SQL compatibili sia con SQLite che MySQL!**

### ✅ Query Corrette (Database-Agnostic)
```sql
-- Concatenazione: usa || (standard SQL)
SELECT cognome || ' ' || nome as nome_completo FROM allievi;

-- Date/Time SQLite
SELECT datetime('now');
SELECT strftime('%Y-%m', data) = strftime('%Y-%m', 'now');

-- No funzioni proprietarie!
```

### ❌ Query Sbagliate (Database-Specific)
```sql
-- NO! CONCAT è MySQL-specific
SELECT CONCAT(cognome, ' ', nome) FROM allievi;

-- NO! NOW() è MySQL-specific  
SELECT NOW();

-- NO! MONTH(), YEAR() sono MySQL-specific
WHERE MONTH(data) = MONTH(NOW());
```

### Driver Supportati
- **SQLite** (default): File database locale
- **MySQL**: Supporto futuro con stessa codebase

---

## 🎯 Controller Pattern

### Regola d'Oro
**MAI scrivere query SQL nei file View (HTML/PHP)!**

### ✅ Corretto
```php
// index.php (VIEW)
$allieviCtrl = new AllieviController();
$allievi = $allieviCtrl->getAllievi();
```

### ❌ Sbagliato
```php
// index.php (VIEW)
$allievi = $db->query("SELECT * FROM allievi"); // NO!
```

### Struttura Controller
```php
class AllieviController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllievi($attivi_only = true) {
        // Query qui, non nelle view
        return $this->db->query("SELECT...");
    }
}
```

---

## 📝 Convenzioni Codice

### Naming
- **Classi**: PascalCase (es: `AllieviController`)
- **Metodi**: camelCase (es: `getAllievi()`)
- **Variabili**: snake_case (es: `$prossime_lezioni`)
- **Costanti**: UPPER_CASE (es: `BASE_URL`)

### File
- **Controller**: `{Nome}Controller.php`
- **View**: nome descrittivo (es: `index.php`, `login.php`)
- **Config**: snake_case (es: `config.php`, `database.php`)

### Commenti
```php
/**
 * Descrizione metodo
 * @param int $id ID dell'allievo
 * @return array Dati allievo
 */
public function getAllievoById($id) {
    // Implementazione
}
```

---

## 🔒 Sicurezza

### Prepared Statements
**SEMPRE usare prepared statements!**

```php
// ✅ Corretto
$db->query("SELECT * FROM users WHERE id = ?", [$id]);

// ❌ Sbagliato
$db->query("SELECT * FROM users WHERE id = $id"); // SQL Injection!
```

### Escape Output
```php
// ✅ Usa sempre e() per output
echo e($user['nome']); // Previene XSS

// ❌ Mai output diretto
echo $user['nome']; // Vulnerabile!
```

### Password
```php
// ✅ Sempre hash
password_hash($password, PASSWORD_DEFAULT);
password_verify($input, $hash);

// ❌ Mai plaintext
// Non salvare mai password in chiaro!
```

---

## 🎨 Frontend

### Bootstrap 5.3
- Usa classi Bootstrap per layout
- Custom CSS in `assets/css/style.css`
- Icone: Bootstrap Icons

### Template
```php
<?php
require_once 'includes/bootstrap.php';
$auth->requireLogin(); // Se richiede auth

// Controller logic
$ctrl = new SomeController();
$data = $ctrl->getData();

include 'includes/header.php';
?>

<!-- HTML qui -->

<?php include 'includes/footer.php'; ?>
```

---

## 🧪 Testing

### Script di Test
Posizionare in `tests/`

```php
// tests/test_feature.php
require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== TEST FEATURE ===\n";
// Test logic
echo "✅ Test passed\n";
```

### Esecuzione
```bash
php tests/test_feature.php
```

---

## 📦 Git

### Commit Messages
```
Tipo: Descrizione breve

Dettagli (opzionale)
```

**Tipi:**
- `feat:` - Nuova funzionalità
- `fix:` - Bug fix
- `refactor:` - Refactoring codice
- `docs:` - Documentazione
- `style:` - Formattazione
- `test:` - Test

**Esempio:**
```
feat: Implementato AllieviController

- Creato controller per gestione allievi
- Metodi: getAllievi, countAllievi, searchAllievi
- Query database-agnostiche
```

---

## 🚀 Development Workflow

### 1. Planning
- Analizza requisito
- Identifica controller necessario
- Pianifica query database-agnostiche

### 2. Implementation
- Crea/modifica controller
- Scrivi metodi con query SQL standard
- Aggiorna view per usare controller

### 3. Testing
- Testa con SQLite (default)
- Verifica compatibilità MySQL
- Crea script test se necessario

### 4. Commit
- Commit atomici e descrittivi
- Documenta modifiche importanti

---

## 📚 Risorse

### File Importanti
- `README.md` - Guida generale progetto
- `config/config.php` - Configurazione app
- `database/schema_sqlite.sql` - Schema database
- `includes/bootstrap.php` - Init applicazione

### Helper Functions
Vedi `includes/helpers.php`:
- `e($str)` - Escape HTML
- `formatDate($date)` - Formatta date
- `nomeCompleto($cognome, $nome)` - Nome completo
- Altre utility

---

## ⚠️ Common Pitfalls

### 1. Query Non Agnostiche
❌ Usare `CONCAT()`, `NOW()`, `MONTH()`
✅ Usare `||`, `datetime('now')`, `strftime()`

### 2. Query nelle View
❌ SQL in index.php, calendario.php, etc.
✅ SQL solo nei Controller

### 3. Input Non Sanitizzato
❌ Query con variabili dirette
✅ Sempre prepared statements

### 4. Output Non Escaped
❌ `echo $var;`
✅ `echo e($var);`

---

## 🎯 Prossimi Step

### TODO
- [ ] Implementare CRUD completo allievi
- [ ] Calendario interattivo
- [ ] Sistema notifiche
- [ ] API REST endpoints
- [ ] Report e statistiche avanzate

### Nice to Have
- [ ] Export Excel/PDF
- [ ] Sistema permessi ruoli
- [ ] Log avanzato attività
- [ ] Dashboard admin

---

**Ultima modifica:** 04/02/2026
**Versione:** 0.3.0
