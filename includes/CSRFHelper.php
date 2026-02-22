<?php
/**
 * CSRF Helper
 * Gestione centralizzata protezione CSRF
 */

class CSRFHelper {
    /**
     * Genera token CSRF
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verifica token CSRF
     */
    public static function verifyToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Genera campo hidden per form
     */
    public static function field() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Verifica token da richiesta POST/JSON
     */
    public static function verify() {
        $token = null;
        
        // Controlla POST
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }
        // Controlla JSON body
        elseif ($_SERVER['CONTENT_TYPE'] === 'application/json') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $token = $data['csrf_token'] ?? null;
        }
        // Controlla header
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        
        if (!$token || !self::verifyToken($token)) {
            http_response_code(403);
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
            } else {
                die('Token CSRF non valido');
            }
            exit;
        }
        
        return true;
    }
}
