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
     * Ottiene tutti i docenti attivi con le loro materie
     */
    public function getDocenti($attivi_only = true, $limit = null, $offset = 0) {
        $where = $attivi_only ? "WHERE d.attivo = 1" : "";
        $limit_clause = $limit ? "LIMIT ? OFFSET ?" : "";
        
        $sql = "SELECT 
                    d.*,
                    COALESCE(GROUP_CONCAT(m.nome, ', '), d.specializzazioni) as materie
                FROM docenti d
                LEFT JOIN docenti_materie dm ON d.id = dm.docente_id
                LEFT JOIN materie m ON dm.materia_id = m.id AND m.attiva = 1
                $where
                GROUP BY d.id, d.cognome, d.nome, d.email, d.telefono, d.specializzazioni, d.note, d.user_id, d.attivo, d.created_at
                ORDER BY d.cognome, d.nome $limit_clause";
        
        $params = [];
        if ($limit) {
            $params = [$limit, $offset];
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Alias per getDocenti - per compatibilità
     */
    public function getAllDocenti($attivi_only = true) {
        return $this->getDocenti($attivi_only);
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
            specializzazioni, note, attivo, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 1, datetime('now'))";
        
        $docente_id = $this->db->insert($sql, [
            $data['nome'],
            $data['cognome'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['specializzazioni'] ?? null,
            $data['note'] ?? null
        ]);
        
        // Salva materie se presenti
        if ($docente_id && !empty($data['materie'])) {
            $this->salvaMaterie($docente_id, $data['materie']);
        }
        
        return $docente_id;
    }
    
    /**
     * Aggiorna docente
     */
    public function updateDocente($id, $data) {
        $sql = "UPDATE docenti SET 
            nome = ?, cognome = ?, email = ?, 
            telefono = ?, specializzazioni = ?, note = ?
            WHERE id = ?";
        
        $result = $this->db->execute($sql, [
            $data['nome'],
            $data['cognome'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['specializzazioni'] ?? null,
            $data['note'] ?? null,
            $id
        ]);
        
        // Aggiorna materie se presenti
        if (isset($data['materie'])) {
            $this->salvaMaterie($id, $data['materie']);
        }
        
        return $result;
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
    
    /**
     * Salva materie docente (sostituisce tutte le materie esistenti)
     */
    private function salvaMaterie($docente_id, $materie_ids) {
        // Rimuovi tutte le materie esistenti
        $this->db->execute("DELETE FROM docenti_materie WHERE docente_id = ?", [$docente_id]);
        
        // Inserisci nuove materie
        if (!empty($materie_ids) && is_array($materie_ids)) {
            foreach ($materie_ids as $materia_id) {
                $this->db->execute(
                    "INSERT INTO docenti_materie (docente_id, materia_id) VALUES (?, ?)",
                    [$docente_id, $materia_id]
                );
            }
        }
    }
    
    /**
     * Ottieni IDs materie di un docente
     */
    public function getMaterieIds($docente_id) {
        $result = $this->db->query(
            "SELECT materia_id FROM docenti_materie WHERE docente_id = ?",
            [$docente_id]
        );
        return array_column($result, 'materia_id');
    }
}
