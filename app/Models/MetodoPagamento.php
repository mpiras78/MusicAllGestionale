<?php

namespace MusicAll\Models;

/**
 * Model per metodi_pagamento
 * Tabella tipologica per modalità di pagamento
 */
class MetodoPagamento extends Model
{
    protected $table = 'metodi_pagamento';
    
    protected $fillable = [
        'codice',
        'nome',
        'descrizione',
        'richiede_riferimento',
        'attivo',
        'ordinamento'
    ];

    protected $casts = [
        'richiede_riferimento' => 'boolean',
        'attivo' => 'boolean',
        'ordinamento' => 'integer'
    ];

    /**
     * Scope per metodi attivi
     */
    public function scopeAttivi($query)
    {
        return $query->where('attivo', 1);
    }

    /**
     * Scope per ordinamento
     */
    public function scopeOrdinati($query)
    {
        return $query->orderBy('ordinamento', 'asc');
    }

    /**
     * Get metodo CONTANTI
     */
    public static function getContanti()
    {
        return self::where('codice', 'CONTANTI')->first();
    }

    /**
     * Get metodo BONIFICO
     */
    public static function getBonifico()
    {
        return self::where('codice', 'BONIFICO')->first();
    }

    /**
     * Relazione: pagamenti con questo metodo
     */
    public function pagamenti()
    {
        return $this->hasMany(Pagamento::class, 'metodo_pagamento_id');
    }
}