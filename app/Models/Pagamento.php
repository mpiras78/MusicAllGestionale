<?php

namespace MusicAll\Models;

/**
 * Model per pagamenti
 * Gestione pagamenti con supporto quota associativa inclusa
 */
class Pagamento extends Model
{
    protected $table = 'pagamenti';
    
    protected $fillable = [
        'socio_id',
        'iscrizione_id',
        'tipo_pagamento_id',
        'anno_accademico',
        'mese_riferimento',
        'importo',
        'sconto',
        'importo_netto',
        'include_quota_associativa',
        'importo_quota_associativa',
        'metodo_pagamento_id',
        'data_pagamento',
        'data_scadenza',
        'numero_ricevuta',
        'riferimento_transazione',
        'note',
        'stato',
        'registrato_da'
    ];

    protected $casts = [
        'importo' => 'decimal:2',
        'sconto' => 'decimal:2',
        'importo_netto' => 'decimal:2',
        'importo_quota_associativa' => 'decimal:2',
        'include_quota_associativa' => 'boolean',
        'data_pagamento' => 'date',
        'data_scadenza' => 'date'
    ];

    /**
     * Relazione: socio
     */
    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    /**
     * Relazione: iscrizione
     */
    public function iscrizione()
    {
        return $this->belongsTo(Iscrizione::class);
    }

    /**
     * Relazione: tipo pagamento
     */
    public function tipoPagamento()
    {
        return $this->belongsTo(TipoPagamento::class, 'tipo_pagamento_id');
    }

    /**
     * Relazione: metodo pagamento
     */
    public function metodoPagamento()
    {
        return $this->belongsTo(MetodoPagamento::class, 'metodo_pagamento_id');
    }

    /**
     * Relazione: user registrante
     */
    public function registrante()
    {
        return $this->belongsTo(User::class, 'registrato_da');
    }

    /**
     * Scope: pagamenti pagati
     */
    public function scopePagati($query)
    {
        return $query->where('stato', 'pagato');
    }

    /**
     * Scope: per anno accademico
     */
    public function scopePerAnno($query, $annoAccademico)
    {
        return $query->where('anno_accademico', $annoAccademico);
    }

    /**
     * Scope: per socio
     */
    public function scopePerSocio($query, $socioId)
    {
        return $query->where('socio_id', $socioId);
    }

    /**
     * Scope: per tipo pagamento
     */
    public function scopePerTipo($query, $tipoPagamentoId)
    {
        return $query->where('tipo_pagamento_id', $tipoPagamentoId);
    }

    /**
     * Scope: con quota associativa inclusa
     */
    public function scopeConQuotaAssociativa($query)
    {
        return $query->where('include_quota_associativa', 1);
    }

    /**
     * Scope: per periodo (mese/anno)
     */
    public function scopePerPeriodo($query, $anno, $mese = null)
    {
        $query->whereYear('data_pagamento', $anno);
        
        if ($mese) {
            $query->whereMonth('data_pagamento', $mese);
        }
        
        return $query;
    }

    /**
     * Get breakdown importo per UI
     * 
     * @return array
     */
    public function getBreakdown()
    {
        $breakdown = [];
        
        if ($this->include_quota_associativa && $this->importo_quota_associativa > 0) {
            $importoMensile = $this->importo_netto - $this->importo_quota_associativa;
            
            $breakdown[] = [
                'tipo' => 'Quota Mensile',
                'importo' => $importoMensile
            ];
            
            $breakdown[] = [
                'tipo' => 'Quota Associativa Annuale',
                'importo' => $this->importo_quota_associativa
            ];
        } else {
            $breakdown[] = [
                'tipo' => $this->tipoPagamento ? $this->tipoPagamento->nome : 'Pagamento',
                'importo' => $this->importo_netto
            ];
        }
        
        return $breakdown;
    }

    /**
     * Get descrizione completa pagamento
     * 
     * @return string
     */
    public function getDescrizioneAttribute()
    {
        $desc = $this->tipoPagamento ? $this->tipoPagamento->nome : 'Pagamento';
        
        if ($this->mese_riferimento) {
            $desc .= ' - ' . ucfirst($this->mese_riferimento);
        }
        
        if ($this->include_quota_associativa) {
            $desc .= ' (include Quota Associativa)';
        }
        
        return $desc;
    }

    /**
     * Get nome socio
     */
    public function getSocioNomeAttribute()
    {
        return $this->socio ? $this->socio->nome . ' ' . $this->socio->cognome : '';
    }

    /**
     * Calcola e imposta importo netto
     */
    public function calcolaImportoNetto()
    {
        $this->importo_netto = $this->importo - $this->sconto;
        return $this;
    }

    /**
     * Verifica se il pagamento è in ritardo
     * 
     * @return bool
     */
    public function isInRitardo()
    {
        if (!$this->data_scadenza || $this->stato === 'pagato') {
            return false;
        }
        
        return $this->data_scadenza < now();
    }

    /**
     * Get giorni di ritardo
     * 
     * @return int
     */
    public function getGiorniRitardo()
    {
        if (!$this->isInRitardo()) {
            return 0;
        }
        
        return now()->diffInDays($this->data_scadenza);
    }
}