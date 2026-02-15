<?php
/**
 * EventiController
 * Gestisce gli eventi del calendario (recuperi, prenotazioni, eventi custom)
 */

class EventiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Ottieni eventi per una data specifica
     * Include recuperi, prenotazioni e altri eventi da eventi_calendario
     * 
     * @param string $data Data nel formato YYYY-MM-DD
     * @return array Lista eventi
     */
    public function getEventiPerData($data) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.ora_inizio,
                e.ora_fine,
                e.aula_id,
                t.codice as tipo,
                t.nome as tipologia_nome,
                t.colore_bg,
                t.colore_border,
                COALESCE(a.cognome || ' ' || a.nome, '') as allievo,
                COALESCE(a.id, 0) as allievo_id,
                CASE
                    WHEN d.id IS NOT NULL THEN d.cognome || ' ' || d.nome
                    WHEN se.id IS NOT NULL THEN se.cognome || ' ' || se.nome
                    ELSE ''
                END as docente,
                COALESCE(m.nome, e.titolo, 'Prenotazione') as materia,
                au.nome as aula,
                e.note,
                e.attivo as attiva,
                e.confermato,
                'evento' as source_type
            FROM eventi_calendario e
            INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
            LEFT JOIN allievi a ON e.allievo_id = a.id
            LEFT JOIN docenti d ON e.docente_id = d.id
            LEFT JOIN soci_esterni se ON e.socio_occasionale_id = se.id
            LEFT JOIN materie m ON e.materia_id = m.id
            LEFT JOIN aule au ON e.aula_id = au.id
            WHERE e.data_evento = ?
            AND e.attivo = 1
            AND t.attiva = 1
            ORDER BY e.ora_inizio
        ");
        
        $stmt->execute([$data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ottieni evento per ID
     * 
     * @param int $evento_id ID evento
     * @return array|null Dati evento o null se non trovato
     */
    public function getEventoById($evento_id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                t.codice as tipologia_codice,
                t.nome as tipologia_nome,
                t.categoria as tipologia_categoria,
                a.cognome || ' ' || a.nome as allievo_nome,
                d.cognome || ' ' || d.nome as docente_nome,
                se.cognome || ' ' || se.nome as socio_nome,
                m.nome as materia_nome,
                au.nome as aula_nome
            FROM eventi_calendario e
            INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
            LEFT JOIN allievi a ON e.allievo_id = a.id
            LEFT JOIN docenti d ON e.docente_id = d.id
            LEFT JOIN soci_esterni se ON e.socio_occasionale_id = se.id
            LEFT JOIN materie m ON e.materia_id = m.id
            LEFT JOIN aule au ON e.aula_id = au.id
            WHERE e.id = ?
        ");
        
        $stmt->execute([$evento_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}