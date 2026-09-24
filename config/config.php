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
define('APP_VERSION', '2.2.1');
define('BASE_URL', 'http://localhost:8000');
define('BASE_PATH', dirname(__DIR__));
define('LOG_PATH', BASE_PATH . '/logs');

// Sicurezza
define('SESSION_NAME', 'MUSICALL_SESSION');
define('SESSION_LIFETIME', 1800);  // Ridotto da 28800 (8h) a 1800 (30min) per OWASP compliance
                                    // REASON: Session lunga = window più ampio per hijacking
                                    // SEVERITY: CRITICAL - sec-002
define('PASSWORD_MIN_LENGTH', 12);  // Aumentato da 6 per OWASP 2021 compliance
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SYMBOLS', true);

// Upload
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Paginazione
define('ITEMS_PER_PAGE', 20);

// Email
define('EMAIL_FROM', 'noreply@musicall.it');
define('EMAIL_FROM_NAME', 'MusicAll');
define('APP_URL', BASE_URL); // URL per link nelle email

// ========================================
// PERMESSI RUOLO DOCENTE
// ========================================

// Visualizzazione Calendario
define('DOCENTE_VIEW_ALL_CALENDAR', false);  // false = solo proprie lezioni, true = tutte le lezioni

// Modifica Lezioni
define('DOCENTE_CAN_EDIT_LESSONS', false);   // false = solo lettura, true = può modificare

// Gestione Assenze
define('DOCENTE_CAN_VIEW_ABSENCES', true);   // true = può vedere assenze dei propri soci

// Gestione Soci
define('DOCENTE_CAN_VIEW_STUDENTS', true);   // true = può vedere lista soci delle proprie lezioni

// SMTP (se necessario per email avanzate)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

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
define('DURATA_SLOT_DEFAULT', 15); // minuti (granularità del calendario)

// Modalità Debug - Forza false in produzione per sicurezza
// REASON: DEBUG_MODE=true espone stack trace, file paths, DB queries ai visitatori
// SEVERITY: CRITICAL - Information Disclosure
define('DEBUG_MODE', getenv('APP_ENV') === 'local' ? true : false);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 0);  // Non loggare durante debug (development)
} else {
    error_reporting(0);
    ini_set('display_errors', 0);  // Non mostrare errori (production safe)
    ini_set('log_errors', 1);       // Loggare in file invece
    ini_set('error_log', LOG_PATH . '/php_errors.log');  // File errors invece di browser
}