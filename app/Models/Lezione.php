<?php

namespace MusicAll\Models;

/**
 * Lezione Model
 * Lezione settimanale ricorrente
 */
class Lezione extends Model
{
    protected $table = 'lezioni';
    
    protected $casts = [
        'giorno_settimana' => 'integer',
        'durata_minuti' => 'integer',
        'attiva' => 'boolean',
    ];
    
    public function allievo()
    {
        return $this->belongsTo(Allievo::class, 'allievo_id');
    }
    
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
    
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
    
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
    
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }
    
    public function scopePerGiorno($query, $giorno)
    {
        return $query->where('giorno_settimana', $giorno);
    }
}