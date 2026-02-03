<?php

namespace MusicAll\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Base Model
 * Classe base per tutti i models
 */
class Model extends EloquentModel
{
    /**
     * Indica se il model usa timestamps
     */
    public $timestamps = true;
    
    /**
     * Nome delle colonne timestamp
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    /**
     * Attributi che possono essere mass-assigned
     */
    protected $guarded = ['id'];
    
    /**
     * Attributi nascosti per serializzazione
     */
    protected $hidden = [];
    
    /**
     * Cast automatici degli attributi
     */
    protected $casts = [];
}