<?php

namespace MusicAll\Models;

class User extends Model
{
    protected $table = 'users';
    
    protected $hidden = [
        'password',
    ];
    
    protected $casts = [
        'attivo' => 'boolean',
        'ultimo_accesso' => 'datetime',
    ];
    
    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
    
    public function scopePerRuolo($query, $ruolo)
    {
        return $query->where('ruolo', $ruolo);
    }
    
    public function isAdmin()
    {
        return $this->ruolo === 'admin';
    }
    
    public function isDocente()
    {
        return $this->ruolo === 'docente';
    }
    
    public function isSegreteria()
    {
        return $this->ruolo === 'segreteria';
    }
}