<?php

use MusicAll\Models\Pagamento;
use MusicAll\Models\Iscrizione;
use MusicAll\Models\TipoPagamento;
use MusicAll\Models\MetodoPagamento;
use MusicAll\Models\Socio;

/**
 * Controller per gestione pagamenti
 */
class PagamentiController
{
    private $db;
    private $userId;
    private $fk_col;

    public function __construct()
    {
        $this->fk_col = 'socio_id';
    }

    /**
     * Lista pagamenti con filtri
     * 
     * @param array $filters
     * @return array
     */
    public function lista($filters = [])
    {
        try {
            $query = Pagamento::query()
                ->with(['socio', 'iscrizione', 'tipoPagamento', 'metodoPagamento']);

            // Filtro anno accademico
            if (!empty($filters['anno_accademico'])) {
                $query->perAnno($filters['anno_accademico']);
            }

            // Filtro socio/socio
            if (!empty($filters['socio_id'])) {
                $query->perSocio($filters['socio_id']);
            } elseif (!empty($filters['socio_id'])) {
                $query->perSocio($filters['socio_id']);
            }

            // Filtro tipo pagamento
            if (!empty($filters['tipo_pagamento_id'])) {
                $query->perTipo($filters['tipo_pagamento_id']);
            }

            // Filtro stato
            if (!empty($filters['stato'])) {
                if ($filters['stato'] === 'pagato') {
                    $query->pagati();
                } else {
                    $query->where('stato', $filters['stato']);
                }
            }

            // Filtro periodo
            if (!empty($filters['anno']) && !empty($filters['mese'])) {
                $query->perPeriodo($filters['anno'], $filters['mese']);
            } elseif (!empty($filters['anno'])) {
                $query->perPeriodo($filters['anno']);
            }

            // Filtro con quota associativa
            if (isset($filters['con_quota']) && $filters['con_quota']) {
                $query->conQuotaAssociativa();
            }

            // Ordinamento
            $query->orderBy('data_pagamento', 'desc');

            $pagamenti = $query->get();

            return [
                'success' => true,
                'pagamenti' => $pagamenti,
                'count' => $pagamenti->count()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Dettaglio pagamento
     * 
     * @param int $id
     * @return array
     */
    public function dettaglio($id)
    {
        try {
            $pagamento = Pagamento::with([
                'socio',
                'iscrizione.materia',
                'iscrizione.docente',
                'tipoPagamento',
                'metodoPagamento',
                'registrante'
            ])->find($id);

            if (!$pagamento) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato'
                ];
            }

            // Get breakdown
            $breakdown = $pagamento->getBreakdown();

            return [
                'success' => true,
                'pagamento' => $pagamento,
                'breakdown' => $breakdown
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Registra nuovo pagamento
     * 
     * @param array $data
     * @return array
     */
    public function registra($data)
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

            // Calcola importo netto
            $importo = (float) $data['importo'];
            $sconto = (float) ($data['sconto'] ?? 0);
            $importoNetto = $importo - $sconto;

            // Determina se includere quota associativa
            $includeQuota = isset($data['include_quota_associativa']) && 
                           $data['include_quota_associativa'];
            
            $importoQuota = $includeQuota ? (float) ($data['importo_quota_associativa'] ?? 0) : 0;

            // Crea pagamento
            $pagSocioId = $data['socio_id'] ?? $data['socio_id'] ?? null;
            $pagamento = Pagamento::create([
                $this->fk_col => $pagSocioId,
                'iscrizione_id' => $data['iscrizione_id'] ?? null,
                'tipo_pagamento_id' => $data['tipo_pagamento_id'],
                'anno_accademico' => $data['anno_accademico'],
                'mese_riferimento' => $data['mese_riferimento'] ?? null,
                'importo' => $importo,
                'sconto' => $sconto,
                'importo_netto' => $importoNetto,
                'include_quota_associativa' => $includeQuota,
                'importo_quota_associativa' => $importoQuota,
                'metodo_pagamento_id' => $data['metodo_pagamento_id'],
                'data_pagamento' => $data['data_pagamento'] ?? date('Y-m-d'),
                'data_scadenza' => $data['data_scadenza'] ?? null,
                'numero_ricevuta' => $data['numero_ricevuta'] ?? null,
                'riferimento_transazione' => $data['riferimento_transazione'] ?? null,
                'note' => $data['note'] ?? null,
                'stato' => 'pagato',
                'registrato_da' => $this->userId
            ]);

            // Get breakdown per conferma
            $breakdown = $pagamento->getBreakdown();

            // Log attività
            $pagamentoNome = $pagamento->socio_nome ?? $pagamento->socio_nome ?? '';
            SecurityHelper::logActivity(
                $this->userId,
                'pagamento_registrato',
                'pagamento',
                $pagamento->id,
                "Registrato pagamento di €{$importoNetto} per {$pagamentoNome}"
            );

            return [
                'success' => true,
                'pagamento' => $pagamento,
                'breakdown' => $breakdown,
                'message' => 'Pagamento registrato con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Aggiorna pagamento
     * 
     * @param int $id
     * @param array $data
     * @return array
     */
    public function aggiorna($id, $data)
    {
        try {
            $pagamento = Pagamento::find($id);

            if (!$pagamento) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato'
                ];
            }

            // Aggiorna campi consentiti
            if (isset($data['data_pagamento'])) {
                $pagamento->data_pagamento = $data['data_pagamento'];
            }
            if (isset($data['numero_ricevuta'])) {
                $pagamento->numero_ricevuta = $data['numero_ricevuta'];
            }
            if (isset($data['riferimento_transazione'])) {
                $pagamento->riferimento_transazione = $data['riferimento_transazione'];
            }
            if (isset($data['note'])) {
                $pagamento->note = $data['note'];
            }
            if (isset($data['stato'])) {
                $pagamento->stato = $data['stato'];
            }

            $pagamento->save();

            // Log attività
            $pagamentoNome = $pagamento->socio_nome ?? $pagamento->socio_nome ?? '';
            SecurityHelper::logActivity(
                $this->userId,
                'pagamento_aggiornato',
                'pagamento',
                $pagamento->id,
                "Aggiornato pagamento per {$pagamentoNome}"
            );

            return [
                'success' => true,
                'pagamento' => $pagamento,
                'message' => 'Pagamento aggiornato con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Elimina pagamento
     * 
     * @param int $id
     * @return array
     */
    public function elimina($id)
    {
        try {
            $pagamento = Pagamento::find($id);

            if (!$pagamento) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato'
                ];
            }

            $socioNome = $pagamento->socio_nome ?? $pagamento->socio_nome ?? '';
            $importo = $pagamento->importo_netto;
            
            $pagamento->delete();

            // Log attività
            SecurityHelper::logActivity(
                $this->userId,
                'pagamento_eliminato',
                'pagamento',
                $id,
                "Eliminato pagamento di €{$importo} per {$socioNome}"
            );

            return [
                'success' => true,
                'message' => 'Pagamento eliminato con successo'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Report incassi per periodo
     * 
     * @param array $params
     * @return array
     */
    public function reportIncassi($params = [])
    {
        try {
            $anno = $params['anno'] ?? date('Y');
            $mese = $params['mese'] ?? null;

            $query = Pagamento::pagati()
                ->perPeriodo($anno, $mese);

            $pagamenti = $query->get();

            $totaleIncassato = $pagamenti->sum('importo_netto');
            $numeroPagamenti = $pagamenti->count();
            $mediaIncasso = $numeroPagamenti > 0 ? $totaleIncassato / $numeroPagamenti : 0;

            // Breakdown per tipo
            $perTipo = $pagamenti->groupBy('tipo_pagamento_id')->map(function($gruppo) {
                return [
                    'count' => $gruppo->count(),
                    'totale' => $gruppo->sum('importo_netto')
                ];
            });

            // Breakdown per metodo
            $perMetodo = $pagamenti->groupBy('metodo_pagamento_id')->map(function($gruppo) {
                return [
                    'count' => $gruppo->count(),
                    'totale' => $gruppo->sum('importo_netto')
                ];
            });

            // Pagamenti con quota inclusa
            $conQuota = $pagamenti->where('include_quota_associativa', true);
            $totaleQuote = $conQuota->sum('importo_quota_associativa');

            return [
                'success' => true,
                'periodo' => [
                    'anno' => $anno,
                    'mese' => $mese
                ],
                'totale_incassato' => $totaleIncassato,
                'numero_pagamenti' => $numeroPagamenti,
                'media_incasso' => round($mediaIncasso, 2),
                'per_tipo' => $perTipo,
                'per_metodo' => $perMetodo,
                'quote_associative' => [
                    'numero' => $conQuota->count(),
                    'totale' => $totaleQuote
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lista pagamenti in scadenza
     * 
     * @param int $giorni
     * @return array
     */
    public function inScadenza($giorni = 30)
    {
        try {
            $dataLimite = date('Y-m-d', strtotime("+{$giorni} days"));
            
            $pagamenti = Pagamento::where('stato', '!=', 'pagato')
                ->whereNotNull('data_scadenza')
                ->where('data_scadenza', '<=', $dataLimite)
                ->with(['socio', 'tipoPagamento'])
                ->orderBy('data_scadenza', 'asc')
                ->get();

            return [
                'success' => true,
                'pagamenti' => $pagamenti,
                'count' => $pagamenti->count()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pagamenti in ritardo
     * 
     * @return array
     */
    public function inRitardo()
    {
        try {
            $oggi = date('Y-m-d');
            
            $pagamenti = Pagamento::where('stato', '!=', 'pagato')
                ->whereNotNull('data_scadenza')
                ->where('data_scadenza', '<', $oggi)
                ->with(['socio', 'tipoPagamento'])
                ->orderBy('data_scadenza', 'asc')
                ->get();

            // Aggiungi giorni di ritardo
            $pagamenti->each(function($pag) {
                $pag->giorni_ritardo = $pag->getGiorniRitardo();
            });

            return [
                'success' => true,
                'pagamenti' => $pagamenti,
                'count' => $pagamenti->count()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validazione dati pagamento
     * 
     * @param array $data
     * @return array
     */
    private function valida($data)
    {
        $errors = [];

        if (empty($data['socio_id']) && empty($data['socio_id'])) {
            $errors[] = 'Socio richiesto';
        }
        if (empty($data['tipo_pagamento_id'])) {
            $errors[] = 'Tipo pagamento richiesto';
        }
        if (empty($data['metodo_pagamento_id'])) {
            $errors[] = 'Metodo pagamento richiesto';
        }
        if (empty($data['anno_accademico'])) {
            $errors[] = 'Anno accademico richiesto';
        }
        if (!isset($data['importo']) || $data['importo'] <= 0) {
            $errors[] = 'Importo non valido';
        }

        return [
            'valid' => empty($errors),
            'errors' => implode(', ', $errors)
        ];
    }
}