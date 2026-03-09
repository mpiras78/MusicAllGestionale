<?php

namespace MusicAll\Models;

class Assenza extends Model
{
    protected $table = 'assenze';
    
    protected $casts = [
        'data_assenza' => 'date',
        'data_recupero' => 'date',
        'recuperata' => 'boolean',
    ];
    
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
    
    public function lezione()
    {
        return $this->belongsTo(Lezione::class, 'lezione_id');
    }
    
    public function scopeDaRecuperare($query)
    {
        return $query->where('recuperata', false);
    }
    
    public function scopeRecuperate($query)
    {
        return $query->where('recuperata', true);
    }
}