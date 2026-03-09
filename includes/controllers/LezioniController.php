<?php
/**
 * Lezioni Controller
 * Final-rename implementation using `soci` / `socio_id` (no backwards compatibility)
 */

class LezioniController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getLezioni($attive_only = true, $limit = null) {
        $where = $attive_only ? "WHERE l.attiva = 1" : "";
        $limit_clause = $limit ? "LIMIT ?" : "";

        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            $where
            ORDER BY l.giorno_settimana, l.ora_inizio
            $limit_clause";

        $params = $limit ? [$limit] : [];
        return $this->db->query($sql, $params);
    }

    public function getAllLezioni($attive_only = true) {
        return $this->getLezioni($attive_only);
    }

    public function getLezioniPerGiorno($giorno, $attive_only = true, $data_specifica = null) {
        $where = $attive_only ? "AND l.attiva = 1" : "";

        if ($data_specifica) {
            $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula,
                       ass.id as assenza_id, CASE WHEN ass.id IS NOT NULL THEN 0 ELSE l.attiva END as attiva
                FROM lezioni l
                JOIN soci al ON l.socio_id = al.id
                JOIN docenti d ON l.docente_id = d.id
                JOIN materie m ON l.materia_id = m.id
                JOIN aule a ON l.aula_id = a.id
                LEFT JOIN assenze ass ON ass.lezione_id = l.id AND ass.data_assenza = ?
                WHERE l.giorno_settimana = ? $where
                ORDER BY l.ora_inizio";

            return $this->db->query($sql, [$data_specifica, $giorno]);
        }

        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
                FROM lezioni l
                JOIN soci al ON l.socio_id = al.id
                JOIN docenti d ON l.docente_id = d.id
                JOIN materie m ON l.materia_id = m.id
                JOIN aule a ON l.aula_id = a.id
                WHERE l.giorno_settimana = ? $where
                ORDER BY l.ora_inizio";

        return $this->db->query($sql, [$giorno]);
    }

    public function getProssimeLezioniOggi($giorno, $limit = 5) {
        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.attiva = 1 AND l.giorno_settimana = ?
            ORDER BY l.ora_inizio
            LIMIT ?";

        return $this->db->query($sql, [$giorno, $limit]);
    }

    public function countLezioniSettimana($attive_only = true) {
        $where = $attive_only ? "WHERE attiva = 1" : "";
        return $this->db->count("SELECT COUNT(*) FROM lezioni $where");
    }

    public function getLezioniSocio($socio_id) {
        $sql = "SELECT l.*, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.socio_id = ? AND l.attiva = 1
            ORDER BY l.giorno_settimana, l.ora_inizio";

        return $this->db->query($sql, [$socio_id]);
    }

    public function getLezioniDocente($docente_id) {
        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.docente_id = ? AND l.attiva = 1
            ORDER BY l.giorno_settimana, l.ora_inizio";

        return $this->db->query($sql, [$docente_id]);
    }

    public function getLezioniPerDocenteEGiorno($docente_id, $giorno) {
        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.docente_id = ? AND l.giorno_settimana = ? AND l.attiva = 1
            ORDER BY l.ora_inizio";

        return $this->db->query($sql, [$docente_id, $giorno]);
    }

    public function getLezioneById($lezione_id) {
        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, al.id as socio_id, d.cognome || ' ' || d.nome as docente, m.nome as materia, a.nome as aula
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            JOIN aule a ON l.aula_id = a.id
            WHERE l.id = ?";

        $res = $this->db->query($sql, [$lezione_id]);
        return $res ? $res[0] : null;
    }

    public function getLezioniAula($aula_id, $giorno = null) {
        $where_giorno = $giorno ? "AND l.giorno_settimana = ?" : "";
        $params = $giorno ? [$aula_id, $giorno] : [$aula_id];

        $sql = "SELECT l.*, al.cognome || ' ' || al.nome as socio, d.cognome || ' ' || d.nome as docente, m.nome as materia
            FROM lezioni l
            JOIN soci al ON l.socio_id = al.id
            JOIN docenti d ON l.docente_id = d.id
            JOIN materie m ON l.materia_id = m.id
            WHERE l.aula_id = ? AND l.attiva = 1 $where_giorno
            ORDER BY l.giorno_settimana, l.ora_inizio";

        return $this->db->query($sql, $params);
    }
}
