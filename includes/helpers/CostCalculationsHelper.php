<?php

namespace App\Helpers;

/**
 * CostCalculationsHelper
 * 
 * Helper functions per calcoli di costi, pro-rata, e pagamenti
 * 
 * @package App\Helpers
 */
class CostCalculationsHelper
{
    private $db;

    public function __construct($database = null)
    {
        global $db;
        $this->db = $database ?? $db;
    }

    /**
     * Calcola il costo dell'iscrizione annuale basato sul mese
     * 
     * Logica biperiodica:
     * - Agosto-Febbraio: €150
     * - Marzo-Luglio: €100
     * 
     * @param string $data - Data di riferimento (YYYY-MM-DD) o NULL per oggi
     * @return float - Costo iscrizione
     */
    public function getCostoIscrizioneMese($data = null)
    {
        try {
            if (!$data) {
                $data = date('Y-m-d');
            }

            // Ottenere i costi da dati_associazione
            $sql = "SELECT costo_iscrizione_agosto_febbraio, costo_iscrizione_marzo_luglio 
                    FROM dati_associazione LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return 150.00; // Default fallback
            }

            $mese = (int)date('m', strtotime($data));

            // Agosto (08) a Febbraio (02)
            if ($mese >= 8 || $mese <= 2) {
                return (float)$result['costo_iscrizione_agosto_febbraio'];
            }

            // Marzo (03) a Luglio (07)
            return (float)$result['costo_iscrizione_marzo_luglio'];

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::getCostoIscrizioneMese: " . $e->getMessage());
            return 150.00;
        }
    }

    /**
     * Genera numero tessera nel formato YYYYN
     * 
     * Es: 20261, 20262, ...
     * Dove:
     * - 2026 = anno accademico
     * - N = numero progressivo
     * 
     * @param int $anno_accademico - Anno accademico
     * @return string - Numero tessera
     */
    public function generaNumerTessera($anno_accademico)
    {
        try {
            // Contare iscrizioni già fatte questo anno
            $sql = "SELECT COUNT(*) as count FROM iscrizioni_annuali 
                    WHERE anno_accademico = :anno_accademico";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':anno_accademico' => $anno_accademico]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $progressivo = ($result['count'] ?? 0) + 1;

            return sprintf("%d%d", $anno_accademico, $progressivo);

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::generaNumerTessera: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcola numero di lezioni in un mese per un corso
     * 
     * Conta le lezioni ricorrenti nel mese specifico
     * 
     * @param int $corso_id - ID del corso
     * @param int $anno - Anno
     * @param int $mese - Mese (1-12)
     * @return int - Numero lezioni
     */
    public function contaLezioniMese($corso_id, $anno, $mese)
    {
        try {
            // Creare range date per il mese
            $data_inizio = sprintf("%04d-%02d-01", $anno, $mese);
            $data_fine = date('Y-m-t', strtotime($data_inizio)); // Ultimo giorno del mese

            // Query per contare lezioni nel range
            $sql = "SELECT COUNT(*) as count FROM eventi_calendario 
                    WHERE corso_id = :corso_id 
                    AND data_evento BETWEEN :data_inizio AND :data_fine 
                    AND stato != 'CANCELLATA'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':corso_id' => $corso_id,
                ':data_inizio' => $data_inizio,
                ':data_fine' => $data_fine
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::contaLezioniMese: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcola costo mensile per un socio (un corso)
     * 
     * Costo = numero_lezioni × costo_per_lezione
     * 
     * @param int $corso_id - ID del corso
     * @param int $anno - Anno
     * @param int $mese - Mese
     * @return float - Costo totale
     */
    public function calcolaCostoCorsoMese($corso_id, $anno, $mese)
    {
        try {
            // Ottenere dati corso
            $sql = "SELECT costo_lezione FROM corsi_soci WHERE id = :corso_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':corso_id' => $corso_id]);
            $corso = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$corso) {
                return 0.00;
            }

            $numero_lezioni = $this->contaLezioniMese($corso_id, $anno, $mese);
            return (float)$corso['costo_lezione'] * $numero_lezioni;

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::calcolaCostoCorsoMese: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Calcola costo totale corsi per un mese (tutti i corsi socio)
     * 
     * @param int $socio_id - ID del socio
     * @param int $anno - Anno
     * @param int $mese - Mese
     * @return float - Costo totale
     */
    public function calcolaCostatiMese($socio_id, $anno, $mese)
    {
        try {
            // Ottenere tutti i corsi attivi del socio
            $sql = "SELECT id FROM corsi_soci 
                    WHERE socio_id = :socio_id 
                    AND stato = 'ATTIVO'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':socio_id' => $socio_id]);
            $corsi = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $costo_totale = 0.00;

            foreach ($corsi as $corso) {
                // Verificare se corso è sospeso nel mese
                if ($this->isCorsoSospesoMese($corso['id'], $anno, $mese)) {
                    continue; // Saltare corso sospeso
                }

                $costo_totale += $this->calcolaCostoCorsoMese($corso['id'], $anno, $mese);
            }

            return (float)$costo_totale;

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::calcolaCostoMese: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Calcola pro-rata per modifica corso mid-month
     * 
     * Delta = (lezioni_nuovo × costo_nuovo) - (lezioni_vecchio × costo_vecchio)
     * 
     * @param int $corso_id_vecchio - ID vecchio corso
     * @param int $corso_id_nuovo - ID nuovo corso
     * @param string $data_modifica - Data modifica (YYYY-MM-DD)
     * @return array - ['delta' => float, 'dettagli' => string]
     */
    public function calcolaProRataModificaCorso($corso_id_vecchio, $corso_id_nuovo, $data_modifica)
    {
        try {
            $anno = (int)date('Y', strtotime($data_modifica));
            $mese = (int)date('m', strtotime($data_modifica));
            $giorno = (int)date('d', strtotime($data_modifica));

            // Ultimo giorno del mese
            $ultimo_giorno = (int)date('t', strtotime($data_modifica));

            // Lezioni vecchio corso da oggi a fine mese
            $lezioni_vecchio = $this->contaLezioniMessoDa($corso_id_vecchio, $anno, $mese, $giorno, $ultimo_giorno);

            // Lezioni nuovo corso da oggi a fine mese
            $lezioni_nuovo = $this->contaLezioniMessoDa($corso_id_nuovo, $anno, $mese, $giorno, $ultimo_giorno);

            // Costi per lezione
            $sql = "SELECT costo_lezione FROM corsi_soci WHERE id = :corso_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':corso_id' => $corso_id_vecchio]);
            $corso_vecchio = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt->execute([':corso_id' => $corso_id_nuovo]);
            $corso_nuovo = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$corso_vecchio || !$corso_nuovo) {
                return ['delta' => 0.00, 'dettagli' => 'Errore: corso non trovato'];
            }

            $costo_vecchio = (float)$corso_vecchio['costo_lezione'] * $lezioni_vecchio;
            $costo_nuovo = (float)$corso_nuovo['costo_lezione'] * $lezioni_nuovo;
            $delta = $costo_nuovo - $costo_vecchio;

            $dettagli = sprintf(
                "Pro-rata modifica corso: %d lezioni %s (€%.2f) → %d lezioni %s (€%.2f) = Delta: €%.2f",
                $lezioni_vecchio,
                "vecchio",
                $costo_vecchio,
                $lezioni_nuovo,
                "nuovo",
                $costo_nuovo,
                $delta
            );

            return [
                'delta' => $delta,
                'costo_vecchio' => $costo_vecchio,
                'costo_nuovo' => $costo_nuovo,
                'lezioni_vecchio' => $lezioni_vecchio,
                'lezioni_nuovo' => $lezioni_nuovo,
                'dettagli' => $dettagli
            ];

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::calcolaProRataModificaCorso: " . $e->getMessage());
            return ['delta' => 0.00, 'dettagli' => 'Errore nel calcolo pro-rata'];
        }
    }

    /**
     * Verifica se corso è sospeso nel mese specifico
     * 
     * @param int $corso_id - ID del corso
     * @param int $anno - Anno
     * @param int $mese - Mese
     * @return bool - True se corso è sospeso
     */
    public function isCorsoSospesoMese($corso_id, $anno, $mese)
    {
        try {
            $data_inizio = sprintf("%04d-%02d-01", $anno, $mese);
            $data_fine = date('Y-m-t', strtotime($data_inizio));

            $sql = "SELECT COUNT(*) as count FROM sospensioni_corso 
                    WHERE corso_id = :corso_id 
                    AND tipo = 'TEMPORANEA'
                    AND data_inizio <= :data_fine 
                    AND (data_fine IS NULL OR data_fine >= :data_inizio)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':corso_id' => $corso_id,
                ':data_inizio' => $data_inizio,
                ':data_fine' => $data_fine
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0) > 0;

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::isCorsoSospesoMese: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta lezioni in un range date specifico
     * 
     * @param int $corso_id - ID corso
     * @param int $anno - Anno
     * @param int $mese - Mese
     * @param int $giorno_inizio - Giorno inizio range
     * @param int $giorno_fine - Giorno fine range
     * @return int - Numero lezioni
     */
    private function contaLezioniMessoDa($corso_id, $anno, $mese, $giorno_inizio, $giorno_fine)
    {
        try {
            $data_inizio = sprintf("%04d-%02d-%02d", $anno, $mese, $giorno_inizio);
            $data_fine = sprintf("%04d-%02d-%02d", $anno, $mese, $giorno_fine);

            $sql = "SELECT COUNT(*) as count FROM eventi_calendario 
                    WHERE corso_id = :corso_id 
                    AND data_evento BETWEEN :data_inizio AND :data_fine 
                    AND stato != 'CANCELLATA'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':corso_id' => $corso_id,
                ':data_inizio' => $data_inizio,
                ':data_fine' => $data_fine
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::contaLezioniMessoDa: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Applica sconto familiare
     * 
     * @param float $importo_corsi - Importo totale corsi
     * @param int $socio_id - ID socio
     * @return float - Importo dopo sconto (0 se sconto non applicabile)
     */
    public function applicaScontoFamiliare($importo_corsi, $socio_id)
    {
        try {
            // Verificare se socio ha collegamento famiglia attivo
            $sql = "SELECT id FROM famiglia 
                    WHERE socio_id = :socio_id 
                    AND stato = 'ATTIVO' 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':socio_id' => $socio_id]);
            $famiglia = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$famiglia) {
                return 0.00; // Nessuna famiglia
            }

            // Verificare se familiare ha corso attivo
            $sql = "SELECT COUNT(*) as count FROM corsi_soci cs 
                    INNER JOIN famiglia f ON cs.socio_id = f.familiare_id 
                    WHERE f.socio_id = :socio_id 
                    AND f.stato = 'ATTIVO'
                    AND cs.stato = 'ATTIVO'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':socio_id' => $socio_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ((int)($result['count'] ?? 0) == 0) {
                return 0.00; // Familiare non ha corsi attivi
            }

            // Calcolare sconto
            $sql = "SELECT sconto_familiare_percentuale FROM dati_associazione LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);

            $percentuale = (float)($config['sconto_familiare_percentuale'] ?? 10.00);
            return $importo_corsi * ($percentuale / 100);

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::applicaScontoFamiliare: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Applica sconto compleanno
     * 
     * @param float $importo_corsi - Importo totale corsi
     * @param int $socio_id - ID socio
     * @param int $mese - Mese (1-12) per verifica
     * @return float - Importo sconto
     */
    public function applicaScontoCompleanno($importo_corsi, $socio_id, $mese = null)
    {
        try {
            if (!$mese) {
                $mese = (int)date('m');
            }

            // Verificare data nascita socio
            $sql = "SELECT data_nascita FROM soci WHERE id = :socio_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':socio_id' => $socio_id]);
            $socio = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$socio || !$socio['data_nascita']) {
                return 0.00;
            }

            $mese_nascita = (int)date('m', strtotime($socio['data_nascita']));

            if ($mese_nascita != $mese) {
                return 0.00; // Compleanno non è questo mese
            }

            // Ottenere percentuale sconto
            $sql = "SELECT sconto_compleanno_percentuale FROM dati_associazione LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);

            $percentuale = (float)($config['sconto_compleanno_percentuale'] ?? 5.00);
            return $importo_corsi * ($percentuale / 100);

        } catch (\Exception $e) {
            error_log("Error in CostCalculationsHelper::applicaScontoCompleanno: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Applica sconto mattutini (50% iscrizione + 10% corsi)
     * 
     * @param float $costo_iscrizione - Costo iscrizione
     * @param float $importo_corsi - Importo totale corsi (solo mattutini)
     * @return array - ['iscrizione_sconto' => float, 'corsi_sconto' => float]
     */
    public function applicaScontoMattutini($costo_iscrizione, $importo_corsi)
    {
        return [
            'iscrizione_sconto' => $costo_iscrizione * 0.5, // 50%
            'corsi_sconto' => $importo_corsi * 0.1 // 10%
        ];
    }
}
