<?php

namespace MusicAll\Models;

/**
 * Model per iscrizioni
 * Gestione iscrizioni allievi per anno accademico
 */
class Iscrizione extends Model
{
    protected $table = 'iscrizioni';
    
    protected $fillable = [
        'allievo_id',
        'anno_accademico',
        'materia_id',
        'docente_id',
        'tipo_corso',
        'data_iscrizione',
        'data_inizio_corso',
        'data_fine_corso',
        'stato',
        'creato_da'
    ];

    protected $casts = [
        'data_iscrizione' => 'date',
        'data_inizio_corso' => 'date',
        'data_fine_corso' => 'date'
    ];

    /**
     * Relazione: allievo
     */
    public function allievo()
    {
        return $this->belongsTo(Allievo::class);
    }

    /**
     * Relazione: materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Relazione: docente
     */
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    /**
     * Relazione: pagamenti
     */
    public function pagamenti()
    {
        return $this->hasMany(Pagamento::class);
    }

    /**
     * Relazione: user che ha creato
     */
    public function creatore()
    {
        return $this->belongsTo(User::class, 'creato_da');
    }

    /**
     * Scope: iscrizioni attive
     */
    public function scopeAttive($query)
    {
        return $query->where('stato', 'attiva');
    }

    /**
     * Scope: per anno accademico
     */
    public function scopePerAnno($query, $annoAccademico)
    {
        return $query->where('anno_accademico', $annoAccademico);
    }

    /**
     * Scope: per allievo
     */
    public function scopePerAllievo($query, $allievoId)
    {
        return $query->where('allievo_id', $allievoId);
    }

    /**
     * Verifica se la quota associativa è già stata pagata
     * 
     * @return bool
     */
    public function hasQuotaAssociativa()
    {
        $tipoQuotaId = TipoPagamento::getQuotaAssociativa()->id;
        
        // Cerca pagamento quota associativa o pagamento con quota inclusa
        return Pagamento::where('allievo_id', $this->allievo_id)
            ->where('anno_accademico', $this->anno_accademico)
            ->where(function($q) use ($tipoQuotaId) {
                $q->where('tipo_pagamento_id', $tipoQuotaId)
                  ->orWhere('include_quota_associativa', 1);
            })
            ->where('stato', 'pagato')
            ->exists();
    }

    /**
     * Calcola importo iscrizione con check automatico quota
     * 
     * @return array
     */
    public function calcolaImportoIscrizione()
    {
        $config = ConfigurazioneTariffe::getPerAnno($this->anno_accademico);
        
        if (!$config) {
            throw new \Exception("Configurazione tariffe non trovata per {$this->anno_accademico}");
        }

        $includeQuota = !$this->hasQuotaAssociativa();
        
        return $config->calcolaImporto($this->tipo_corso, $includeQuota);
    }

    /**
     * Get nome completo allievo
     */
    public function getAllievoNomeAttribute()
    {
        return $this->allievo ? $this->allievo->nome . ' ' . $this->allievo->cognome : '';
    }

    /**
     * Get nome materia
     */
    public function getMateriaNomeAttribute()
    {
        return $this->materia ? $this->materia->nome : '';
    }

    /**
     * Get nome docente
     */
    public function getDocenteNomeAttribute()
    {
        return $this->docente ? $this->docente->cognome . ' ' . $this->docente->nome : '';
    }
}