# Test Suite MusicAll

Suite completa di unit test, integration test e feature test per MusicAll.

## 📋 Struttura Test

```
tests/
├── Unit/                          # Test logica business isolata
│   ├── AssenzeRecuperiTest.php   # ⭐⭐⭐ CRITICO - Logica recuperi
│   ├── CalendarioTest.php        # ⭐⭐⭐ Funzioni calendario
│   ├── HelpersTest.php           # ⭐⭐ Utility functions
│   └── ValidationTest.php        # ⭐⭐ Validazione input
│
├── Integration/                   # Test integrazione componenti
│   └── DatabaseTest.php          # ⭐⭐ Query e transazioni
│
├── Feature/                       # Test end-to-end
│   └── GestioneAssenzeWorkflowTest.php  # ⭐ Workflow completi
│
├── bootstrap.php                  # Setup ambiente test
└── README.md                      # Questa guida
```

## 🚀 Esecuzione Test

### Tutti i test
```bash
vendor/bin/phpunit
```

### Solo unit test
```bash
vendor/bin/phpunit --testsuite Unit
```

### Solo integration test
```bash
vendor/bin/phpunit --testsuite Integration
```

### Test specifico
```bash
vendor/bin/phpunit tests/Unit/AssenzeRecuperiTest.php
```

### Con coverage
```bash
vendor/bin/phpunit --coverage-html coverage/
```

## 📊 Test Implementati

### Unit Tests (6 file, ~40 test)

#### AssenzeRecuperiTest.php ⭐⭐⭐ CRITICO
- ✅ Assenza docente sempre da recuperare
- ✅ Prime 3 assenze socio obbligatorie
- ✅ 4a assenza socio non obbligatoria
- ✅ Logica completa determinazione da_recuperare
- ✅ Anno scolastico settembre-giugno
- ✅ Conteggio assenze per coppia socio-lezione

#### CalendarioTest.php ⭐⭐⭐
- ✅ Calcolo rowspan slot 15 minuti
- ✅ Generazione slot orari
- ✅ Generazione slot giornata completa
- ✅ Rilevamento sovrapposizione lezioni
- ✅ Lezioni consecutive non si sovrappongono
- ✅ Riconoscimento festività italiane
- ✅ Mapping giorni settimana
- ✅ Icone materie corrette

#### HelpersTest.php ⭐⭐
- ✅ Formattazione data italiana
- ✅ Formattazione ora
- ✅ Sanitizzazione input (XSS prevention)
- ✅ Validazione email
- ✅ Validazione telefono italiano
- ✅ Generazione slug
- ✅ Calcolo età
- ✅ Formattazione valuta euro
- ✅ Troncamento testo
- ✅ Generazione colore avatar
- ✅ Estrazione iniziali nome

#### ValidationTest.php ⭐⭐
- ✅ Validazione dati socio completi
- ✅ Validazione dati socio incompleti
- ✅ Validazione orario lezione
- ✅ Validazione data assenza
- ✅ Validazione durata lezione standard
- ✅ Validazione giorno settimana

### Integration Tests

#### DatabaseTest.php ⭐⭐
- ✅ Connessione database funzionante
- ✅ Creazione tabella e insert
- ✅ Transazione con commit
- ✅ Transazione con rollback
- ✅ Query con JOIN
- ✅ Query con aggregazione
- ✅ Prepared statement con parametri

### Feature Tests

#### GestioneAssenzeWorkflowTest.php ⭐
- ✅ Workflow completo registrazione assenza
- ✅ Workflow programmazione recupero
- ✅ Workflow filtro assenze

## 🎯 Coverage Target

```
✅ Code Coverage Totale:    >80%
✅ Logica Assenze/Recuperi: 100%
✅ Funzioni Calendario:     >90%
✅ Helper Functions:        >80%
✅ Validazione:             >85%
```

## 📝 Convenzioni Test

### Naming
```php
// Pattern: test + DescrizioneComportamento
public function testAssenzaDocenteSempreDaRecuperare()
public function testCalcolaRowspanCorretto()
```

### Assertions comuni
```php
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertCount($expectedCount, $array);
$this->assertStringContains($needle, $haystack);
$this->assertArrayHasKey($key, $array);
```

## 🔧 Setup Ambiente Test

### Requisiti
```bash
composer require --dev phpunit/phpunit
```

### Configurazione
File `phpunit.xml` già configurato con:
- Database SQLite in-memory
- Bootstrap automatico
- Coverage reporting

## 📈 Esecuzione CI/CD

### GitHub Actions (esempio)
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit
```

## 🐛 Debug Test

### Verbose output
```bash
vendor/bin/phpunit --verbose
```

### Stop on failure
```bash
vendor/bin/phpunit --stop-on-failure
```

### Test specifico con debug
```bash
vendor/bin/phpunit --filter testAssenzaDocenteSempreDaRecuperare --debug
```

## 📚 Best Practices

1. **Test Isolati**: Ogni test deve essere indipendente
2. **Naming Chiaro**: Nome test descrive comportamento
3. **Arrange-Act-Assert**: Struttura test in 3 fasi
4. **Mock Esterni**: Mocka database/API esterne
5. **Fast Tests**: Test veloci (<100ms ciascuno)

## 🎓 Esempi Uso

### Test semplice
```php
public function testEsempioSemplice()
{
    $result = 2 + 2;
    $this->assertEquals(4, $result);
}
```

### Test con setup
```php
private $calculator;

protected function setUp(): void
{
    $this->calculator = new Calculator();
}

public function testAddizione()
{
    $result = $this->calculator->add(2, 3);
    $this->assertEquals(5, $result);
}
```

## 🔮 Prossimi Test da Implementare

- [ ] Test Models Eloquent
- [ ] Test Controllers
- [ ] Test API endpoints
- [ ] Test autenticazione
- [ ] Test permessi ruoli
- [ ] Performance tests

## 📞 Supporto

Per problemi o domande sui test:
1. Verifica documentazione PHPUnit
2. Controlla esempi in questa suite
3. Consulta team di sviluppo

---

**Versione Test Suite:** 1.0.0  
**Ultima modifica:** 16 Febbraio 2026  
**Test Totali:** ~40  
**Coverage:** Target >80%
