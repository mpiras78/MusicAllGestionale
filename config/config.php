<?php
/**
 * MusicAll - Configuration File
 * Configurazione principale dell'applicazione
 */

// Timezone
date_default_timezone_set('Europe/Rome');

// Configurazione Database
//define('DB_HOST', 'localhost');
//define('DB_NAME', 'musicall');
//define('DB_USER', 'root');
//define('DB_PASS', '');
//define('DB_CHARSET', 'utf8mb4');
define('DB_DRIVER', 'sqlite');
define('DB_PATH', __DIR__ . '/../database/musicall.sqlite');


// Configurazione Applicazione
define('APP_NAME', 'MusicAll');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/musicall');
define('BASE_PATH', dirname(__DIR__));

// Sicurezza
define('SESSION_NAME', 'MUSICALL_SESSION');
define('SESSION_LIFETIME', 3600 * 8); // 8 ore
define('PASSWORD_MIN_LENGTH', 6);

// Upload
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Paginazione
define('ITEMS_PER_PAGE', 20);

// Email (se necessario)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@musicall.it');

// Giorni della settimana
define('GIORNI_SETTIMANA', [
    'lunedi' => 'Lunedì',
    'martedi' => 'Martedì',
    'mercoledi' => 'Mercoledì',
    'giovedi' => 'Giovedì',
    'venerdi' => 'Venerdì',
    'sabato' => 'Sabato',
    'domenica' => 'Domenica'
]);

// Orari
define('ORA_INIZIO_SCUOLA', '09:15');
define('ORA_FINE_SCUOLA', '22:00');
define('DURATA_SLOT_DEFAULT', 45); // minuti

// Modalità Debug
define('DEBUG_MODE', true);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}