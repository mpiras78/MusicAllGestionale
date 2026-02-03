<?php
/**
 * Database Class
 * Gestione connessione e query al database
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Errore di connessione al database: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Esegue una query di selezione
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            return false;
        }
    }
    
    /**
     * Esegue una query che ritorna una singola riga
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            return false;
        }
    }
    
    /**
     * Esegue una query di modifica (INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            return false;
        }
    }
    
    /**
     * Inserisce un record e ritorna l'ID
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            return false;
        }
    }
    
    /**
     * Conta i record
     */
    public function count($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            return 0;
        }
    }
    
    /**
     * Inizia una transazione
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Conferma una transazione
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Annulla una transazione
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Log degli errori
     */
    private function logError($exception, $sql, $params) {
        if (DEBUG_MODE) {
            error_log("Database Error: " . $exception->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
        }
    }
    
    /**
     * Previene la clonazione
     */
    private function __clone() {}
    
    /**
     * Previene l'unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}