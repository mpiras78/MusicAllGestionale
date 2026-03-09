<?php
/**
 * Controller per Configurazione Corsi
 */

class ConfigurazioneCorsiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // ========== TIPI CORSO ==========
    
    public function getTipiCorso($attivi_only = false) {
        $sql = "SELECT tc.*, tip.nome as tipologia_nome 
                FROM tipi_corso_config tc
                LEFT JOIN tipologie_laboratorio tip ON tc.tipologia_laboratorio_id = tip.id";
        
        if ($attivi_only) {
            $sql .= " WHERE tc.attivo = 1";
        }
        
        $sql .= " ORDER BY tc.ordine_visualizzazione, tc.nome";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTipoCorsoById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tipi_corso_config WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function creaTipoCorso($data) {
        $sql = "INSERT INTO tipi_corso_config 
                (nome, durata_lezione, costo_mensile, include_laboratorio, tipologia_laboratorio_id, descrizione, ordine_visualizzazione, attivo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['durata_lezione'],
            $data['costo_mensile'],
            $data['include_laboratorio'] ?? 0,
            $data['tipologia_laboratorio_id'] ?? null,
            $data['descrizione'] ?? null,
            $data['ordine_visualizzazione'] ?? 0,
            $data['attivo'] ?? 1
        ]);
    }
    
    public function aggiornaTipoCorso($id, $data) {
        $sql = "UPDATE tipi_corso_config SET 
                nome = ?, durata_lezione = ?, costo_mensile = ?, 
                include_laboratorio = ?, tipologia_laboratorio_id = ?, 
                descrizione = ?, ordine_visualizzazione = ?, attivo = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['durata_lezione'],
            $data['costo_mensile'],
            $data['include_laboratorio'] ?? 0,
            $data['tipologia_laboratorio_id'] ?? null,
            $data['descrizione'] ?? null,
            $data['ordine_visualizzazione'] ?? 0,
            $data['attivo'] ?? 1,
            $id
        ]);
    }
    
    public function eliminaTipoCorso($id) {
        // Verifica se ci sono iscrizioni attive
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM iscrizioni WHERE tipo_corso_config_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Impossibile eliminare: ci sono iscrizioni attive'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM tipi_corso_config WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // ========== TIPI LABORATORIO ==========
    
    public function getTipiLaboratorio($attivi_only = false) {
        $sql = "SELECT tl.*, 
                CONCAT(d.cognome, ' ', d.nome) as docente_nome,
                a.nome as aula_nome,
                (SELECT COUNT(*) FROM laboratorio_partecipanti lp 
                 WHERE lp.tipo_laboratorio_id = tl.id AND lp.attivo = 1) as num_partecipanti
                FROM tipi_laboratorio tl
                LEFT JOIN docenti d ON tl.docente_id = d.id
                LEFT JOIN aule a ON tl.aula_id = a.id";
        
        if ($attivi_only) {
            $sql .= " WHERE tl.attivo = 1";
        }
        
        $sql .= " ORDER BY tl.giorno_settimana, tl.ora_inizio";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTipoLaboratorioById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tipi_laboratorio WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function creaTipoLaboratorio($data) {
        $sql = "INSERT INTO tipi_laboratorio 
                (nome, descrizione, docente_id, giorno_settimana, ora_inizio, ora_fine, aula_id, max_partecipanti, attivo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['descrizione'] ?? null,
            $data['docente_id'] ?? null,
            $data['giorno_settimana'],
            $data['ora_inizio'],
            $data['ora_fine'],
            $data['aula_id'] ?? null,
            $data['max_partecipanti'] ?? null,
            $data['attivo'] ?? 1
        ]);
    }
    
    public function aggiornaTipoLaboratorio($id, $data) {
        $sql = "UPDATE tipi_laboratorio SET 
                nome = ?, descrizione = ?, docente_id = ?, 
                giorno_settimana = ?, ora_inizio = ?, ora_fine = ?, 
                aula_id = ?, max_partecipanti = ?, attivo = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['descrizione'] ?? null,
            $data['docente_id'] ?? null,
            $data['giorno_settimana'],
            $data['ora_inizio'],
            $data['ora_fine'],
            $data['aula_id'] ?? null,
            $data['max_partecipanti'] ?? null,
            $data['attivo'] ?? 1,
            $id
        ]);
    }
    
    public function eliminaTipoLaboratorio($id) {
        // Verifica se ci sono corsi collegati
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tipi_corso_config WHERE tipo_laboratorio_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Impossibile eliminare: ci sono corsi collegati'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM tipi_laboratorio WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getPartecipantiLaboratorio($laboratorio_id) {
        $sql = "SELECT lp.*, CONCAT(a.cognome, ' ', a.nome) as socio_nome
                FROM laboratorio_partecipanti lp
                JOIN soci a ON lp.socio_id = a.id
                WHERE lp.tipo_laboratorio_id = ? AND lp.attivo = 1
                ORDER BY a.cognome, a.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$laboratorio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTipologieLaboratorio() {
        $sql = "SELECT * FROM tipologie_laboratorio WHERE attivo = 1 ORDER BY nome";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
