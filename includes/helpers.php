<?php
/**
 * Helper Functions
 * Funzioni di utilità globali
 */

/**
 * Escape HTML per prevenire XSS
 * (Solo se non già definita da Laravel/Illuminate)
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Get parametro da GET con valore default
 */
if (!function_exists('get')) {
    function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }
}

/**
 * Get parametro da POST con valore default
 */
if (!function_exists('post')) {
    function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }
}

/**
 * Verifica se la richiesta è POST
 */
if (!function_exists('isPost')) {
    function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

/**
 * Verifica se la richiesta è GET
 */
if (!function_exists('isGet')) {
    function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

/**
 * Redirect a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Set flash message in session
 */
function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message from session
 */
function getFlashMessage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    
    return null;
}

/**
 * Check if flash message exists
 */
function hasFlashMessage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    return isset($_SESSION['flash_message']);
}

/**
 * Genera slot orari
 */
function generaSlotOrari($ora_inizio, $ora_fine, $durata_minuti = 15) {
    $slots = [];
    $current = strtotime($ora_inizio);
    $end = strtotime($ora_fine);
    
    while ($current < $end) {
        $next = strtotime("+{$durata_minuti} minutes", $current);
        $slots[] = [
            'inizio' => date('H:i:s', $current),
            'fine' => date('H:i:s', $next)
        ];
        $current = $next;
    }
    
    return $slots;
}

/**
 * Formatta data italiana
 */
function formatDataItaliana($data) {
    if (empty($data)) return '';
    
    $timestamp = is_numeric($data) ? $data : strtotime($data);
    return date('d/m/Y', $timestamp);
}

/**
 * Formatta data e ora italiana
 */
