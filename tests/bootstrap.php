<?php
/**
 * Bootstrap per Test Suite MusicAll
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Carica configurazione test
define('APP_ENV', 'testing');
define('BASE_PATH', dirname(__DIR__));

// Carica helpers se esistono
if (file_exists(__DIR__ . '/../includes/helpers.php')) {
    require_once __DIR__ . '/../includes/helpers.php';
}

// Setup database in-memory per test
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Funzioni helper per test
if (!function_exists('createTestDatabase')) {
    function createTestDatabase() {
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        // Esegui schema (semplificato per SQLite)
        // In produzione, usa migrations
    }
}
