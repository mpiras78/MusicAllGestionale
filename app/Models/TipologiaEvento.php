<?php

namespace MusicAll\Models;

/**
 * Tipologia Evento Model
 * 
 * @property int $id
 * @property string $categoria
 * @property string $codice
 * @property string $nome
 * @property string|null $descrizione
 * @property string $colore_bg
 * @property string $colore_border
 * @property string|null $icona
 * @property bool $attiva
 * @property int $ordine_visualizzazione
 * @property \Carbon\Carbon $created_at
 */
class TipologiaEvento extends Model
{
    /**
     * Nome della tabella
     */
    protected $table = 'tipologie_evento';
    
    /**
     * Attributi mass-assignable
     */
    protected $fillable = [
        'categoria', 'codice', 'nome', 'descrizione',
        'colore_bg', 'colore_border', 'icona',
        'attiva', 'ordine_visualizzazione'
    ];
    
    /**
     * Cast degli attributi
     */
    protected $casts = [
        'attiva' => 'boolean',
        'ordine_visualizzazione' => 'integer',
    ];
    
    /**
     * Relazione: Eventi di questa tipologia
     */
    public function eventi()
    {
        return $this->hasMany(EventoCalendario::class, 'tipologia_id');
    }
    
    /**
     * Scope: Solo tipologie attive
     */
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }
    
    /**
     * Scope: Tipologie per categoria
     */
    public function scopePerCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }
    
    /**
     * Scope: Ordinate per visualizzazione
     */
    public function scopeOrdinatePerVisualizzazione($query)
    {
        return $query->orderBy('ordine_visualizzazione')->orderBy('nome');
    }
}