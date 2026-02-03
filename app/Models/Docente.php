<?php

namespace MusicAll\Models;

/**
 * Docente Model
 * 
 * @property int $id
 * @property string $cognome
 * @property string $nome
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $specializzazione
 * @property bool $attivo
 */
class Docente extends Model
{
    protected $table = 'docenti';
    
    protected $casts = [
        'attivo' => 'boolean',
    ];
    
    /**
     * Relazione: Lezioni del docente
     */
    public function lezioni()
    {
        return $this->hasMany(Lezione::class, 'docente_id');
    }
    
    /**
     * Relazione: Materie insegnate
     */
    public function materie()
    {
        return $this->belongsToMany(Materia::class, 'docenti_materie', 'docente_id', 'materia_id');
    }
    
    /**
     * Scope: Solo docenti attivi
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