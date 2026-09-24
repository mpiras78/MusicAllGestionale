<?php
/**
 * Helper per gestire script inline con nonce per CSP compliance
 * Usage: <?= html_script('console.log("hello");') ?>
 */

if (!function_exists('html_script')) {
    /**
     * Genera un tag <script> con nonce CSP valido
     * 
     * @param string $code Il codice JavaScript
     * @param bool $echo Se true, stampa il tag; se false lo restituisce
     * @return string|void
     */
    function html_script($code, $echo = true) {
        // Assicura che la sessione sia inizializzata
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Usa il nonce dalla sessione
        $nonce = $_SESSION['csp_nonce'] ?? bin2hex(random_bytes(16));
        
        $script = "<script nonce=\"{$nonce}\">\n{$code}\n</script>";
        
        if ($echo) {
            echo $script;
        } else {
            return $script;
        }
    }
}
