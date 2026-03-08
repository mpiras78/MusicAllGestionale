<?php

namespace MusicAll\Models;

/**
 * Allievo Model
 * 
 * @property int $id
 * @property string $cognome
 * @property string $nome
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $data_nascita
 * @property string|null $indirizzo
 * @property string|null $note
 * @property bool $attivo
 * @property \Carbon\Carbon $created_at
 */
class Allievo extends Model
{
    /**
     * Nome della tabella (ora `soci`, mantiene il model `Allievo` per retrocompatibilità)
     */
    protected $table = 'soci';
    
    /**
     * Cast degli attributi
     */
    protected $casts = [
        'attivo' => 'boolean',
        'data_nascita' => 'date',
    ];
    
    /**
     * Relazione: Lezioni dell'allievo
     */
    public function lezioni()
    {
        return $this->hasMany(Lezione::class, 'socio_id');
    }
    
    /**
     * Relazione: Assenze dell'allievo
     */
    public function assenze()
    {
        return $this->hasMany(Assenza::class, 'socio_id');
    }
    
    /**
     * Relazione: Lezioni custom
     */
    public function lezioniCustom()
    {
        return $this->hasMany(LezioneCustom::class, 'socio_id');
    }
    
    /**
     * Scope: Solo allievi attivi
     */
    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
    
    /**
     * Accessor: Nome completo
     */
    public function getNomeCompletoAttribute()
    {
        return trim($this->cognome . ' ' . $this->nome);
    }
}