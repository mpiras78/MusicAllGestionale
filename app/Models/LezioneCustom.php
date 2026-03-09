<?php

namespace MusicAll\Models;

class LezioneCustom extends Model
{
    protected $table = 'lezioni_custom';
    
    protected $casts = [
        'data' => 'date',
        'durata_minuti' => 'integer',
    ];
    
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
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
    
    public function scopePerData($query, $data)
    {
        return $query->whereDate('data', $data);
    }
    
    public function scopeFuture($query)
    {
        return $query->where('data', '>=', now());
    }
}