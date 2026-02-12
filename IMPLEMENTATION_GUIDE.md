# 🚀 Guida Implementazione Sistema Eventi - FASE 1-4

## ✅ Già Completato
- [x] Database migrato (35 lezioni → eventi_calendario)
- [x] Schema aggiornato per nuovi deployment
- [x] Model EventoCalendario creato
- [x] Password admin: P@ssw0rd1

---

## 📦 DELIVERABLE MINIMI PER FASE 1-4

### Fase 1: Backend Essenziale

#### Models da creare (priorità):
```
✅ app/Models/EventoCalendario.php        [FATTO]
⬜ app/Models/TipologiaEvento.php         [TODO - semplice, solo lettura]
⬜ app/Models/Pagamento.php                [TODO - per fase 2]
⬜ app/Models/Iscrizione.php               [TODO - per fase 2]
```

**NOTA**: Gli altri models possono aspettare. EventoCalendario è il core.

---

### Fase 2: API Essenziali

#### ✅ API Eventi Calendario (PRIORITÀ 1)
**File**: `api_eventi.php`

```php
<?php
// GET    /api_eventi.php?action=list&date=2026-02-12
// POST   /api_eventi.php?action=create
// PUT    /api_eventi.php?action=update&id=1
// DELETE /api_eventi.php?action=delete&id=1
```

**Features minime**:
- Lista eventi per data
- Crea evento (con validazione conflitti)
- Modifica evento
- Elimina evento (soft delete)

---

### Fase 3: Frontend Minimo Funzionante

#### ✅ Calendario Unificato (PRIORITÀ 1)
**File**: `calendario_unificato.php`

**Features**:
- Vista settimanale con FullCalendar
- Colori per tipologia evento
- Click → modal dettagli
- Filtro per aula/docente

**Libraries**:
- FullCalendar 6.x (già in use?)
- Bootstrap 5 (già in use)

---

### Fase 4: Adattamento Esistente

#### ✅ Modifica calendario.php attuale
**Modifiche minime**:
1. Query da `lezioni` → `v_calendario_unificato`
2. Aggiungere colori per tipologia
3. Mantenere funzionalità esistenti

---

## 🎯 IMPLEMENTAZIONE RAPIDA (2-3 giorni)

### Giorno 1: API Base
```bash
# Crea API eventi
nano api_eventi.php

# Testa API
php tests/test_api_eventi.php
```

### Giorno 2: Frontend  Base
```bash
# Modifica calendario esistente
nano calendario.php

# Test browser
open http://localhost/musicall/calendario.php
```

### Giorno 3: Integrazione & Test
```bash
# Verifica tutto funzioni
# Fix bug
# Deploy
```

---

## 📝 TEMPLATE PRONTI ALL'USO

### 1️⃣ API Eventi (api_eventi.php)

```php
<?php
require_once 'includes/bootstrap.php';
require_once 'app/Models/EventoCalendario.php';

header('Content-Type: application/json');

// Autenticazione
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$db = Database::getInstance()->getConnection();

try {
    switch ($action) {
        case 'list':
            // GET eventi per data
            $date = $_GET['date'] ?? date('Y-m-d');
            $giornoSettimana = date('l', strtotime($date)); // lunedi, martedi, etc.
            
            $eventi = EventoCalendario::forDate($db, $date, strtolower($giornoSettimana));
            
            echo json_encode([
                'success' => true,
                'data' => $eventi
            ]);
            break;
            
        case 'create':
            // POST nuovo evento
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validazione base
            if (!isset($data['tipologia_id'], $data['aula_id'], $data['ora_inizio'], $data['ora_fine'])) {
                throw new Exception('Dati mancanti');
            }
            
            // Crea evento
            $evento = new EventoCalendario($db);
            $evento->fill($data);
            $evento->created_by = $_SESSION['user_id'];
            
            // Verifica conflitti
            if ($evento->hasConflicts()) {
                throw new Exception('Conflitto orario: aula già occupata');
            }
            
            $id = $evento->save();
            
            echo json_encode([
                'success' => true,
                'id' => $id,
                'message' => 'Evento creato'
            ]);
            break;
            
        case 'update':
            // PUT modifica evento
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID mancante');
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $evento = EventoCalendario::find($db, $id);
            if (!$evento) throw new Exception('Evento non trovato');
            
            $evento->fill($data);
            $evento->updated_by = $_SESSION['user_id'];
            
            // Verifica conflitti (escludendo se stesso)
            if ($evento->hasConflicts($id)) {
                throw new Exception('Conflitto orario');
            }
            
            $evento->save();
            
            echo json_encode([
                'success' => true,
                'message' => 'Evento aggiornato'
            ]);
            break;
            
        case 'delete':
            // DELETE evento (soft)
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID mancante');
            
            $evento = EventoCalendario::find($db, $id);
            if (!$evento) throw new Exception('Evento non trovato');
            
            $evento->attivo = 0;
            $evento->save();
            
            echo json_encode([
                'success' => true,
                'message' => 'Evento eliminato'
            ]);
            break;
            
        default:
            throw new Exception('Azione non valida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

---

### 2️⃣ Modifiche a calendario.php

**TROVA**:
```php
// Query lezioni esistente
$query = "SELECT l.*, ... FROM lezioni l ...";
```

**SOSTITUISCI CON**:
```php
// Query nuova con eventi_calendario
$query = "SELECT * FROM v_calendario_unificato WHERE ...";
```

**AGGIUNGI colori tipologia**:
```javascript
// In FullCalendar config
eventDidMount: function(info) {
    info.el.style.backgroundColor = info.event.extendedProps.colore_bg;
    info.el.style.borderColor = info.event.extendedProps.colore_border;
}
```

---

### 3️⃣ Test API (tests/test_api_eventi.php)

```php
<?php
require_once 'includes/bootstrap.php';

