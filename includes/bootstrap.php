<?php
/**
 * Bootstrap File
 * Carica configurazione, Eloquent ORM e classi necessarie
 */

// Use statements devono essere all'inizio
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Carica configurazione
require_once __DIR__ . '/../config/config.php';

// Carica Composer autoloader (se esiste)
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    
    // Inizializza Eloquent ORM
    
    $capsule = new Capsule;
    
    // Carica configurazione database
    $dbConfig = require __DIR__ . '/../config/database.php';
    $defaultConnection = $dbConfig['default'];
    $connections = $dbConfig['connections'];
    
    // Aggiungi connessione di default (senza nome diventa 'default')
    if (isset($connections[$defaultConnection])) {
        $capsule->addConnection($connections[$defaultConnection]);
    }
    
    // Aggiungi altre connessioni nominate
    foreach ($connections as $name => $config) {
        if ($name !== $defaultConnection) {
            $capsule->addConnection($config, $name);
        }
    }
    
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
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/EmailHelper.php';
require_once __DIR__ . '/CSRFHelper.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/InputValidator.php';
require_once __DIR__ . '/PagamentiHelper.php';

// Funzioni helper
require_once __DIR__ . '/helpers.php';

// Carica Controllers
require_once __DIR__ . '/controllers/SociController.php';
require_once __DIR__ . '/controllers/DocentiController.php';
require_once __DIR__ . '/controllers/LezioniController.php';
require_once __DIR__ . '/controllers/AssenzeController.php';
require_once __DIR__ . '/controllers/AuleController.php';
require_once __DIR__ . '/controllers/RecuperiController.php';
require_once __DIR__ . '/controllers/EventiController.php';
require_once __DIR__ . '/controllers/IscrizioniController.php';
require_once __DIR__ . '/controllers/ConfigurazioneCorsiController.php';

// Inizializza oggetti globali
if (!ELOQUENT_ENABLED) {
    // Legacy mode: usa classe Database
    $db = Database::getInstance();
}
// Auth sempre necessario per login/logout
$auth = new Auth();

// Carica Models (se Eloquent disponibile)
if (ELOQUENT_ENABLED && is_dir(__DIR__ . '/../app/Models')) {
    foreach (glob(__DIR__ . '/../app/Models/*.php') as $modelFile) {
        require_once $modelFile;
    }
}