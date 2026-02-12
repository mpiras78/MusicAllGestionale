<?php

namespace MusicAll\Models;

/**
 * Model per tipi_pagamento
 * Tabella tipologica per categorie di pagamento
 */
class TipoPagamento extends Model
{
    protected $table = 'tipi_pagamento';
    
    protected $fillable = [
        'codice',
        'nome',
        'descrizione',
        'richiede_mese',
        'richiede_iscrizione',
        'categoria_contabile',
        'attivo',
        'ordinamento'
    ];

    protected $casts = [
        'richiede_mese' => 'boolean',
        'richiede_iscrizione' => 'boolean',
        'attivo' => 'boolean',
        'ordinamento' => 'integer'
    ];

    /**
     * Scope per tipi attivi
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
     * Get tipo pagamento QUOTA_ASSOCIATIVA
     */
    public static function getQuotaAssociativa()
    {
        return self::where('codice', 'QUOTA_ASSOCIATIVA')->first();
    }

    /**
     * Get tipo pagamento MENSILE
     */
    public static function getMensile()
    {
        return self::where('codice', 'MENSILE')->first();
    }

    /**
     * Get tipo pagamento EXTRA
     */
    public static function getExtra()
    {
        return self::where('codice', 'EXTRA')->first();
    }

    /**
     * Relazione: pagamenti di questo tipo
     */
    public function pagamenti()
    {
        return $this->hasMany(Pagamento::class, 'tipo_pagamento_id');
    }
}