// Test 1: Lista eventi
echo "Test 1: GET eventi...\n";
$response = file_get_contents('http://localhost/musicall/api_eventi.php?action=list&date=2026-02-12');
$data = json_decode($response, true);
print_r($data);

// Test 2: Crea evento
echo "\nTest 2: POST nuovo evento...\n";
$evento = [
    'tipologia_id' => 1, // LEZ_REGOLARE
    'ricorrente' => 0,
    'data_evento' => '2026-02-15',
    'ora_inizio' => '10:00:00',
    'ora_fine' => '10:45:00',
    'aula_id' => 1,
    'docente_id' => 1,
    'materia_id' => 1,
    'allievo_id' => 1
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($evento)
    ]
]);

$response = file_get_contents('http://localhost/musicall/api_eventi.php?action=create', false, $context);
$data = json_decode($response, true);
print_r($data);

echo "\n✅ Test completati\n";
```

---

## 🎯 NEXT STEPS IMMEDIATI

1. **Crea `api_eventi.php`** con il template sopra
2. **Testa con**: `php tests/test_api_eventi.php`
3. **Modifica `calendario.php`** per usare `v_calendario_unificato`
4. **Verifica in browser**: eventi visibili con colori tipologia

---

## 📊 METRICHE SUCCESSO

### Minimo Viable Product (MVP):
- ✅ API lista eventi funziona
- ✅ API crea evento funziona
- ✅ Calendario visualizza eventi con colori
- ✅ Nessun breaking change su funzionalità esistenti

### Nice to Have (Fase 2):
- ⬜ Drag & drop eventi
- ⬜ Filtri avanzati
- ⬜ Export/print calendario
- ⬜ Notifiche email

---

## ⚠️ NOTE IMPORTANTI

### Backward Compatibility
- Tabella `lezioni` resta intatta
- Queries esistenti continuano a funzionare
- `v_calendario_unificato` è un layer sopra

### Sicurezza
- ✅ Autenticazione obbligatoria API
- ✅ Validazione input
- ✅ Verifica conflitti orari
- ✅ Soft delete (attivo=0)

### Performance
- ✅ Indici DB già ottimizzati
- ✅ VIEW pre-calcolata
- ⬜ Cache (opzionale, fase 2)

---

## 🐛 TROUBLESHOOTING

### API non risponde
```bash
# Verifica permessi
chmod 644 api_eventi.php

# Verifica log errori
tail -f /var/log/apache2/error.log
```

### Eventi non visibili
```sql
-- Verifica dati in DB
SELECT COUNT(*) FROM eventi_calendario WHERE attivo=1;
SELECT * FROM v_calendario_unificato LIMIT 5;
```

### Conflitti orari non rilevati
```php
// Debug hasConflicts()
var_dump($evento->hasConflicts());
```

---

**Ultimo aggiornamento**: 2026-02-12  
**Versione**: v1.1.0 + Eventi MVP  
**Tempo stimato implementazione**: 2-3 giorni