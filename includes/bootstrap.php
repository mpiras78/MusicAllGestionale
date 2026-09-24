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

// Security Headers (sec-003: Content Security Policy)
// REASON: CSP previene XSS injection bloccando inline scripts non autorizzati
// SEVERITY: HIGH - sec-003
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // HTTPS Redirect (sec-004: Enforce HTTPS)
    // REASON: HTTPS obbligatorio per proteggere dati in transito (man-in-the-middle prevention)
    // SEVERITY: HIGH - sec-004
    if (!DEBUG_MODE && !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        if (strpos(BASE_URL, 'https://') !== false && $protocol === 'http://') {
            // Redirect a HTTPS
            $url = str_replace('http://', 'https://', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            header('Location: ' . $url, true, 301);
            exit;
        }
    }
    
    // Imposta headers di sicurezza
    header("X-Content-Type-Options: nosniff");                          // Previene MIME sniffing
    header("X-Frame-Options: SAMEORIGIN");                              // Previene clickjacking
    header("X-XSS-Protection: 1; mode=block");                          // XSS protection (legacy)
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // HSTS header (sec-004)
    
    // Genera nonce per OGNI richiesta HTTP (non per sessione)
    // MOTIVO: Il nonce deve cambiare ad ogni richiesta per sicurezza CSP ottimale
    $_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
    $csp_nonce = $_SESSION['csp_nonce'];
    
    // Content Security Policy - restrittiva con nonce
    $csp = "default-src 'self'; "
        . "script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com 'nonce-{$csp_nonce}'; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "  // Inline CSS per Bootstrap
        . "img-src 'self' data: https:; "
        . "font-src 'self' https://cdn.jsdelivr.net; "
        . "connect-src 'self' https://cdn.jsdelivr.net; "                // AJAX a self + CDN source maps
        . "frame-ancestors 'none'; "                                    // Non embeddable
        . "base-uri 'self'; "
        . "form-action 'self'";                                         // Form submission solo a self
    
    header("Content-Security-Policy: " . $csp);
    
    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions Policy (ex Feature-Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// Carica helper per CSP
require_once __DIR__ . '/helpers_csp.php';

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