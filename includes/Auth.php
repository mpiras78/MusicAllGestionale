<?php
/**
 * Authentication Class
 * Gestione autenticazione e sessioni
 */

class Auth {
    private $db;
    
    public function __construct() {
        // Inizializza database
        $this->db = Database::getInstance();
        $this->startSession();
    }
    
    /**
     * Inizia la sessione
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configura secure cookie flags per production
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => !DEBUG_MODE,      // HTTPS only in production
                'httponly' => true,            // Non accessibile da JavaScript (XSS protection) - sec-002
                'samesite' => 'Strict'         // CSRF protection - sec-002
            ]);
            
            session_name(SESSION_NAME);
            session_start();
            
            // Rigenera l'ID di sessione periodicamente per sicurezza
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }
    
    /**
     * Login utente
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1";
        $user = $this->db->queryOne($sql, [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Aggiorna ultimo login
            $this->db->execute(
                "UPDATE users SET last_login = datetime('now') WHERE id = ?",
                [$user['id']]
            );
            
            // Salva dati in sessione
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time(); // Inizializza timeout
            
            // Log attività
            $this->logActivity($user['id'], 'login', null, null, 'Login effettuato');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Logout utente
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $this->logActivity($user_id, 'logout', null, null, 'Logout effettuato');
        }
        
        $_SESSION = [];
        session_destroy();
        
        return true;
    }
    
    /**
     * Verifica se l'utente è autenticato
     * Controlla anche timeout sessione secondo SESSION_LIFETIME
     * REASON: sec-002 - SESSION_LIFETIME ridotto da 8h a 30min per OWASP compliance
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verifica timeout sessione con SESSION_LIFETIME
        if (isset($_SESSION['last_activity'])) {
            $timeout = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : (15 * 60);
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > $timeout) {
                // Sessione scaduta - salva flag per messaggio
                $_SESSION['session_timeout'] = true;
                $this->logout();
                return false;
            }
        }
        
        // Verifica anche login_time per protezione ulteriore
        if (isset($_SESSION['login_time'])) {
            $maxSessionAge = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : (15 * 60);
            $sessionAge = time() - $_SESSION['login_time'];
            
            if ($sessionAge > $maxSessionAge) {
                $_SESSION['session_expired'] = true;
                $this->logout();
                return false;
            }
        }
        
        // Aggiorna timestamp ultima attività
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Verifica se l'utente ha un determinato ruolo
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($_SESSION['role'], $role);
        }
        
        return $_SESSION['role'] === $role;
    }
    
    /**
     * Verifica se l'utente è admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Ottiene l'ID dell'utente corrente
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Ottiene i dati dell'utente corrente
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $sql = "SELECT id, username, email, role, created_at, last_login 
                FROM users WHERE id = ? AND active = 1";
        return $this->db->queryOne($sql, [$this->getUserId()]);
    }
    
    /**
     * Cambia password
     */
    public function changePassword($user_id, $old_password, $new_password) {
        $user = $this->db->queryOne("SELECT password FROM users WHERE id = ?", [$user_id]);
        
        if (!$user || !password_verify($old_password, $user['password'])) {
            return false;
        }
        
        if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            return false;
        }
        
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $result = $this->db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashed, $user_id]
        );
        
        if ($result) {
            $this->logActivity($user_id, 'password_change', 'users', $user_id, 'Password modificata');
        }
        
        return $result;
    }
    
    /**
     * Registra un nuovo utente
     */
    public function register($username, $password, $email, $role = 'segreteria') {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return false;
        }
        
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, email, role, active) 
                VALUES (?, ?, ?, ?, 1)";
        
        $user_id = $this->db->insert($sql, [$username, $hashed, $email, $role]);
        
        if ($user_id) {
            $this->logActivity($this->getUserId(), 'user_create', 'users', $user_id, "Utente $username creato");
        }
        
        return $user_id;
    }
    
    /**
     * Richiedi il login (redirect se non loggato)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Controlla se sessione scaduta
            $timeout = isset($_SESSION['session_timeout']) ? '?timeout=1' : '';
            header('Location: ' . BASE_URL . '/login.php' . $timeout);
            exit;
        }
    }
    
    /**
     * Richiedi un determinato ruolo
     */
    public function requireRole($role) {
        $this->requireLogin();
        
        if (!$this->hasRole($role)) {
            header('Location: ' . BASE_URL . '/index.php?error=access_denied');
            exit;
        }
    }
    
    /**
     * Log delle attività
     */
    public function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $description = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
         
        $sql = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
         
        $result = $this->db->execute($sql, [
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            $description,
            $ip,
            $userAgent
        ]);
         
        // Aggiungi logging di sicurezza per eventi critici (sec-009)
        // REASON: sec-009 - Loggare tutti gli eventi di sicurezza per audit trail
        // SEVERITY: HIGH - sec-009
        $securityActions = ['login', 'logout', 'login_failure', 'password_change', 'user_create', 'user_delete', 'role_change'];
         
        if (in_array($action, $securityActions)) {
            $this->logSecurityEvent($user_id, $action, $entity_type, $entity_id, $description, $ip);
        }
         
        return $result;
    }
     
    /**
     * Log di sicurezza - Registra eventi di sicurezza critici (sec-009)
     */
    private function logSecurityEvent($user_id, $action, $entity_type, $entity_id, $description, $ip) {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $severity = 'MEDIUM';
             
            // Determina severity
            if (in_array($action, ['login_failure', 'password_change'])) {
                $severity = 'HIGH';
            } elseif (in_array($action, ['user_delete', 'role_change'])) {
                $severity = 'CRITICAL';
            }
             
            $sql = "INSERT INTO security_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent, severity, timestamp) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
             
            $this->db->execute($sql, [
                $user_id,
                $action,
                $entity_type,
                $entity_id,
                $description,
                $ip,
                $userAgent,
                $severity,
                $timestamp
            ]);
        } catch (Exception $e) {
            error_log("SecurityLog Error: " . $e->getMessage());
        }
    }
    
    /**
     * Genera token CSRF
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verifica token CSRF
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}