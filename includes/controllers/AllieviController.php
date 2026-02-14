<?php
/**
 * Allievi Controller
 * Gestione logica business per allievi
 */

class AllieviController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutti gli allievi attivi
     */
    public function getAllievi($attivi_only = true, $limit = null, $offset = 0) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        $limit_clause = $limit ? "LIMIT ? OFFSET ?" : "";
        
        $sql = "SELECT * FROM allievi $where ORDER BY cognome, nome $limit_clause";
        
        $params = [];
        if ($limit) {
            $params = [$limit, $offset];
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Ottiene un singolo allievo per ID
     */
    public function getAllievoById($id) {
        return $this->db->queryOne("SELECT * FROM allievi WHERE id = ?", [$id]);
    }
    
    /**
     * Ottiene ultimi allievi aggiunti
     */
    public function getUltimiAllievi($limit = 5) {
        return $this->db->query("
            SELECT * FROM allievi 
            WHERE attivo = 1 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Conta allievi attivi
     */
    public function countAllievi($attivi_only = true) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        return $this->db->count("SELECT COUNT(*) FROM allievi $where");
    }
    
    /**
     * Cerca allievi per nome/cognome
     */
    public function searchAllievi($query) {
        $search = "%$query%";
        return $this->db->query("
            SELECT * FROM allievi 
            WHERE (nome LIKE ? OR cognome LIKE ? OR email LIKE ?)
            AND attivo = 1
            ORDER BY cognome, nome
        ", [$search, $search, $search]);
    }
    
    /**
     * Crea nuovo allievo
     */
    public function createAllievo($data) {
        $sql = "INSERT INTO allievi (
            nome, cognome, data_nascita, email, telefono, 
            indirizzo, note, attivo, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))";
        
        return $this->db->insert($sql, [
            $data['nome'],
            $data['cognome'],
            $data['data_nascita'] ?? null,
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['indirizzo'] ?? null,
            $data['note'] ?? null
        ]);
    }
    
    /**
     * Aggiorna allievo
     */
    public function updateAllievo($id, $data) {
        $sql = "UPDATE allievi SET 
            nome = ?, cognome = ?, data_nascita = ?, email = ?, 
            telefono = ?, indirizzo = ?, note = ?
            WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['nome'],
            $data['cognome'],
            $data['data_nascita'] ?? null,
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['indirizzo'] ?? null,
            $data['note'] ?? null,
            $id
        ]);
    }
    
    /**
     * Disattiva allievo
     */
    public function disattivaAllievo($id) {
        return $this->db->execute(
            "UPDATE allievi SET attivo = 0 WHERE id = ?",
            [$id]
        );
    }

    /**
     * Ottieni allievi con lezioni programmate
     */
    public function getAllieviConLezioni() {
        return $this->db->query("
            SELECT 
                a.id,
                a.cognome || ' ' || a.nome as nome_completo,
                COUNT(DISTINCT l.id) as num_lezioni,
                GROUP_CONCAT(DISTINCT m.nome) as materie,
                GROUP_CONCAT(DISTINCT l.giorno_settimana) as giorni
            FROM allievi a
            JOIN lezioni l ON a.id = l.allievo_id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.attiva = 1 AND a.attivo = 1
            GROUP BY a.id, a.cognome, a.nome
            ORDER BY a.cognome, a.nome
        ");
    }

    /**
     * Statistiche allievi con/senza lezioni
     */
    public function getStatisticheLezioni() {
        $totale = $this->countAllievi(true);
        $allievi_con_lezioni = $this->getAllieviConLezioni();
        $con_lezioni = is_array($allievi_con_lezioni) ? count($allievi_con_lezioni) : 0;
        
        return [
            'totale' => $totale,
            'con_lezioni' => $con_lezioni,
            'senza_lezioni' => $totale - $con_lezioni,
            'percentuale' => $totale > 0 ? round(($con_lezioni / $totale) * 100, 1) : 0
        ];
    }
}
