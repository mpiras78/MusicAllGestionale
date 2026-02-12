<?php

use MusicAll\Models\Iscrizione;
use MusicAll\Models\Allievo;
use MusicAll\Models\Materia;
use MusicAll\Models\Docente;
use MusicAll\Models\ConfigurazioneTariffe;

/**
 * Controller per gestione iscrizioni
 */
class IscrizioniController
{
    private $db;
    private $userId;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    /**
     * Lista iscrizioni con filtri
     * 
     * @param array $filters
     * @return array
     */
    public function lista($filters = [])
    {
        try {
            $query = Iscrizione::query()
                ->with(['allievo', 'materia', 'docente']);

            // Filtro anno accademico
            if (!empty($filters['anno_accademico'])) {
                $query->perAnno($filters['anno_accademico']);
            }

            // Filtro allievo
            if (!empty($filters['allievo_id'])) {
                $query->perAllievo($filters['allievo_id']);
            }

            // Filtro stato
            if (!empty($filters['stato'])) {
                if ($filters['stato'] === 'attiva') {
                    $query->attive();
                } else {
                    $query->where('stato', $filters['stato']);
                }
            }

            // Filtro materia
            if (!empty($filters['materia_id'])) {
                $query->where('materia_id', $filters['materia_id']);
            }

            // Filtro docente
            if (!empty($filters['docente_id'])) {
                $query->where('docente_id', $filters['docente_id']);
            }

            // Ordinamento
            $query->orderBy('created_at', 'desc');

            $iscrizioni = $query->get();

            return [
                'success' => true,
                'iscrizioni' => $iscrizioni,
                'count' => $iscrizioni->count()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Dettaglio iscrizione
     * 
     * @param int $id
     * @return array
     */
    public function dettaglio($id)
    {
        try {
            $iscrizione = Iscrizione::with(['allievo', 'materia', 'docente', 'pagamenti.tipoPagamento'])
                ->find($id);

            if (!$iscrizione) {
                return [
                    'success' => false,
                    'error' => 'Iscrizione non trovata'
                ];
            }

            return [
                'success' => true,
                'iscrizione' => $iscrizione
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crea nuova iscrizione
     * 
     * @param array $data
     * @return array
     */
    public function crea($data)
    {
        try {
            // Validazione
            $validation = $this->valida($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['errors']
                ];
            }

            // Check se esiste già iscrizione per stesso allievo/materia/anno
            $esistente = Iscrizione::perAllievo($data['allievo_id'])
                ->perAnno($data['anno_accademico'])
                ->where('materia_id', $data['materia_id'])
                ->first();

            if ($esistente) {
                return [
                    'success' => false,
                    'error' => 'Esiste già una iscrizione per questo allievo e materia nell\'anno accademico selezionato'
                ];
            }

            // Crea iscrizione
            $iscrizione = Iscrizione::create([
                'allievo_id' => $data['allievo_id'],
                'anno_accademico' => $data['anno_accademico'],
                'materia_id' => $data['materia_id'],
                'docente_id' => $data['docente_id'],
                'tipo_corso' => $data['tipo_corso'],
                'data_iscrizione' => $data['data_iscrizione'] ?? date('Y-m-d'),
                'data_inizio_corso' => $data['data_inizio_corso'],
                'data_fine_corso' => $data['data_fine_corso'] ?? null,
                'stato' => 'attiva',
                'creato_da' => $this->userId
            ]);

            // Calcola importo per il frontend
            $importo = $iscrizione->calcolaImportoIscrizione();

            // Log attività
            SecurityHelper::logActivity(
                $this->userId,
                'iscrizione_creata',
                'iscrizione',
                $iscrizione->id,
                "Creata iscrizione per {$iscrizione->allievo_nome}"
            );

            return [
                'success' => true,
                'iscrizione' => $iscrizione,
                'importo_calcolato' => $importo,
                'message' => 'Iscrizione creata con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Aggiorna iscrizione
     * 
     * @param int $id
     * @param array $data
     * @return array
     */
    public function aggiorna($id, $data)
    {
        try {
            $iscrizione = Iscrizione::find($id);

            if (!$iscrizione) {
                return [
                    'success' => false,
                    'error' => 'Iscrizione non trovata'
                ];
            }

            // Aggiorna campi consentiti
            if (isset($data['data_inizio_corso'])) {
                $iscrizione->data_inizio_corso = $data['data_inizio_corso'];
            }
            if (isset($data['data_fine_corso'])) {
                $iscrizione->data_fine_corso = $data['data_fine_corso'];
            }
            if (isset($data['stato'])) {
                $iscrizione->stato = $data['stato'];
            }
            if (isset($data['tipo_corso'])) {
                $iscrizione->tipo_corso = $data['tipo_corso'];
            }

            $iscrizione->save();

            // Log attività
            SecurityHelper::logActivity(
                $this->userId,
                'iscrizione_aggiornata',
                'iscrizione',
                $iscrizione->id,
                "Aggiornata iscrizione per {$iscrizione->allievo_nome}"
            );

            return [
                'success' => true,
                'iscrizione' => $iscrizione,
                'message' => 'Iscrizione aggiornata con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sospendi iscrizione
     * 
     * @param int $id
     * @return array
     */
    public function sospendi($id)
    {
        return $this->aggiorna($id, ['stato' => 'sospesa']);
    }

    /**
     * Riattiva iscrizione
     * 
     * @param int $id
     * @return array
     */
    public function riattiva($id)
    {
        return $this->aggiorna($id, ['stato' => 'attiva']);
    }

    /**
     * Elimina iscrizione (soft)
     * 
     * @param int $id
     * @return array
     */
    public function elimina($id)
    {
        try {
            $iscrizione = Iscrizione::find($id);

            if (!$iscrizione) {
                return [
                    'success' => false,
                    'error' => 'Iscrizione non trovata'
                ];
            }

            // Check se ha pagamenti
            if ($iscrizione->pagamenti()->count() > 0) {
                return [
                    'success' => false,
                    'error' => 'Impossibile eliminare: esistono pagamenti associati'
                ];
            }

            $allievoNome = $iscrizione->allievo_nome;
            $iscrizione->delete();

            // Log attività
            SecurityHelper::logActivity(
                $this->userId,
                'iscrizione_eliminata',
                'iscrizione',
                $id,
                "Eliminata iscrizione per {$allievoNome}"
            );

            return [
                'success' => true,
                'message' => 'Iscrizione eliminata con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcola importo iscrizione
     * 
     * @param array $data
     * @return array
     */
    public function calcolaImporto($data)
    {
        try {
            $allievoId = $data['allievo_id'] ?? null;
            $annoAccademico = $data['anno_accademico'] ?? null;
            $tipoCorso = $data['tipo_corso'] ?? 'individuale';

            if (!$allievoId || !$annoAccademico) {
                return [
                    'success' => false,
                    'error' => 'Parametri mancanti'
                ];
            }

            // Get configurazione tariffe
            $config = ConfigurazioneTariffe::getPerAnno($annoAccademico);
            
            if (!$config) {
                return [
                    'success' => false,
                    'error' => "Configurazione tariffe non trovata per {$annoAccademico}"
                ];
            }

            // Check se allievo ha già pagato quota associativa
            $hasQuota = \MusicAll\Models\Pagamento::where('allievo_id', $allievoId)
                ->where('anno_accademico', $annoAccademico)
                ->where(function($q) {
                    $tipoQuotaId = \MusicAll\Models\TipoPagamento::getQuotaAssociativa()->id;
                    $q->where('tipo_pagamento_id', $tipoQuotaId)
                      ->orWhere('include_quota_associativa', 1);
                })
                ->where('stato', 'pagato')
                ->exists();

            $includeQuota = !$hasQuota;
            $importo = $config->calcolaImporto($tipoCorso, $includeQuota);

            return [
                'success' => true,
                'importo' => $importo,
                'has_quota_associativa' => $hasQuota
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validazione dati iscrizione
     * 
     * @param array $data
     * @return array
     */
    private function valida($data)
    {
        $errors = [];

        if (empty($data['allievo_id'])) {
            $errors[] = 'Allievo richiesto';
        }
        if (empty($data['anno_accademico'])) {
            $errors[] = 'Anno accademico richiesto';
        }
        if (empty($data['materia_id'])) {
            $errors[] = 'Materia richiesta';
        }
        if (empty($data['docente_id'])) {
            $errors[] = 'Docente richiesto';
        }
        if (empty($data['tipo_corso'])) {
            $errors[] = 'Tipo corso richiesto';
        }
        if (empty($data['data_inizio_corso'])) {
            $errors[] = 'Data inizio corso richiesta';
        }

        return [
            'valid' => empty($errors),
            'errors' => implode(', ', $errors)
        ];
    }
}