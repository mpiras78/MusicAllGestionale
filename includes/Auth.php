<?php
/**
 * Authentication Class
 * Gestione autenticazione e sessioni
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->startSession();
    }
    
    /**
     * Inizia la sessione
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
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
                "UPDATE users SET last_login = NOW() WHERE id = ?",
                [$user['id']]
            );
            
            // Salva dati in sessione
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
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
        if ($this->isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $this->logActivity($user_id, 'logout', null, null, 'Logout effettuato');
        }
        
        $_SESSION = [];
        session_destroy();
        
        return true;
    }
    
    /**
     * Verifica se l'utente è loggato
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
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
            header('Location: ' . BASE_URL . '/login.php');
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
        
        $sql = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            $description,
            $ip
        ]);
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