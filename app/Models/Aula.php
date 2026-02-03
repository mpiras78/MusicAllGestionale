<?php

namespace MusicAll\Models;

class Aula extends Model
{
    protected $table = 'aule';
    
    protected $casts = [
        'ordine_visualizzazione' => 'integer',
        'attiva' => 'boolean',
    ];
    
    public function lezioni()
    {
        return $this->hasMany(Lezione::class, 'aula_id');
    }
    
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }
    
    public function scopeOrdinata($query)
    {
        return $query->orderBy('ordine_visualizzazione');
    }
}