<?php
/**
 * Soci Controller (Rinomina da AllieviController)
 * Gestione logica business per soci (ex allievi)
 */

class SociController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutti i soci attivi
     */
    public function getSoci($attivi_only = true, $limit = null, $offset = 0) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        $limit_clause = $limit ? "LIMIT ? OFFSET ?" : "";
        
        $sql = "SELECT * FROM soci $where ORDER BY cognome, nome $limit_clause";
        
        $params = [];
        if ($limit) {
            $params = [$limit, $offset];
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Ottiene un singolo socio per ID
     */
    public function getSocioById($id) {
        return $this->db->queryOne("SELECT * FROM soci WHERE id = ?", [$id]);
    }
    
    /**
     * Ottiene ultimi soci aggiunti
     */
    public function getUltimiSoci($limit = 5) {
        return $this->db->query("
            SELECT * FROM soci 
            WHERE attivo = 1 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Conta soci attivi
     */
    public function countSoci($attivi_only = true) {
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        return $this->db->count("SELECT COUNT(*) FROM soci $where");
    }
    
    /**
     * Cerca soci per nome/cognome
     */
    public function searchSoci($query) {
        $search = "%$query%";
        return $this->db->query("
            SELECT * FROM soci 
            WHERE (nome LIKE ? OR cognome LIKE ?)
            AND attivo = 1
            ORDER BY cognome, nome
        ", [$search, $search]);
    }
    
    /**
     * Crea nuovo socio
     */
    public function createSocio($data) {
        $sql = "INSERT INTO soci (
            nome, cognome, data_nascita, telefono, telefono_2,
            indirizzo, cap, citta, note, attivo, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))";
        
        return $this->db->insert($sql, [
            $data['nome'],
            $data['cognome'],
            $data['data_nascita'] ?? null,
            $data['telefono'] ?? null,
            $data['telefono_2'] ?? null,
            $data['indirizzo'] ?? null,
            $data['cap'] ?? null,
            $data['citta'] ?? null,
            $data['note'] ?? null
        ]);
    }
    
    /**
     * Aggiorna socio
     */
    public function updateSocio($id, $data) {
        $sql = "UPDATE soci SET 
            nome = ?, cognome = ?, data_nascita = ?, 
            telefono = ?, telefono_2 = ?, 
            indirizzo = ?, cap = ?, citta = ?, note = ?
            WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['nome'],
            $data['cognome'],
            $data['data_nascita'] ?? null,
            $data['telefono'] ?? null,
            $data['telefono_2'] ?? null,
            $data['indirizzo'] ?? null,
            $data['cap'] ?? null,
            $data['citta'] ?? null,
            $data['note'] ?? null,
            $id
        ]);
    }
    
    /**
     * Disattiva socio
     */
    public function disattivaSocio($id) {
        return $this->db->execute(
            "UPDATE soci SET attivo = 0 WHERE id = ?",
            [$id]
        );
    }

    /**
     * Ottieni soci con lezioni programmate
     */
    public function getSociConLezioni() {
        return $this->db->query("
            SELECT 
                s.id,
                s.cognome || ' ' || s.nome as nome_completo,
                COUNT(DISTINCT l.id) as num_lezioni,
                GROUP_CONCAT(DISTINCT m.nome) as materie,
                GROUP_CONCAT(DISTINCT l.giorno_settimana) as giorni
            FROM soci s
            JOIN lezioni l ON s.id = l.allievo_id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.attiva = 1 AND s.attivo = 1
            GROUP BY s.id, s.cognome, s.nome
            ORDER BY s.cognome, s.nome
        ");
    }

    /**
     * Ottieni numero tessera per socio
     */
    public function getNumerTessera($socio_id) {
        $sql = "SELECT numero_tessera FROM iscrizioni_annuali 
                WHERE socio_id = ? 
                ORDER BY anno_accademico DESC 
                LIMIT 1";
        $result = $this->db->queryOne($sql, [$socio_id]);
        return $result ? $result['numero_tessera'] : null;
    }

    /**
     * Statistiche soci con/senza lezioni
     */
    public function getStatisticheLezioni() {
        $totale = $this->countSoci(true);
        $soci_con_lezioni = $this->getSociConLezioni();
        $con_lezioni = is_array($soci_con_lezioni) ? count($soci_con_lezioni) : 0;
        
        return [
            'totale' => $totale,
            'con_lezioni' => $con_lezioni,
            'senza_lezioni' => $totale - $con_lezioni,
            'percentuale_attivi' => $totale > 0 ? round(($con_lezioni / $totale) * 100, 2) : 0
        ];
    }
    
    /**
     * Alias di compatibilità (per refactoring graduale)
     * TODO: Rimuovere dopo che tutto il codebase è aggiornato
     */
    public function getAllievi($attivi_only = true, $limit = null, $offset = 0) {
        return $this->getSoci($attivi_only, $limit, $offset);
    }
    
    public function getAllievoById($id) {
        return $this->getSocioById($id);
    }
}
