<?php

namespace App\Controllers;

use PDO;

/**
 * AuditLogController
 * 
 * Gestisce il tracciamento di tutte le modifiche nel sistema
 * CREATE, UPDATE, DELETE, e azioni custom
 * 
 * @package App\Controllers
 */
class AuditLogController extends BaseController
{
    protected $table = 'audit_log';

    /**
     * Log una azione nel database
     * 
     * @param string $azione - Tipo di azione (CREATE, UPDATE, DELETE, etc)
     * @param string $tabella - Nome della tabella modificata
     * @param int $record_id - ID del record modificato
     * @param mixed $vecchio_valore - Vecchio valore (per UPDATE)
     * @param mixed $nuovo_valore - Nuovo valore
     * @param int|null $admin_id - ID admin se azione admin
     * @param int|null $socio_id - ID socio se azione socio
     * @param string|null $dettagli - Dettagli aggiuntivi
     * @return int - ID del record audit creato
     */
    public function log(
        $azione,
        $tabella,
        $record_id,
        $vecchio_valore = null,
        $nuovo_valore = null,
        $admin_id = null,
        $socio_id = null,
        $dettagli = null
    ) {
        try {
            // Convertire array/object a JSON se necessario
            if (is_array($vecchio_valore) || is_object($vecchio_valore)) {
                $vecchio_valore = json_encode($vecchio_valore, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($nuovo_valore) || is_object($nuovo_valore)) {
                $nuovo_valore = json_encode($nuovo_valore, JSON_UNESCAPED_UNICODE);
            }

            // Ottenere IP address
            $ip_address = $this->getClientIp();

            // Ottenere User Agent
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Query INSERT
            $sql = "INSERT INTO audit_log 
                    (azione, tabella, record_id, vecchio_valore, nuovo_valore, 
                     admin_id, socio_id, ip_address, user_agent, dettagli, created_at) 
                    VALUES 
                    (:azione, :tabella, :record_id, :vecchio_valore, :nuovo_valore, 
                     :admin_id, :socio_id, :ip_address, :user_agent, :dettagli, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':azione' => $azione,
                ':tabella' => $tabella,
                ':record_id' => $record_id,
                ':vecchio_valore' => $vecchio_valore,
                ':nuovo_valore' => $nuovo_valore,
                ':admin_id' => $admin_id,
                ':socio_id' => $socio_id,
                ':ip_address' => $ip_address,
                ':user_agent' => $user_agent,
                ':dettagli' => $dettagli
            ]);

            return $this->db->lastInsertId();

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::log: " . $e->getMessage());
            // Non lanciare eccezione per evitare break di operazione principale
            return 0;
        }
    }

    /**
     * Log una CREATE (nuovo record)
     * 
     * @param string $tabella - Nome della tabella
     * @param int $record_id - ID del record creato
     * @param array $dati - Dati inseriti
     * @param int|null $admin_id - ID admin
     * @param int|null $socio_id - ID socio che ha fatto azione
     * @return int - ID audit log creato
     */
    public function logCreate($tabella, $record_id, $dati, $admin_id = null, $socio_id = null)
    {
        return $this->log(
            'CREATE',
            $tabella,
            $record_id,
            null,
            $dati,
            $admin_id,
            $socio_id,
            "Creazione nuovo record in $tabella"
        );
    }

    /**
     * Log una UPDATE (modifica record)
     * 
     * @param string $tabella - Nome della tabella
     * @param int $record_id - ID del record modificato
     * @param array $vecchio_valore - Vecchi valori
     * @param array $nuovo_valore - Nuovi valori
     * @param int|null $admin_id - ID admin
     * @param int|null $socio_id - ID socio
     * @return int - ID audit log creato
     */
    public function logUpdate($tabella, $record_id, $vecchio_valore, $nuovo_valore, $admin_id = null, $socio_id = null)
    {
        return $this->log(
            'UPDATE',
            $tabella,
            $record_id,
            $vecchio_valore,
            $nuovo_valore,
            $admin_id,
            $socio_id,
            "Modifica record in $tabella"
        );
    }

    /**
     * Log una DELETE (eliminazione record)
     * 
     * @param string $tabella - Nome della tabella
     * @param int $record_id - ID del record eliminato
     * @param array $dati_eliminati - Dati eliminati
     * @param int|null $admin_id - ID admin
     * @param int|null $socio_id - ID socio
     * @return int - ID audit log creato
     */
    public function logDelete($tabella, $record_id, $dati_eliminati, $admin_id = null, $socio_id = null)
    {
        return $this->log(
            'DELETE',
            $tabella,
            $record_id,
            $dati_eliminati,
            null,
            $admin_id,
            $socio_id,
            "Eliminazione record da $tabella"
        );
    }

