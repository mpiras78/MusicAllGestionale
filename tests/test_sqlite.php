<?php
/**
 * Test SQLite In-Memory con Eloquent ORM
 * 
 * Questo script dimostra come usare SQLite in-memory per test rapidi
 * 
 * Esegui: php tests/test_sqlite.php
 */

// Imposta connessione a SQLite per test
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');

require_once __DIR__ . '/../includes/bootstrap.php';

use MusicAll\Models\Allievo;
use MusicAll\Models\Docente;
use MusicAll\Models\Aula;
use MusicAll\Models\Materia;
use Illuminate\Database\Capsule\Manager as DB;

echo "========================================\n";
echo "Test SQLite In-Memory con Eloquent ORM\n";
echo "========================================\n\n";

// Verifica connessione
try {
    if (!ELOQUENT_ENABLED) {
        die("❌ Eloquent non abilitato. Esegui: composer install\n");
    }
    
    echo "✅ Eloquent ORM caricato\n";
    echo "✅ Database: " . DB::connection()->getDatabaseName() . "\n";
    echo "✅ Driver: " . DB::connection()->getDriverName() . "\n\n";
    
    // Crea tabelle (schema semplificato per test)
    echo "Creazione tabelle...\n";
    
    DB::schema()->create('allievi', function ($table) {
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
    
    DB::schema()->create('docenti', function ($table) {
        $table->id();
        $table->string('cognome');
        $table->string('nome');
        $table->string('email')->nullable();
        $table->string('telefono')->nullable();
        $table->string('specializzazione')->nullable();
        $table->boolean('attivo')->default(true);
        $table->timestamps();
    });
    
    DB::schema()->create('aule', function ($table) {
        $table->id();
        $table->string('nome');
        $table->text('descrizione')->nullable();
        $table->integer('ordine_visualizzazione')->default(0);
        $table->boolean('attiva')->default(true);
        $table->timestamps();
    });
    
    echo "✅ Tabelle create\n\n";
    
    // Test 1: Crea Allievi
    echo "Test 1: Creazione Allievi\n";
    echo "-------------------------\n";
    
    $allievo1 = Allievo::create([
        'cognome' => 'Rossi',
        'nome' => 'Mario',
        'email' => 'mario.rossi@email.com',
        'telefono' => '333-1234567',
        'attivo' => true
    ]);
    
    $allievo2 = Allievo::create([
        'cognome' => 'Bianchi',
        'nome' => 'Laura',
        'email' => 'laura.bianchi@email.com',
        'attivo' => true
    ]);
    
    $allievo3 = Allievo::create([
        'cognome' => 'Verdi',
        'nome' => 'Giuseppe',
        'attivo' => false
    ]);
    
    echo "✅ Creati " . Allievo::count() . " allievi\n\n";
    
    // Test 2: Query con Eloquent
    echo "Test 2: Query Allievi Attivi\n";
    echo "-----------------------------\n";
    
    $allieviAttivi = Allievo::attivi()->get();
    foreach ($allieviAttivi as $allievo) {
        echo "- {$allievo->nome_completo} ({$allievo->email})\n";
    }
    echo "\n";
    
    // Test 3: Crea Docenti
    echo "Test 3: Creazione Docenti\n";
    echo "-------------------------\n";
    
    Docente::create([
        'cognome' => 'Maestri',
        'nome' => 'Giovanni',
        'email' => 'g.maestri@musicall.it',
        'specializzazione' => 'Pianoforte',
        'attivo' => true
    ]);
    
    Docente::create([
        'cognome' => 'Melodia',
        'nome' => 'Sofia',
        'specializzazione' => 'Canto',
        'attivo' => true
    ]);
    
    echo "✅ Creati " . Docente::count() . " docenti\n\n";
    
    // Test 4: Crea Aule
    echo "Test 4: Creazione Aule\n";
    echo "----------------------\n";
    
    Aula::create(['nome' => 'Aula MIDI', 'ordine_visualizzazione' => 1, 'attiva' => true]);
    Aula::create(['nome' => 'Aula Piano', 'ordine_visualizzazione' => 2, 'attiva' => true]);
    Aula::create(['nome' => 'Sala Magna', 'ordine_visualizzazione' => 3, 'attiva' => true]);
    
    echo "✅ Create " . Aula::count() . " aule\n\n";
    
    // Test 5: Query complesse
    echo "Test 5: Query con Scopes\n";
    echo "------------------------\n";
    
    $auleAttive = Aula::attive()->ordinata()->get();
    foreach ($auleAttive as $aula) {
        echo "- {$aula->nome} (ordine: {$aula->ordine_visualizzazione})\n";
    }
    echo "\n";
    
    // Test 6: Aggiornamenti
    echo "Test 6: Aggiornamento Record\n";
    echo "----------------------------\n";
    
    $allievo = Allievo::find(1);
    $allievo->telefono = '333-9999999';
    $allievo->save();
    
    echo "✅ Aggiornato allievo: {$allievo->nome_completo}\n";
    echo "   Nuovo telefono: {$allievo->telefono}\n\n";
    
    // Test 7: Eliminazione
    echo "Test 7: Eliminazione Record\n";
    echo "---------------------------\n";
    
    $count = Allievo::count();
    Allievo::find(3)->delete();
    $newCount = Allievo::count();
    
    echo "✅ Eliminato 1 allievo\n";
    echo "   Prima: {$count} - Dopo: {$newCount}\n\n";
    
    // Test 8: Query Builder
    echo "Test 8: Query Builder Raw\n";
    echo "-------------------------\n";
    
    $results = DB::table('allievi')
        ->where('attivo', true)
        ->orderBy('cognome')
        ->get();
    
    echo "Query: SELECT * FROM allievi WHERE attivo = 1 ORDER BY cognome\n";
    echo "Risultati: {$results->count()}\n\n";
    
    // Statistiche finali
    echo "========================================\n";
    echo "STATISTICHE FINALI\n";
    echo "========================================\n";
    echo "Allievi totali: " . Allievo::count() . "\n";
    echo "Allievi attivi: " . Allievo::attivi()->count() . "\n";
    echo "Docenti: " . Docente::count() . "\n";
    echo "Aule: " . Aula::count() . "\n";
    echo "\n";
    
    echo "✅ TUTTI I TEST COMPLETATI CON SUCCESSO!\n";
    echo "========================================\n\n";
    
    echo "💡 Nota: Questo database è in-memory e sarà cancellato alla fine dello script.\n";
    echo "   Per usare un database persistente, cambia la configurazione in config/database.php\n\n";
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}