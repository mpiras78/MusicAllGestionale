<?php
/**
 * Aule Controller
 * Gestione logica business per aule
 */

class AuleController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutte le aule attive
     */
    public function getAule($attive_only = true) {
        $where = $attive_only ? "WHERE attiva = 1" : "";
        return $this->db->query("
            SELECT * FROM aule 
            $where 
            ORDER BY ordine_visualizzazione
        ");
    }
    
    /**
     * Ottiene tutte le aule (alias di getAule)
     */
    public function getAllAule() {
        return $this->getAule(false); // Tutte le aule, anche inattive
    }
    
    /**
     * Ottiene una singola aula per ID
     */
    public function getAulaById($id) {
        return $this->db->queryOne("SELECT * FROM aule WHERE id = ?", [$id]);
    }
}