function formatDataOraItaliana($datetime) {
    if (empty($datetime)) return '';
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Calcola età da data nascita
 */
function calcolaEta($data_nascita) {
    if (empty($data_nascita)) return null;
    
    $nascita = new DateTime($data_nascita);
    $oggi = new DateTime();
    return $oggi->diff($nascita)->y;
}

/**
 * Ottieni nome giorno in italiano
 */
function getGiornoItaliano($data_o_giorno) {
    $giorni_eng_ita = [
        'Monday' => 'Lunedì',
        'Tuesday' => 'Martedì', 
        'Wednesday' => 'Mercoledì',
        'Thursday' => 'Giovedì',
        'Friday' => 'Venerdì',
        'Saturday' => 'Sabato',
        'Sunday' => 'Domenica'
    ];
    
    $giorni_ita_lower = [
        'lunedì' => 'Lunedì',
        'lunedi' => 'Lunedì',
        'martedì' => 'Martedì',
        'martedi' => 'Martedì',
        'mercoledì' => 'Mercoledì',
        'mercoledi' => 'Mercoledì',
        'giovedì' => 'Giovedì',
        'giovedi' => 'Giovedì',
        'venerdì' => 'Venerdì',
        'venerdi' => 'Venerdì',
        'sabato' => 'Sabato',
        'domenica' => 'Domenica'
    ];
    
    // Se è già un nome giorno in inglese
    if (isset($giorni_eng_ita[$data_o_giorno])) {
        return $giorni_eng_ita[$data_o_giorno];
    }
    
    // Se è già in italiano (normalizza)
    $lower = strtolower($data_o_giorno);
    if (isset($giorni_ita_lower[$lower])) {
        return $giorni_ita_lower[$lower];
    }
    
    // Se è una data, converti
    try {
        $dt = is_string($data_o_giorno) ? new DateTime($data_o_giorno) : $data_o_giorno;
        $giorno_eng = $dt->format('l');
        return $giorni_eng_ita[$giorno_eng] ?? $giorno_eng;
    } catch (Exception $e) {
        // Se fallisce, ritorna l'input originale
        return $data_o_giorno;
    }
}

/**
 * Ottieni nome mese in italiano
 */
function getMeseItaliano($mese_numero_o_data) {
    $mesi = [
        1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
        5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
        9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
    ];
    
    // Se è un numero (1-12)
    if (is_numeric($mese_numero_o_data)) {
        return $mesi[(int)$mese_numero_o_data] ?? '';
    }
    
    // Se è una data
    $dt = is_string($mese_numero_o_data) ? new DateTime($mese_numero_o_data) : $mese_numero_o_data;
    $mese_num = (int)$dt->format('n');
    
    return $mesi[$mese_num] ?? '';
}

/**
 * Verifica se una data è una festività italiana
 * Ritorna array con info festività o false
 */
function isFestivitaItaliana($data) {
    // Converti in DateTime se è stringa
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    
    $anno = (int)$data->format('Y');
    $mese = (int)$data->format('m');
    $giorno = (int)$data->format('d');
    
    // Festività fisse
    $festivita_fisse = [
        '01-01' => '🎊 Capodanno',
        '01-06' => '🌟 Epifania',
        '04-25' => '🇮🇹 Festa della Liberazione',
        '05-01' => '⚒️ Festa dei Lavoratori',
        '06-02' => '🇮🇹 Festa della Repubblica',
        '08-15' => '⛪ Ferragosto',
        '11-01' => '🕯️ Ognissanti',
        '12-08' => '⛪ Immacolata Concezione',
        '12-25' => '🎄 Natale',
        '12-26' => '🎁 Santo Stefano'
    ];
    
    $chiave = sprintf('%02d-%02d', $mese, $giorno);
    
    if (isset($festivita_fisse[$chiave])) {
        return [
            'nome' => $festivita_fisse[$chiave],
            'tipo' => 'nazionale',
            'data' => $data->format('Y-m-d')
        ];
    }
    
    // Pasqua e Lunedì dell'Angelo (mobili)
    $pasqua = easter_date($anno);
    $data_pasqua = date('Y-m-d', $pasqua);
    $lunedi_angelo = date('Y-m-d', strtotime('+1 day', $pasqua));
    
    if ($data->format('Y-m-d') == $data_pasqua) {
        return [
            'nome' => '🐣 Pasqua',
            'tipo' => 'nazionale',
            'data' => $data_pasqua
        ];
    }
    
    if ($data->format('Y-m-d') == $lunedi_angelo) {
        return [
            'nome' => '🐰 Lunedì dell\'Angelo',
            'tipo' => 'nazionale',
            'data' => $lunedi_angelo
        ];
    }
    
    return false;
}

/**
 * Ottieni tutte le festività per un mese
 */
function getFestivitaMese($anno, $mese) {
    $festivita = [];
    $giorni_mese = cal_days_in_month(CAL_GREGORIAN, $mese, $anno);
    
    for ($giorno = 1; $giorno <= $giorni_mese; $giorno++) {
        $data = new DateTime("$anno-$mese-$giorno");
        $festivity = isFestivitaItaliana($data);
        if ($festivity) {
            $festivita[$data->format('Y-m-d')] = $festivity;
        }
    }
    
    return $festivita;
}

/**
 * Ottieni tutte le festività per un anno
 */
function getFestivitaAnno($anno) {
    $festivita = [];
    
    for ($mese = 1; $mese <= 12; $mese++) {
        $festivita = array_merge($festivita, getFestivitaMese($anno, $mese));
    }
    
    return $festivita;
}

/**
 * Verifica se la scuola è aperta in una data
 */
function isScuolaAperta($data) {
    // Verifica festività
    if (isFestivitaItaliana($data)) {
        return false;
    }
    
    // Verifica domenica
    $dt = is_string($data) ? new DateTime($data) : $data;
    if ($dt->format('N') == 7) { // 7 = Domenica
        return false;
    }
    
    // TODO: Aggiungere controllo periodi ferie scuola (estate, Natale, etc.)
    
    return true;
}