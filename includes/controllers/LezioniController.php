<?php
/**
 * Lezioni Controller
 * Gestione logica business per lezioni
 */

class LezioniController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutte le lezioni
     */
    public function getLezioni($attive_only = true, $limit = null) {
        $where = $attive_only ? "WHERE l.attiva = 1" : "";
        $limit_clause = $limit ? "LIMIT ?" : "";
        
        $sql = "SELECT l.*, 
                al.cognome || ' ' || al.nome as allievo,
                d.cognome || ' ' || d.nome as docente,
                m.nome as materia,
                a.nome as aula
            FROM lezioni l
            JOIN allievi al ON l.allievo_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            $where
            ORDER BY l.giorno_settimana, l.ora_inizio
            $limit_clause";
        
        $params = $limit ? [$limit] : [];
        return $this->db->query($sql, $params);
    }
    
    /**
     * Ottiene lezioni per un giorno specifico
     */
    public function getLezioniPerGiorno($giorno, $attive_only = true) {
        $where = $attive_only ? "AND l.attiva = 1" : "";
        
        return $this->db->query("
            SELECT l.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia,
                   a.nome as aula
            FROM lezioni l
            JOIN allievi al ON l.allievo_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.giorno_settimana = ? $where
            ORDER BY l.ora_inizio
        ", [$giorno]);
    }
    
    /**
     * Ottiene prossime lezioni per oggi
     */
    public function getProssimeLezioniOggi($giorno, $limit = 5) {
        return $this->db->query("
            SELECT l.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia,
                   a.nome as aula
            FROM lezioni l
            JOIN allievi al ON l.allievo_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.attiva = 1 
            AND l.giorno_settimana = ?
            ORDER BY l.ora_inizio
            LIMIT ?
        ", [$giorno, $limit]);
    }
    
    /**
     * Conta lezioni settimanali
     */
    public function countLezioniSettimana($attive_only = true) {
        $where = $attive_only ? "WHERE attiva = 1" : "";
        return $this->db->count("SELECT COUNT(*) FROM lezioni $where");
    }
    
    /**
     * Ottiene lezioni per allievo
     */
    public function getLezioniAllievo($allievo_id) {
        return $this->db->query("
            SELECT l.*, 
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia,
                   a.nome as aula
            FROM lezioni l
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.allievo_id = ? AND l.attiva = 1
            ORDER BY l.giorno_settimana, l.ora_inizio
        ", [$allievo_id]);
    }
    
    /**
     * Ottiene lezioni per docente
     */
    public function getLezioniDocente($docente_id) {
        return $this->db->query("
            SELECT l.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   m.nome as materia,
                   a.nome as aula
            FROM lezioni l
            JOIN allievi al ON l.allievo_id = al.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.docente_id = ? AND l.attiva = 1
            ORDER BY l.giorno_settimana, l.ora_inizio
        ", [$docente_id]);
    }
    
    /**
     * Ottiene lezioni per aula
     */
    public function getLezioniAula($aula_id, $giorno = null) {
        $where_giorno = $giorno ? "AND l.giorno_settimana = ?" : "";
        $params = $giorno ? [$aula_id, $giorno] : [$aula_id];
        
        return $this->db->query("
            SELECT l.*, 
                   al.cognome || ' ' || al.nome as allievo,
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia
            FROM lezioni l
            JOIN allievi al ON l.allievo_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.aula_id = ? AND l.attiva = 1 $where_giorno
            ORDER BY l.giorno_settimana, l.ora_inizio
        ", $params);
    }
}