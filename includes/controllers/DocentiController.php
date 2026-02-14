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

    /**
     * Ottiene docente per user_id
     */
    public function getDocenteByUserId($user_id) {
        return $this->db->queryOne("SELECT * FROM docenti WHERE user_id = ?", [$user_id]);
    }

    /**
     * Crea nuovo docente
     */
    public function createDocente($data) {
        $sql = "INSERT INTO docenti (
            nome, cognome, email, telefono, 
            indirizzo, note, attivo, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 1, datetime('now'))";
        
        return $this->db->insert($sql, [
            $data['nome'],
            $data['cognome'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['indirizzo'] ?? null,
            $data['note'] ?? null
        ]);
    }
    
    /**
     * Aggiorna docente
     */
    public function updateDocente($id, $data) {
        $sql = "UPDATE docenti SET 
            nome = ?, cognome = ?, email = ?, 
            telefono = ?, indirizzo = ?, note = ?
            WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['nome'],
            $data['cognome'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['indirizzo'] ?? null,
            $data['note'] ?? null,
            $id
        ]);
    }
    
    /**
     * Disattiva docente
     */
    public function disattivaDocente($id) {
        return $this->db->execute(
            "UPDATE docenti SET attivo = 0 WHERE id = ?",
            [$id]
        );
    }

    /**
     * Cerca docenti per nome/cognome
     */
    public function searchDocenti($query) {
        $search = "%$query%";
        return $this->db->query("
            SELECT * FROM docenti 
            WHERE (nome LIKE ? OR cognome LIKE ? OR email LIKE ?)
            AND attivo = 1
            ORDER BY cognome, nome
        ", [$search, $search, $search]);
    }

    /**
     * Ottieni docenti con numero lezioni
     */
    public function getDocentiConLezioni() {
        return $this->db->query("
            SELECT 
                d.id,
                d.cognome || ' ' || d.nome as nome_completo,
                COUNT(DISTINCT l.id) as num_lezioni,
                GROUP_CONCAT(DISTINCT m.nome) as materie,
                GROUP_CONCAT(DISTINCT l.giorno_settimana) as giorni
            FROM docenti d
            JOIN lezioni l ON d.id = l.docente_id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.attiva = 1 AND d.attivo = 1
            GROUP BY d.id, d.cognome, d.nome
            ORDER BY d.cognome, d.nome
        ");
    }

    /**
     * Statistiche docenti con/senza lezioni
     */
    public function getStatisticheLezioni() {
        $totale = $this->countDocenti(true);
        $docenti_con_lezioni = $this->getDocentiConLezioni();
        $con_lezioni = is_array($docenti_con_lezioni) ? count($docenti_con_lezioni) : 0;
        
        return [
            'totale' => $totale,
            'con_lezioni' => $con_lezioni,
            'senza_lezioni' => $totale - $con_lezioni,
            'percentuale' => $totale > 0 ? round(($con_lezioni / $totale) * 100, 1) : 0
        ];
    }
}
