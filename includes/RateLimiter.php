<?php
/**
 * Rate Limiter
 * Protezione contro brute force e abusi
 */

class RateLimiter {
    private $db;
    private $limits = [
        'login' => ['max' => 5, 'window' => 900],      // 5 tentativi in 15 min
        'api' => ['max' => 100, 'window' => 60],       // 100 richieste al minuto
        'password_reset' => ['max' => 3, 'window' => 3600], // 3 reset in 1 ora
    ];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->createTableIfNotExists();
    }
    
    /**
     * Verifica se l'azione è permessa
     */
    public function check($identifier, $action = 'api') {
        $limit = $this->limits[$action] ?? $this->limits['api'];
        
        // Conta tentativi nella finestra temporale
        $sql = "SELECT COUNT(*) as attempts 
                FROM rate_limit_log 
                WHERE identifier = ? 
                AND action = ? 
                AND timestamp > datetime('now', '-{$limit['window']} seconds')";
        
        $result = $this->db->queryOne($sql, [$identifier, $action]);
        
        if ($result['attempts'] >= $limit['max']) {
            return false; // Limite superato
        }
        
        return true;
    }
    
    /**
     * Registra tentativo
     */
    public function hit($identifier, $action = 'api', $metadata = null) {
        $sql = "INSERT INTO rate_limit_log (identifier, action, metadata, timestamp) 
                VALUES (?, ?, ?, datetime('now'))";
        
        return $this->db->execute($sql, [
            $identifier,
            $action,
            $metadata ? json_encode($metadata) : null
        ]);
    }
    
    /**
     * Ottieni tempo rimanente di blocco
     */
    public function getTimeRemaining($identifier, $action = 'api') {
        $limit = $this->limits[$action] ?? $this->limits['api'];
        
        $sql = "SELECT timestamp 
                FROM rate_limit_log 
                WHERE identifier = ? AND action = ?
                ORDER BY timestamp DESC 
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, [$identifier, $action]);
        
        if (!$result) {
            return 0;
        }
        
        $lastAttempt = strtotime($result['timestamp']);
        $windowEnd = $lastAttempt + $limit['window'];
        $remaining = $windowEnd - time();
        
        return max(0, $remaining);
    }
    
    /**
     * Pulisci vecchi record (esegui periodicamente)
     */
    public function cleanup($days = 7) {
        $sql = "DELETE FROM rate_limit_log 
                WHERE timestamp < datetime('now', '-{$days} days')";
        
        return $this->db->execute($sql);
    }
    
    /**
     * Crea tabella se non esiste
     */
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS rate_limit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            identifier VARCHAR(255) NOT NULL,
            action VARCHAR(50) NOT NULL,
            metadata TEXT,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->execute($sql);
        
        // Crea indici
        $this->db->execute("CREATE INDEX IF NOT EXISTS idx_rate_limit_identifier_action 
                           ON rate_limit_log(identifier, action)");
        $this->db->execute("CREATE INDEX IF NOT EXISTS idx_rate_limit_timestamp 
                           ON rate_limit_log(timestamp)");
    }
    
    /**
     * Blocca richiesta se limite superato
     */
    public function enforce($identifier, $action = 'api') {
        if (!$this->check($identifier, $action)) {
            $remaining = $this->getTimeRemaining($identifier, $action);
            $minutes = ceil($remaining / 60);
            
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => "Troppi tentativi. Riprova tra {$minutes} minuti.",
                'retry_after' => $remaining
            ]);
            exit;
        }
        
        $this->hit($identifier, $action);
    }
}
