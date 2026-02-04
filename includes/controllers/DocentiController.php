<?php
/**
 * Docenti Controller
 * Gestione logica business per docenti
 */

class DocentiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutti i docenti attivi
     */
    public function getDocenti($attivi_only = true, $limit = null, $offset = 0) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        $limit_clause = $limit ? "LIMIT ? OFFSET ?" : "";
        
        $sql = "SELECT * FROM docenti $where ORDER BY cognome, nome $limit_clause";
        
        $params = [];
        if ($limit) {
            $params = [$limit, $offset];
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Ottiene un singolo docente per ID
     */
    public function getDocenteById($id) {
        return $this->db->queryOne("SELECT * FROM docenti WHERE id = ?", [$id]);
    }
    
    /**
     * Conta docenti attivi
     */
    public function countDocenti($attivi_only = true) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        return $this->db->count("SELECT COUNT(*) FROM docenti $where");
    }
    
    /**
     * Ottiene materie insegnate da un docente
     */
    public function getMaterieDocente($docente_id) {
        return $this->db->query("
            SELECT m.* 
            FROM materie m
            JOIN docenti_materie dm ON m.id = dm.materia_id
            WHERE dm.docente_id = ?
            ORDER BY m.nome
        ", [$docente_id]);
    }
    
    /**
     * Ottiene docenti per una materia
     */
    public function getDocentiPerMateria($materia_id) {
        return $this->db->query("
            SELECT d.* 
            FROM docenti d
            JOIN docenti_materie dm ON d.id = dm.docente_id
            WHERE dm.materia_id = ? AND d.attivo = 1
            ORDER BY d.cognome, d.nome
        ", [$materia_id]);
    }
}