    /**
     * Log azione custom (es: MODIFICA_CORSO_PRO_RATA, REVOCA_SCONTO, etc)
     * 
     * @param string $azione - Nome azione custom
     * @param string $tabella - Tabella interessata
     * @param int $record_id - ID record
     * @param array $dati - Dati operazione
     * @param int|null $admin_id - ID admin
     * @param int|null $socio_id - ID socio
     * @param string $dettagli - Descrizione dettagliata
     * @return int - ID audit log creato
     */
    public function logCustomAction($azione, $tabella, $record_id, $dati, $admin_id = null, $socio_id = null, $dettagli = null)
    {
        return $this->log(
            $azione,
            $tabella,
            $record_id,
            null,
            $dati,
            $admin_id,
            $socio_id,
            $dettagli
        );
    }

    /**
     * Recupera history per un record
     * 
     * @param string $tabella - Nome della tabella
     * @param int $record_id - ID del record
     * @return array - Array di operazioni audit
     */
    public function getHistory($tabella, $record_id)
    {
        try {
            $sql = "SELECT * FROM audit_log 
                    WHERE tabella = :tabella AND record_id = :record_id 
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tabella' => $tabella,
                ':record_id' => $record_id
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getHistory: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera history per un socio
     * 
     * @param int $socio_id - ID del socio
     * @param int $limit - Numero di record
     * @return array - Array di operazioni audit
     */
    public function getHistorySocio($socio_id, $limit = 100)
    {
        try {
            $sql = "SELECT * FROM audit_log 
                    WHERE socio_id = :socio_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':socio_id', $socio_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getHistorySocio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera operazioni admin
     * 
     * @param int $admin_id - ID dell'admin
     * @param int $limit - Numero di record
     * @return array - Array di operazioni audit
     */
    public function getHistoryAdmin($admin_id, $limit = 100)
    {
        try {
            $sql = "SELECT * FROM audit_log 
                    WHERE admin_id = :admin_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getHistoryAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera operazioni per tabella
     * 
     * @param string $tabella - Nome della tabella
     * @param string $azione - Filtra per azione (opzionale)
     * @param int $limit - Numero di record
     * @return array - Array di operazioni audit
     */
    public function getHistoryTabella($tabella, $azione = null, $limit = 100)
    {
        try {
            $where = "WHERE tabella = :tabella";
            $params = [':tabella' => $tabella];

            if ($azione) {
                $where .= " AND azione = :azione";
                $params[':azione'] = $azione;
            }

            $sql = "SELECT * FROM audit_log 
                    $where 
                    ORDER BY created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getHistoryTabella: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera operazioni in un periodo
     * 
     * @param string $data_inizio - Data inizio (YYYY-MM-DD)
     * @param string $data_fine - Data fine (YYYY-MM-DD)
     * @param int $limit - Numero di record
     * @return array - Array di operazioni audit
     */
    public function getHistoryPeriodo($data_inizio, $data_fine, $limit = 500)
    {
        try {
            $sql = "SELECT * FROM audit_log 
                    WHERE DATE(created_at) BETWEEN :data_inizio AND :data_fine 
                    ORDER BY created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':data_inizio', $data_inizio);
            $stmt->bindValue(':data_fine', $data_fine);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getHistoryPeriodo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ottieni numero totale record audit log
     * 
     * @return int - Numero totale
     */
    public function getTotalCount()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM audit_log";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getTotalCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ottieni statistiche audit log
     * 
     * @return array - Stats per azione, tabella, etc
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Stats per azione
            $sql = "SELECT azione, COUNT(*) as count FROM audit_log GROUP BY azione ORDER BY count DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['per_azione'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Stats per tabella
            $sql = "SELECT tabella, COUNT(*) as count FROM audit_log GROUP BY tabella ORDER BY count DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['per_tabella'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Stats totali
            $stats['totale'] = $this->getTotalCount();

            return $stats;

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::getStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Pulisci audit log vecchi (archivio)
     * Mantieni solo ultimi N giorni
     * 
     * @param int $giorni - Mantieni ultimi N giorni (default 180 = 6 mesi)
     * @return int - Numero di record eliminati
     */
    public function archiveOldLogs($giorni = 180)
    {
        try {
            $sql = "DELETE FROM audit_log 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL :giorni DAY)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':giorni', $giorni, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();

        } catch (\Exception $e) {
            error_log("Error in AuditLogController::archiveOldLogs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ottieni IP address del client
     * 
     * @return string - IP address
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        // Validare IP format
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
    }
}
