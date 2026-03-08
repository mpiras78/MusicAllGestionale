<?php
/**
 * Recuperi Controller
 * Gestione logica business per recuperi lezioni
 */

class RecuperiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ottiene recuperi per docente con stato calcolato
     */
    public function getRecuperiDocente($docente_id, $stato = null) {
        $where = "WHERE r.docente_id = ?";
        $params = [$docente_id];
        
        if ($stato) {
            // Filtra per stato
            switch ($stato) {
                case 'proposta':
                    $where .= " AND r.annullato = 0 AND r.confermata_da_docente = 0 AND r.data_recupero >= DATE('now')";
                    break;
                case 'confermata':
                    $where .= " AND r.annullato = 0 AND (r.confermata_da_docente = 1 OR r.confermata_da_segreteria = 1) AND r.data_recupero >= DATE('now')";
                    break;
                case 'completato':
                    $where .= " AND r.annullato = 0 AND r.data_recupero < DATE('now')";
                    break;
                case 'annullato':
                    $where .= " AND r.annullato = 1";
                    break;
            }
        }
        
        return $this->db->query("SELECT * FROM v_recuperi_con_stato r $where ORDER BY r.data_recupero, r.ora_inizio", $params);
    }
    
    /**
     * Ottiene tutti i recuperi (admin/segreteria)
     */
    public function getRecuperi($stato = null, $limit = null) {
        $where = "";
        $params = [];
        
        if ($stato) {
            switch ($stato) {
                case 'proposta':
                    $where = "WHERE annullato = 0 AND confermata_da_docente = 0 AND data_recupero >= DATE('now')";
                    break;
                case 'confermata':
                    $where = "WHERE annullato = 0 AND (confermata_da_docente = 1 OR confermata_da_segreteria = 1) AND data_recupero >= DATE('now')";
                    break;
                case 'completato':
                    $where = "WHERE annullato = 0 AND data_recupero < DATE('now')";
                    break;
                case 'annullato':
                    $where = "WHERE annullato = 1";
                    break;
            }
        }
        
        $limit_clause = $limit ? "LIMIT ?" : "";
        if ($limit) $params[] = $limit;
        
        return $this->db->query("
            SELECT * FROM v_recuperi_con_stato 
            $where 
            ORDER BY data_recupero, ora_inizio 
            $limit_clause
        ", $params);
    }
    
    /**
     * Ottiene assenze che necessitano recupero (anche parziale)
     */
    public function getAssenzeDaRecuperare($docente_id = null) {
        $where = "";
        $params = [];
        
        if ($docente_id) {
            $where = "AND l.docente_id = ?";
            $params[] = $docente_id;
        }
        
        // Query con calcolo minuti recuperati
        return $this->db->query("
            SELECT 
                a.id,
                a.data_assenza as data,
                a.tipo as causata_da,
                al.cognome || ' ' || al.nome as allievo,
                d.cognome || ' ' || d.nome as docente,
                m.nome as materia,
                l.giorno_settimana,
                l.ora_inizio,
                l.ora_fine,
                a.minuti_da_recuperare,
                COALESCE((
                    SELECT SUM((strftime('%s', r.ora_fine) - strftime('%s', r.ora_inizio)) / 60)
                    FROM recuperi r
                    WHERE r.assenza_id = a.id 
                    AND r.annullato = 0
                ), 0) as minuti_recuperati,
                (SELECT COUNT(*) FROM assenze WHERE allievo_id = a.allievo_id AND da_recuperare = 1) as totale_assenze_allievo
            FROM assenze a
            JOIN lezioni l ON a.lezione_id = l.id
            JOIN allievi al ON a.allievo_id = al.id
            JOIN docenti d ON a.docente_id = d.id
            LEFT JOIN materie m ON l.materia_id = m.id
            WHERE a.da_recuperare = 1
            AND (
                a.minuti_da_recuperare IS NULL 
                OR a.minuti_da_recuperare > COALESCE((
                    SELECT SUM((strftime('%s', r.ora_fine) - strftime('%s', r.ora_inizio)) / 60)
                    FROM recuperi r
                    WHERE r.assenza_id = a.id 
                    AND r.annullato = 0
                ), 0)
            )
            $where
            ORDER BY a.data_assenza DESC
        ", $params);
    }
    
    /**
     * Crea proposta recupero con validazione tempo
     */
    public function creaRecupero($data) {
        // Validazioni
        if (empty($data['assenza_id']) || empty($data['data_recupero'])) {
            throw new Exception('Dati obbligatori mancanti');
        }
        
        // Ottieni dati assenza con minuti
        $assenza = $this->db->queryOne("
            SELECT ass.*, 
                   l.allievo_id, l.docente_id, l.materia_id,
                   ass.minuti_da_recuperare,
                   COALESCE((
                       SELECT SUM((strftime('%s', r.ora_fine) - strftime('%s', r.ora_inizio)) / 60)
                       FROM recuperi r
                       WHERE r.assenza_id = ass.id 
                       AND r.annullato = 0
                   ), 0) as minuti_gia_recuperati
            FROM assenze ass
            JOIN lezioni l ON ass.lezione_id = l.id
            WHERE ass.id = ?
        ", [$data['assenza_id']]);
        
        if (!$assenza) {
            throw new Exception('Assenza non trovata');
        }
        
        // Calcola minuti del nuovo recupero
        $ora_inizio = new DateTime($data['ora_inizio']);
        $ora_fine = new DateTime($data['ora_fine']);
        $minuti_nuovo_recupero = ($ora_fine->getTimestamp() - $ora_inizio->getTimestamp()) / 60;
        
        if ($minuti_nuovo_recupero <= 0) {
            throw new Exception('Orario non valido: ora fine deve essere dopo ora inizio');
        }
        
        // Verifica che non superi il tempo dell'assenza
        $minuti_totali = $assenza['minuti_gia_recuperati'] + $minuti_nuovo_recupero;
        $minuti_da_recuperare = $assenza['minuti_da_recuperare'] ?? 0;
        
        if ($minuti_da_recuperare > 0 && $minuti_totali > $minuti_da_recuperare) {
            $minuti_rimanenti = $minuti_da_recuperare - $assenza['minuti_gia_recuperati'];
            throw new Exception(
                "Tempo di recupero eccessivo! " .
                "Hai inserito {$minuti_nuovo_recupero} minuti, ma ne servono solo {$minuti_rimanenti}. " .
                "(Totale assenza: {$minuti_da_recuperare}', già recuperati: {$assenza['minuti_gia_recuperati']}')"
            );
        }
        
        // Verifica aula disponibile
        if (!empty($data['aula_id'])) {
            if (!$this->isAulaDisponibile($data['aula_id'], $data['data_recupero'], $data['ora_inizio'], $data['ora_fine'])) {
                throw new Exception('Aula non disponibile in questo orario');
            }
        }
        
        // Inserisci recupero - GIÀ CONFERMATO (flusso semplificato)
        $recupero_id = $this->db->insert("
            INSERT INTO recuperi (
                assenza_id, lezione_originale_id, allievo_id, docente_id, materia_id,
                data_recupero, ora_inizio, ora_fine, aula_id,
                note_segreteria, created_by,
                confermata_da_segreteria, confermata_da_user_id, data_conferma
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, CURRENT_TIMESTAMP)
        ", [
            $data['assenza_id'],
            $assenza['lezione_id'],
            $assenza['allievo_id'],
            $assenza['docente_id'],
            $assenza['materia_id'],
            $data['data_recupero'],
            $data['ora_inizio'],
            $data['ora_fine'],
            $data['aula_id'] ?? null,
            $data['note_segreteria'] ?? null,
            $_SESSION['user_id'],
            $_SESSION['user_id']
        ]);
        
        // FIX: Inserisci ANCHE in eventi_calendario per renderlo visibile nel calendario
        // Trova ID tipologia recupero
        $tipologia_recupero = $this->db->queryOne("SELECT id FROM tipologie_evento WHERE codice = 'LEZ_RECUPERO' LIMIT 1");
        
        if ($tipologia_recupero && $recupero_id) {
            
            // Ottieni data assenza per le note
            $data_assenza_formattata = date('d/m/Y', strtotime($assenza['data_assenza']));
            $note_recupero = "Recupero lezione del {$data_assenza_formattata}";
            if (!empty($data['note_segreteria'])) {
                $note_recupero .= " - " . $data['note_segreteria'];
            }
            
            $this->db->execute("
                INSERT INTO eventi_calendario (
                    tipologia_id, ricorrente, giorno_settimana, data_evento,
                    ora_inizio, ora_fine, aula_id, docente_id, allievo_id, materia_id,
                    titolo, note, confermato, attivo, created_at
                ) VALUES (?, 0, NULL, ?, ?, ?, ?, ?, ?, ?, 'Recupero', ?, 1, 1, datetime('now', 'localtime'))
            ", [
                $tipologia_recupero['id'],
                $data['data_recupero'],
                $data['ora_inizio'],
                $data['ora_fine'],
                $data['aula_id'] ?? null,
                $assenza['docente_id'],
                $assenza['allievo_id'],
                $assenza['materia_id'],
                $note_recupero
            ]);
        }
        
        return $recupero_id;
    }
    
    /**
     * Conferma recupero da parte del docente
     */
    public function confermaRecuperoDocente($recupero_id, $user_id) {
        return $this->db->execute("
            UPDATE recuperi 
            SET confermata_da_docente = 1,
                confermata_da_user_id = ?,
                data_conferma = CURRENT_TIMESTAMP
            WHERE id = ? AND confermata_da_docente = 0
        ", [$user_id, $recupero_id]);
    }
    
    /**
     * Rifiuta recupero da parte del docente
     */
    public function rifiutaRecuperoDocente($recupero_id, $motivo) {
        return $this->db->execute("
            UPDATE recuperi 
            SET annullato = 1,
                motivo_rifiuto = ?,
                annullato_da_user_id = ?,
                data_annullamento = CURRENT_TIMESTAMP
            WHERE id = ?
        ", [$motivo, $_SESSION['user_id'], $recupero_id]);
    }
    
    /**
     * Conferma recupero da parte segreteria (bypass docente)
     */
    public function confermaRecuperoSegreteria($recupero_id, $user_id) {
        return $this->db->execute("
            UPDATE recuperi 
            SET confermata_da_segreteria = 1,
                confermata_da_user_id = ?,
                data_conferma = CURRENT_TIMESTAMP
            WHERE id = ?
        ", [$user_id, $recupero_id]);
    }
    
    /**
     * Annulla recupero
     */
    public function annullaRecupero($recupero_id, $motivo, $user_id) {
        return $this->db->execute("
            UPDATE recuperi 
            SET annullato = 1,
                motivo_annullamento = ?,
                annullato_da_user_id = ?,
                data_annullamento = CURRENT_TIMESTAMP
            WHERE id = ?
        ", [$motivo, $user_id, $recupero_id]);
    }
    
    /**
     * Verifica disponibilità aula
     */
    public function isAulaDisponibile($aula_id, $data, $ora_inizio, $ora_fine, $escludi_recupero_id = null) {
        $where_escludi = $escludi_recupero_id ? "AND r.id != ?" : "";
        $params = $escludi_recupero_id ? [$aula_id, $data, $ora_inizio, $ora_fine, $escludi_recupero_id] : [$aula_id, $data, $ora_inizio, $ora_fine];
        
        // Controlla recuperi esistenti
        $conflitto_recuperi = $this->db->queryOne("
            SELECT COUNT(*) as count
            FROM recuperi r
            WHERE r.aula_id = ?
            AND r.data_recupero = ?
            AND r.annullato = 0
            AND (
                (r.ora_inizio < ? AND r.ora_fine > ?) OR
                (r.ora_inizio >= ? AND r.ora_inizio < ?) OR
                (r.ora_fine > ? AND r.ora_fine <= ?)
            )
            $where_escludi
        ", array_merge([$aula_id, $data, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine], $escludi_recupero_id ? [$escludi_recupero_id] : []));
        
        return $conflitto_recuperi['count'] == 0;
    }
    
    /**
     * Ottiene aule disponibili per data/ora
     */
    public function getAuleDisponibili($data, $ora_inizio, $ora_fine) {
        return $this->db->query("
            SELECT a.* 
            FROM aule a
            WHERE a.id NOT IN (
                SELECT r.aula_id
                FROM recuperi r
                WHERE r.data_recupero = ?
                AND r.annullato = 0
                AND (
                    (r.ora_inizio < ? AND r.ora_fine > ?) OR
                    (r.ora_inizio >= ? AND r.ora_inizio < ?) OR
                    (r.ora_fine > ? AND r.ora_fine <= ?)
                )
            )
            ORDER BY a.nome
        ", [$data, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine, $ora_inizio, $ora_fine]);
    }
    
    /**
     * Conta recuperi per stato
     */
    public function contaRecuperiPerStato($docente_id = null) {
        $where = $docente_id ? "WHERE docente_id = ?" : "";
        $params = $docente_id ? [$docente_id] : [];
        
        $result = $this->db->queryOne("
            SELECT 
                COUNT(CASE WHEN stato = 'proposta' THEN 1 END) as proposta,
                COUNT(CASE WHEN stato = 'confermata' THEN 1 END) as confermata,
                COUNT(CASE WHEN stato = 'completato' THEN 1 END) as completato,
                COUNT(CASE WHEN stato = 'annullato' THEN 1 END) as annullato
            FROM v_recuperi_con_stato
            $where
        ", $params);
        
        return $result;
    }
    
    /**
     * Ottiene recupero singolo
     */
    public function getRecupero($id) {
        return $this->db->queryOne("SELECT * FROM v_recuperi_con_stato WHERE id = ?", [$id]);
    }
}