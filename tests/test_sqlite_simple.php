<?php
/**
 * Test SQLite Semplificato
 * Configura direttamente SQLite in-memory
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use MusicAll\Models\Socio;
use MusicAll\Models\Docente;
use MusicAll\Models\Aula;

echo "========================================\n";
echo "Test SQLite In-Memory - Setup Manuale\n";
echo "========================================\n\n";

// Inizializza Eloquent con SQLite in-memory
$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
    'foreign_key_constraints' => true,
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "✅ Eloquent ORM caricato\n";
echo "✅ Database: SQLite in-memory\n\n";

// Carica Models
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Socio.php';
require_once __DIR__ . '/../app/Models/Docente.php';
require_once __DIR__ . '/../app/Models/Aula.php';

try {
    // Crea tabelle
    echo "Creazione tabelle...\n";
    
    // Usare la tabella `soci` (model Socio punta a 'soci')
    Capsule::schema()->create('soci', function ($table) {
        $table->id();
        $table->string('cognome');
        $table->string('nome');
        $table->string('email')->nullable();
        $table->string('telefono')->nullable();
        $table->date('data_nascita')->nullable();
        $table->text('indirizzo')->nullable();
        $table->text('note')->nullable();
        $table->boolean('attivo')->default(true);
        $table->timestamps();
    });
    
    Capsule::schema()->create('docenti', function ($table) {
        $table->id();
        $table->string('cognome');
        $table->string('nome');
        $table->string('email')->nullable();
        $table->string('telefono')->nullable();
        $table->string('specializzazione')->nullable();
        $table->boolean('attivo')->default(true);
        $table->timestamps();
    });
    
    Capsule::schema()->create('aule', function ($table) {
        $table->id();
        $table->string('nome');
        $table->text('descrizione')->nullable();
        $table->integer('ordine_visualizzazione')->default(0);
        $table->boolean('attiva')->default(true);
        $table->timestamps();
    });
    
    echo "✅ Tabelle create\n\n";
    
    // Test CRUD
    echo "Test 1: Creazione Soci (ex Soci)\n";
    echo "-------------------------\n";
    
    Socio::create([
        'cognome' => 'Rossi',
        'nome' => 'Mario',
        'email' => 'mario.rossi@email.com',
        'telefono' => '333-1234567',
        'attivo' => true
    ]);
    
    Socio::create([
        'cognome' => 'Bianchi',
        'nome' => 'Laura',
        'email' => 'laura.bianchi@email.com',
        'attivo' => true
    ]);
    
    echo "✅ Creati " . Socio::count() . " soci\n\n";
    
    echo "Test 2: Query con Scopes\n";
    echo "------------------------\n";
    
    $sociAttivi = Socio::attivi()->get();
    foreach ($sociAttivi as $socio) {
        echo "- {$socio->nome_completo} ({$socio->email})\n";
    }
    echo "\n";
    
    echo "Test 3: Creazione Docenti\n";
    echo "-------------------------\n";
    
    Docente::create([
        'cognome' => 'Maestri',
        'nome' => 'Giovanni',
        'email' => 'g.maestri@musicall.it',
        'specializzazione' => 'Pianoforte'
    ]);
    
    echo "✅ Creati " . Docente::count() . " docenti\n\n";
    
    echo "Test 4: Creazione Aule\n";
    echo "----------------------\n";
    
    Aula::create(['nome' => 'Aula MIDI', 'ordine_visualizzazione' => 1]);
    Aula::create(['nome' => 'Aula Piano', 'ordine_visualizzazione' => 2]);
    
    echo "✅ Create " . Aula::count() . " aule\n\n";
    
    // Statistiche
    echo "========================================\n";
    echo "STATISTICHE FINALI\n";
    echo "========================================\n";
    echo "Soci: " . Socio::count() . "\n";
    echo "Docenti: " . Docente::count() . "\n";
    echo "Aule: " . Aula::count() . "\n\n";
    
    echo "✅ TUTTI I TEST COMPLETATI CON SUCCESSO!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}