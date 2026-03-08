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
    
    /**
     * Ottiene tutte le assenze con filtri opzionali
     */
    public function getAllAssenze($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['docente_id'])) {
            $where[] = "a.docente_id = ?";
            $params[] = $filters['docente_id'];
        }
        
        if (!empty($filters['allievo_id'])) {
            $where[] = "a.allievo_id = ?";
            $params[] = $filters['allievo_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(al.cognome LIKE ? OR al.nome LIKE ? OR d.cognome LIKE ? OR d.nome LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['causata_da'])) {
            $where[] = "a.tipo = ?";
            $params[] = $filters['causata_da'];
        }
        
        if (!empty($filters['necessita_recupero'])) {
            $where[] = "a.da_recuperare = ?";
            $params[] = $filters['necessita_recupero'];
        }
        
        // Filtro per mese
        if (!empty($filters['mese'])) {
            $mese = (int)$filters['mese'];
            // Determina l'anno in base al mese (anno scolastico)
            $anno_corrente = (int)date('Y');
            $mese_corrente = (int)date('n');
            
            // Se il mese selezionato è settembre-dicembre e siamo in gennaio-luglio,
            // il mese si riferisce all'anno precedente
            if ($mese >= 9 && $mese_corrente < 9) {
                $anno = $anno_corrente - 1;
            }
            // Se il mese selezionato è gennaio-luglio e siamo in settembre-dicembre,
            // il mese si riferisce all'anno successivo
            else if ($mese < 9 && $mese_corrente >= 9) {
                $anno = $anno_corrente + 1;
            }
            // Altrimenti è l'anno corrente
            else {
                $anno = $anno_corrente;
            }
            
            $where[] = "CAST(strftime('%m', a.data_assenza) AS INTEGER) = ? AND CAST(strftime('%Y', a.data_assenza) AS INTEGER) = ?";
            $params[] = $mese;
            $params[] = $anno;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Filtro stato recupero - uso subquery per evitare HAVING su query non-aggregate
        $base_query = "
            SELECT a.*, 
                   a.data_assenza as data,
                   a.tipo as causata_da,
                   a.motivo as note_annullamento,
                   a.da_recuperare as necessita_recupero,
                   al.cognome || ' ' || al.nome as allievo,
                   d.cognome || ' ' || d.nome as docente,
                   m.nome as materia,
                   l.giorno_settimana,
                   l.ora_inizio,
                   l.ora_fine,
                   a.minuti_da_recuperare,
                   (SELECT COUNT(*) FROM recuperi WHERE assenza_id = a.id AND annullato = 0) as ha_recupero,
                   COALESCE((
                       SELECT SUM((strftime('%s', r.ora_fine) - strftime('%s', r.ora_inizio)) / 60)
                       FROM recuperi r
                       WHERE r.assenza_id = a.id 
                       AND r.annullato = 0
                   ), 0) as minuti_recuperati,
                   (SELECT GROUP_CONCAT(r.data_recupero, ', ')
                    FROM recuperi r
                    WHERE r.assenza_id = a.id 
                    AND r.annullato = 0
                    ORDER BY r.data_recupero) as date_recuperi
            FROM assenze a
            JOIN allievi al ON a.allievo_id = al.id
            JOIN docenti d ON a.docente_id = d.id
            LEFT JOIN lezioni l ON a.lezione_id = l.id
            LEFT JOIN materie m ON l.materia_id = m.id
            $where_clause
            ORDER BY a.data_assenza DESC, a.created_at DESC
        ";
        
        // Se c'è filtro stato, wrappa in subquery
        if (!empty($filters['stato_recupero'])) {
            $condition = "";
            switch ($filters['stato_recupero']) {
                case 'programmato':
                    $condition = "ha_recupero > 0";
                    break;
                case 'da_programmare':
                    $condition = "da_recuperare = 1 AND ha_recupero = 0";
                    break;
                case 'non_necessario':
                    $condition = "da_recuperare = 0";
                    break;
            }
            
            if ($condition) {
                $base_query = "SELECT * FROM ($base_query) AS subq WHERE $condition";
            }
        }
        
        return $this->db->query($base_query, $params) ?: [];
    }
    
    /**
     * Crea una nuova assenza
     */
    public function creaAssenza($data) {
        // Validazione
        if (empty($data['lezione_id']) || empty($data['data']) || empty($data['causata_da'])) {
            throw new Exception('Dati obbligatori mancanti');
        }
        
        // Ottieni info lezione
        $lezione = $this->db->queryOne("SELECT * FROM lezioni WHERE id = ?", [$data['lezione_id']]);
        if (!$lezione) {
            throw new Exception('Lezione non trovata');
        }
        
        // Verifica se assenza già esiste
        $esistente = $this->db->queryOne("
            SELECT id FROM assenze 
            WHERE lezione_id = ? AND data_assenza = ?
        ", [$data['lezione_id'], $data['data']]);
        
        if ($esistente) {
            throw new Exception('Assenza già registrata per questa lezione e data');
        }
        
        // Inserisci assenza - schema corretto
        $sql = "
            INSERT INTO assenze (
                lezione_id, allievo_id, docente_id,
                data_assenza, tipo, da_recuperare, 
                motivo, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
        ";
        
        $da_recuperare = isset($data['necessita_recupero']) ? $data['necessita_recupero'] : 1;
        
        $this->db->execute($sql, [
            $data['lezione_id'],
            $lezione['allievo_id'],
            $lezione['docente_id'],
            $data['data'],
            $data['causata_da'],
            $da_recuperare,
            $data['note_annullamento'] ?? null
        ]);
        
        return true;
    }
    
    /**
     * Aggiorna un'assenza
     */
    public function aggiornaAssenza($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['causata_da'])) {
            $fields[] = "causata_da = ?";
            $params[] = $data['causata_da'];
        }
        
        if (isset($data['necessita_recupero'])) {
            $fields[] = "necessita_recupero = ?";
            $params[] = $data['necessita_recupero'];
        }
        
        if (isset($data['note_annullamento'])) {
            $fields[] = "note_annullamento = ?";
            $params[] = $data['note_annullamento'];
        }
        
        if (empty($fields)) {
            throw new Exception('Nessun campo da aggiornare');
        }
        
        $fields[] = "updated_at = datetime('now')";
        $params[] = $id;
        
        $sql = "UPDATE assenze SET " . implode(", ", $fields) . " WHERE id = ?";
        $this->db->execute($sql, $params);
    }
    
    /**
     * Aggiorna solo la data di un'assenza
     */
    public function aggiornaDataAssenza($id, $data_assenza) {
        $this->db->execute("
            UPDATE assenze 
            SET data_assenza = ?,
                updated_at = datetime('now')
            WHERE id = ?
        ", [$data_assenza, $id]);
    }
    
    /**
     * Elimina un'assenza
     */
    public function eliminaAssenza($id) {
        // Verifica se ha recuperi associati
        $recuperi = $this->db->queryOne("
            SELECT COUNT(*) as cnt FROM recuperi WHERE assenza_id = ?
        ", [$id]);
        
        if ($recuperi['cnt'] > 0) {
            throw new Exception('Impossibile eliminare: esistono recuperi associati');
        }
        
        $this->db->execute("DELETE FROM assenze WHERE id = ?", [$id]);
    }
    
    /**
     * Ottieni lista docenti (per filtro)
     */
    public function getDocenti() {
        return $this->db->query("
            SELECT id, cognome || ' ' || nome as nome_completo 
            FROM docenti 
            ORDER BY cognome, nome
        ") ?: [];
    }
    
    /**
     * Ottieni lista soci (per filtro)
     */
    public function getSoci() {
        return $this->db->query("
            SELECT id, cognome || ' ' || nome as nome_completo 
            FROM soci 
            ORDER BY cognome, nome
        ") ?: [];
    }
    
    /**
     * Alias di compatibilità (retrocompatibilità)
     */
    public function getAllievi() {
        return $this->getSoci();
    }
    
    /**
     * Ottiene contatori assenze/recuperi per anno scolastico corrente
     * Anno scolastico: settembre anno precedente - giugno anno corrente
     */
    public function getContatoriAnnoScolastico($allievo_id, $lezione_id) {
        // Determina anno scolastico corrente
        $oggi = new DateTime();
        $mese = (int)$oggi->format('m');
        $anno = (int)$oggi->format('Y');
        
        // Se siamo da gennaio ad agosto, anno scolastico è (anno-1)/anno
        // Se siamo da settembre a dicembre, anno scolastico è anno/(anno+1)
        if ($mese < 9) {
            $anno_inizio = $anno - 1;
            $anno_fine = $anno;
        } else {
            $anno_inizio = $anno;
            $anno_fine = $anno + 1;
        }
        
        // Date inizio e fine anno scolastico
        $data_inizio = "$anno_inizio-09-01";
        $data_fine = "$anno_fine-06-30";
        
        // Conta assenze per questa lezione nell'anno scolastico
        $assenze = $this->db->queryOne("
            SELECT COUNT(*) as totale
            FROM assenze
            WHERE allievo_id = ?
            AND lezione_id = ?
            AND data_assenza BETWEEN ? AND ?
        ", [$allievo_id, $lezione_id, $data_inizio, $data_fine]);
        
        // Conta recuperi per queste assenze
        $recuperi = $this->db->queryOne("
            SELECT COUNT(DISTINCT r.id) as totale
            FROM recuperi r
            JOIN assenze a ON r.assenza_id = a.id
            WHERE a.allievo_id = ?
            AND a.lezione_id = ?
            AND a.data_assenza BETWEEN ? AND ?
            AND r.annullato = 0
        ", [$allievo_id, $lezione_id, $data_inizio, $data_fine]);
        
        return [
            'assenze' => $assenze['totale'] ?? 0,
            'recuperi' => $recuperi['totale'] ?? 0,
            'anno_scolastico' => "$anno_inizio/$anno_fine"
        ];
    }
    
    /**
     * Conta assenze per statistiche
     */
    public function contaAssenze() {
        $result = $this->db->queryOne("
            SELECT 
                COUNT(*) as totale,
                SUM(CASE WHEN tipo = 'allievo' THEN 1 ELSE 0 END) as da_allievo,
                SUM(CASE WHEN tipo = 'docente' THEN 1 ELSE 0 END) as da_docente,
                SUM(CASE WHEN da_recuperare = 1 THEN 1 ELSE 0 END) as da_recuperare,
                SUM(CASE WHEN (SELECT COUNT(*) FROM recuperi WHERE assenza_id = assenze.id AND annullato = 0) > 0 THEN 1 ELSE 0 END) as con_recupero
            FROM assenze
        ");
        
        return $result ?: [
            'totale' => 0,
            'da_allievo' => 0,
            'da_docente' => 0,
            'da_recuperare' => 0,
            'con_recupero' => 0
        ];
    }
}
