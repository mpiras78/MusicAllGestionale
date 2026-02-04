<?php
/**
 * Assenze Controller
 * Gestione logica business per assenze
 */

class AssenzeController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene assenze da recuperare
     */
    public function getAssenzeDaRecuperare($limit = null) {
        $limit_clause = $limit ? "LIMIT ?" : "";
        $params = $limit ? [$limit] : [];
        
        return $this->db->query("
            SELECT a.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   d.cognome || ' ' || d.nome as docente
            FROM assenze a
            JOIN allievi al ON a.allievo_id = al.id
            JOIN docenti d ON a.docente_id = d.id
            WHERE a.recuperata = 0 
            AND a.da_recuperare = 1
            ORDER BY a.data_assenza DESC
            $limit_clause
        ", $params);
    }
    
    /**
     * Conta assenze del mese corrente
     */
    public function countAssenzeMeseCorrente() {
        // Query database-agnostica per contare assenze del mese
        return $this->db->count("
            SELECT COUNT(*) FROM assenze 
            WHERE strftime('%Y-%m', data_assenza) = strftime('%Y-%m', 'now')
        ");
    }
    
    /**
     * Ottiene assenze per allievo
     */
    public function getAssenzeAllievo($allievo_id, $limit = null) {
        $limit_clause = $limit ? "LIMIT ?" : "";
        $params = $limit ? [$allievo_id, $limit] : [$allievo_id];
        
        return $this->db->query("
            SELECT a.*, 
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia
            FROM assenze a
            JOIN docenti d ON a.docente_id = d.id
            LEFT JOIN materie m ON a.materia_id = m.id
            WHERE a.allievo_id = ?
            ORDER BY a.data_assenza DESC
            $limit_clause
        ", $params);
    }
    
    /**
     * Ottiene assenze per docente
     */
    public function getAssenzeDocente($docente_id, $limit = null) {
        $limit_clause = $limit ? "LIMIT ?" : "";
        $params = $limit ? [$docente_id, $limit] : [$docente_id];
        
        return $this->db->query("
            SELECT a.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   m.nome as materia
            FROM assenze a
            JOIN allievi al ON a.allievo_id = al.id
            LEFT JOIN materie m ON a.materia_id = m.id
            WHERE a.docente_id = ?
            ORDER BY a.data_assenza DESC
            $limit_clause
        ", $params);
    }
}