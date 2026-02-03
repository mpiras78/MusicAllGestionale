<?php

namespace MusicAll\Models;

class Materia extends Model
{
    protected $table = 'materie';
    
    protected $casts = [
        'durata_standard' => 'integer',
        'attiva' => 'boolean',
    ];
    
    public function lezioni()
    {
        return $this->hasMany(Lezione::class, 'materia_id');
    }
    
    public function docenti()
    {
        return $this->belongsToMany(Docente::class, 'docenti_materie', 'materia_id', 'docente_id');
    }
    
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }
    
    public function scopePerCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}