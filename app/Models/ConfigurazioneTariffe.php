<?php

namespace MusicAll\Models;

/**
 * Model per configurazione_tariffe
 * Gestione tariffe per anno accademico
 */
class ConfigurazioneTariffe extends Model
{
    protected $table = 'configurazione_tariffe';
    
    protected $fillable = [
        'anno_accademico',
        'quota_associativa',
        'tariffa_individuale',
        'tariffa_gruppo',
        'tariffa_lab',
        'note',
        'attivo'
    ];

    protected $casts = [
        'quota_associativa' => 'decimal:2',
        'tariffa_individuale' => 'decimal:2',
        'tariffa_gruppo' => 'decimal:2',
        'tariffa_lab' => 'decimal:2',
        'attivo' => 'boolean'
    ];

    /**
     * Scope per configurazione attiva
     */
    public function scopeAttiva($query)
    {
        return $query->where('attivo', 1);
    }

    /**
     * Get configurazione per anno accademico
     */
    public static function getPerAnno($annoAccademico)
    {
        return self::where('anno_accademico', $annoAccademico)->first();
    }

    /**
     * Get configurazione corrente (attiva)
     */
    public static function getCurrent()
    {
        return self::where('attivo', 1)->first();
    }

    /**
     * Get tariffa per tipo corso
     *
     * @param string $tipoCorso 'individuale', 'gruppo', 'lab'
     * @return float
     */
    public function getTariffaPerTipo($tipoCorso)
    {
        switch (strtolower($tipoCorso)) {
            case 'individuale':
                return (float) $this->tariffa_individuale;
            case 'gruppo':
                return (float) $this->tariffa_gruppo;
            case 'lab':
            case 'laboratorio':
                return (float) $this->tariffa_lab;
            default:
                return (float) $this->tariffa_individuale;
        }
    }

    /**
     * Calcola importo con quota associativa se necessaria
     *
     * @param string $tipoCorso
     * @param bool $includeQuotaAssociativa
     * @return array
     */
    public function calcolaImporto($tipoCorso, $includeQuotaAssociativa = false)
    {
        $tariffa = $this->getTariffaPerTipo($tipoCorso);
        $quota = $includeQuotaAssociativa ? (float) $this->quota_associativa : 0;
        
        return [
            'tariffa_mensile' => $tariffa,
            'quota_associativa' => $quota,
            'totale' => $tariffa + $quota,
            'include_quota' => $includeQuotaAssociativa
        ];
    }
}