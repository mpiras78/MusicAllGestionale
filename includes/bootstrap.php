<?php
/**
 * Bootstrap File
 * Carica configurazione, Eloquent ORM e classi necessarie
 */

// Carica configurazione
require_once __DIR__ . '/../config/config.php';

// Carica Composer autoloader (se esiste)
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    
    // Inizializza Eloquent ORM
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Events\Dispatcher;
    use Illuminate\Container\Container;
    
    $capsule = new Capsule;
    
    // Carica configurazione database
    $dbConfig = require __DIR__ . '/../config/database.php';
    $defaultConnection = $dbConfig['default'];
    $connections = $dbConfig['connections'];
    
    // Aggiungi connessioni
    foreach ($connections as $name => $config) {
        $capsule->addConnection($config, $name);
    }
    
    // Imposta connection di default
    $capsule->addConnection($connections[$defaultConnection]);
    
    // Set eventi e container
    $capsule->setEventDispatcher(new Dispatcher(new Container));
    
    // Rendi Eloquent disponibile globalmente
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    define('ELOQUENT_ENABLED', true);
} else {
    define('ELOQUENT_ENABLED', false);
}

// Carica classi legacy (backwards compatibility)
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

// Funzioni helper
require_once __DIR__ . '/helpers.php';

// Inizializza oggetti globali (legacy mode se Eloquent non disponibile)
if (!ELOQUENT_ENABLED) {
    $db = Database::getInstance();
}
$auth = new Auth();

// Carica Models (se Eloquent disponibile)
if (ELOQUENT_ENABLED && is_dir(__DIR__ . '/../app/Models')) {
    foreach (glob(__DIR__ . '/../app/Models/*.php') as $modelFile) {
        require_once $modelFile;
    }
}