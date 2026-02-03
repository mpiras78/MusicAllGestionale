<?php
/**
 * Helper Functions
 * Funzioni di utilità globali
 */

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Formatta data italiana
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatta ora
 */
function formatTime($time) {
    if (empty($time)) return '';
    return date('H:i', strtotime($time));
}

/**
 * Formatta data e ora
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Ottiene il giorno della settimana in italiano
 */
function getGiornoItaliano($giorno_settimana) {
    return GIORNI_SETTIMANA[$giorno_settimana] ?? $giorno_settimana;
}

/**
 * Genera select options
 */
function selectOptions($items, $value_field, $label_field, $selected = null) {
    $html = '';
    foreach ($items as $item) {
        $value = $item[$value_field];
        $label = $item[$label_field];
        $selected_attr = ($value == $selected) ? 'selected' : '';
        $html .= sprintf('<option value="%s" %s>%s</option>', e($value), $selected_attr, e($label));
    }
    return $html;
}

/**
 * Mostra messaggio flash
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Ottiene e rimuove messaggio flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Ottiene parametro POST in modo sicuro
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Ottiene parametro GET in modo sicuro
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Verifica se la richiesta è POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica se la richiesta è AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Risposta JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Calcola durata in minuti tra due orari
 */
function calcolaDurata($ora_inizio, $ora_fine) {
    $start = strtotime($ora_inizio);
    $end = strtotime($ora_fine);
    return ($end - $start) / 60;
}

/**
 * Genera slot orari
 */
function generaSlotOrari($ora_inizio = '09:15', $ora_fine = '22:00', $durata = 45) {
    $slots = [];
    $current = strtotime($ora_inizio);
    $end = strtotime($ora_fine);
    
    while ($current < $end) {
        $next = strtotime("+{$durata} minutes", $current);
        $slots[] = [
            'inizio' => date('H:i', $current),
            'fine' => date('H:i', $next)
        ];
        $current = $next;
    }
    
    return $slots;
}

/**
 * Valida email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string
 */
function sanitize($string) {
    return filter_var($string, FILTER_SANITIZE_STRING);
}

/**
 * Tronca testo
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Badge colore per categoria materia
 */
function getCategoriaBadge($categoria) {
    $badges = [
        'strumento' => 'primary',
        'canto' => 'success',
        'teoria' => 'info',
        'insieme' => 'warning',
        'laboratorio' => 'secondary',
        'custom' => 'dark'
    ];
    return $badges[$categoria] ?? 'secondary';
}

/**
 * Badge colore per tipo lezione
 */
function getTipoLezioneBadge($tipo) {
    $badges = [
        'regolare' => 'primary',
        'custom' => 'warning',
        'recupero' => 'success',
        'laboratorio' => 'info'
    ];
    return $badges[$tipo] ?? 'secondary';
}

/**
 * Formatta nome completo
 */
function nomeCompleto($cognome, $nome) {
    return trim($cognome . ' ' . $nome);
}

/**
 * Genera colore casuale per eventi calendario
 */
function getRandomColor($seed = null) {
    $colors = [
        '#3788d8', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
        '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6610f2'
    ];
    
    if ($seed !== null) {
        return $colors[$seed % count($colors)];
    }
    
    return $colors[array_rand($colors)];
}

/**
 * Debug print
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Log personalizzato
 */
function logMessage($message, $file = 'app.log') {
    $log_file = BASE_PATH . '/logs/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Genera password casuale
 */
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
}

/**
 * Ottiene array giorni settimana
 */
function getGiorniSettimana() {
    return GIORNI_SETTIMANA;
}

/**
 * Controlla se una data è nel weekend
 */
function isWeekend($date) {
    $day = date('N', strtotime($date));
    return $day >= 6; // 6 = Sabato, 7 = Domenica
}