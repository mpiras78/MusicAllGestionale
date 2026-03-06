<?php
/**
 * Iscrizioni Controller
 * Gestione iscrizioni allievi ai corsi
 */

class IscrizioniController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene tutte le iscrizioni con filtri
     */
    public function getIscrizioni($anno_scolastico = null, $stato = null, $allievo_id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT i.*, 
                CONCAT(a.cognome, ' ', a.nome) as allievo,
                tc.nome as tipo_corso,
                m.nome as materia,
                CONCAT(d.cognome, ' ', d.nome) as docente,
                0 as is_pacchetto,
                0 as lezioni_utilizzate,
                0 as lezioni_totali,
                i.quota_iscrizione as importo_totale,
                0 as importo_pagato
                FROM iscrizioni i
                LEFT JOIN allievi a ON i.allievo_id = a.id
                LEFT JOIN tipi_corso_config tc ON i.tipo_corso_config_id = tc.id
                LEFT JOIN materie m ON i.materia_id = m.id
                LEFT JOIN docenti d ON i.docente_id = d.id
                WHERE 1=1";
        
        $params = [];
        
        if ($anno_scolastico) {
            $sql .= " AND i.anno_accademico = ?";
            $params[] = $anno_scolastico;
        }
        
        if ($stato) {
            $sql .= " AND i.stato = ?";
            $params[] = $stato;
        }
        
        if ($allievo_id) {
            $sql .= " AND i.allievo_id = ?";
            $params[] = $allievo_id;
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ottiene iscrizione per ID
     */
    public function getIscrizioneById($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM iscrizioni WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ottiene iscrizioni attive di un allievo
     */
    public function getIscrizioniAttiveAllievo($allievo_id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT i.*, tc.nome as tipo_corso, m.nome as materia
            FROM iscrizioni i
            LEFT JOIN tipi_corso_config tc ON i.tipo_corso_config_id = tc.id
            LEFT JOIN materie m ON i.materia_id = m.id
            WHERE i.allievo_id = ? AND i.stato = 'attiva'
            ORDER BY i.data_inizio DESC
        ");
        $stmt->execute([$allievo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea nuova iscrizione
     */
    public function creaIscrizione($data) {
        $conn = $this->db->getConnection();
        
        // Valida data_inizio
        if (empty($data['data_inizio'])) {
            $data['data_inizio'] = date('Y-m-d');
        }
        
        // Se data_fine non è fornita, usa il 31 luglio dello stesso anno
        if (empty($data['data_fine'])) {
            $anno = date('Y', strtotime($data['data_inizio']));
            $data['data_fine'] = "{$anno}-07-31";
        }
        
        $stmt = $conn->prepare("
            INSERT INTO iscrizioni (
                allievo_id, tipo_corso_config_id, materia_id, docente_id,
                anno_accademico, data_inizio, data_fine,
                stato, quota_iscrizione, sconto_fratelli, sconto_meta_anno, note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['allievo_id'],
            $data['tipo_corso_config_id'],
            $data['materia_id'],
            $data['docente_id'],
            $data['anno_scolastico'],
            $data['data_inizio'],
            $data['data_fine'],
            $data['stato'] ?? 'attiva',
            $data['quota_iscrizione'] ?? 30,
            $data['sconto_fratelli'] ?? 0,
            $data['sconto_meta_anno'] ?? 0,
            $data['note'] ?? null
        ]);
        
        return $conn->lastInsertId();
    }
    
    /**
     * Aggiorna iscrizione
     */
    public function aggiornaIscrizione($id, $data) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE iscrizioni SET
                tipo_corso_config_id = ?,
                materia_id = ?,
                docente_id = ?,
                anno_accademico = ?,
                data_inizio = ?,
                data_fine = ?,
                stato = ?,
                quota_iscrizione = ?,
                sconto_fratelli = ?,
                note = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['tipo_corso_config_id'],
            $data['materia_id'],
            $data['docente_id'],
            $data['anno_scolastico'],
            $data['data_inizio'],
            $data['data_fine'] ?? null,
            $data['stato'],
            $data['quota_iscrizione'],
            $data['sconto_fratelli'],
            $data['note'] ?? null,
            $id
        ]);
    }
    
    /**
     * Registra utilizzo lezione custom
     */
    public function registraUtilizzoLezioneCustom($iscrizione_id, $data) {
        // Verifica che sia un corso custom
        $iscrizione = $this->getIscrizioneById($iscrizione_id);
        
        if (!$iscrizione || !$iscrizione['is_pacchetto']) {
            throw new Exception("Iscrizione non valida o non è un corso custom");
        }
        
        if ($iscrizione['lezioni_utilizzate'] >= $iscrizione['lezioni_totali']) {
            throw new Exception("Tutte le lezioni del pacchetto sono state utilizzate");
        }
        
        // Registra utilizzo
        $this->db->insert("
            INSERT INTO utilizzo_lezioni_custom (
                iscrizione_id, data_lezione, ora_inizio, ora_fine,
                docente_id, aula_id, note
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [
            $iscrizione_id,
            $data['data_lezione'],
            $data['ora_inizio'],
            $data['ora_fine'],
            $data['docente_id'],
            $data['aula_id'] ?? null,
            $data['note'] ?? null
        ]);
        
        // Incrementa contatore
        $this->db->execute("
            UPDATE iscrizioni 
            SET lezioni_utilizzate = lezioni_utilizzate + 1,
                updated_at = datetime('now')
            WHERE id = ?
        ", [$iscrizione_id]);
        
        return true;
    }
    
    /**
     * Ottiene tipi corso disponibili
     */
    public function getTipiCorso($attivi_only = true) {
        $conn = $this->db->getConnection();
        $where = $attivi_only ? "WHERE attivo = 1" : "";
        $stmt = $conn->query("SELECT * FROM tipi_corso_config $where ORDER BY ordine_visualizzazione");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Statistiche iscrizioni
     */
    public function getStatistiche($anno_scolastico = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT stato, COUNT(*) as num_iscrizioni, 
                SUM(quota_iscrizione) as importo_totale,
                0 as importo_pagato
                FROM iscrizioni";
        
        $params = [];
        if ($anno_scolastico) {
            $sql .= " WHERE anno_accademico = ?";
            $params[] = $anno_scolastico;
        }
        
        $sql .= " GROUP BY stato";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Conta iscrizioni attive
     */
    public function countIscrizioniAttive() {
        $conn = $this->db->getConnection();
        $stmt = $conn->query("SELECT COUNT(*) FROM iscrizioni WHERE stato = 'attiva'");
        return $stmt->fetchColumn();
    }
    
    /**
     * Ottiene iscrizioni filtrate per mese
     * Mostra le iscrizioni che sono attive nel mese selezionato:
     * - data_inizio <= ultimo giorno del mese
     * - data_fine >= primo giorno del mese
     * - stato = 'attiva'
     */
    public function getIscrizioniPerMese($anno_selezionato, $mese_num) {
        $conn = $this->db->getConnection();
        
        // Primo e ultimo giorno del mese
        $primo_giorno_mese = sprintf('%04d-%02d-01', $anno_selezionato, $mese_num);
        $ultimo_giorno_mese = date('Y-m-t', strtotime($primo_giorno_mese));
        
        // SQLite usa strftime per i confronti di data
        $sql = "
            SELECT 
                i.id,
                CONCAT(a.cognome, ' ', a.nome) as allievo,
                tcc.nome as tipo_corso,
                m.nome as materia,
                CONCAT(d.cognome, ' ', d.nome) as docente,
                i.data_inizio,
                i.data_fine,
                i.stato,
                i.quota_iscrizione as importo_totale,
                0 as importo_pagato,
                0 as is_pacchetto,
                0 as lezioni_utilizzate,
                0 as lezioni_totali
            FROM iscrizioni i
            INNER JOIN allievi a ON i.allievo_id = a.id
            LEFT JOIN tipi_corso_config tcc ON i.tipo_corso_config_id = tcc.id
            LEFT JOIN materie m ON i.materia_id = m.id
            LEFT JOIN docenti d ON i.docente_id = d.id
            WHERE i.stato = 'attiva'
            AND i.data_inizio <= ?
            AND i.data_fine >= ?
            ORDER BY a.cognome, a.nome ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $ultimo_giorno_mese,  // data_inizio <= ultimo giorno del mese
            $primo_giorno_mese    // data_fine >= primo giorno del mese
        ]);
        
        $iscrizioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log della query
        $this->logQuery($sql, [$ultimo_giorno_mese, $primo_giorno_mese], $anno_selezionato, $mese_num, count($iscrizioni));
        
        return $iscrizioni;
    }
    
    /**
     * Log della query eseguita
     */
    private function logQuery($sql, $params, $anno, $mese, $risultati) {
        $log_file = LOG_PATH . '/query_iscrizioni.log';
        $timestamp = date('Y-m-d H:i:s');
        $mese_formattato = sprintf('%02d', $mese);
        $log_message = "[{$timestamp}] Mese: {$anno}-{$mese_formattato} | Params: " . json_encode($params) . " | Risultati: {$risultati}\n";
        $log_message .= "SQL: {$sql}\n";
        $log_message .= str_repeat('-', 80) . "\n";
        
        @file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
