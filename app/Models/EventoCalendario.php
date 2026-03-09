<?php

namespace MusicAll\Models;

/**
 * Evento Calendario Model
 * 
 * @property int $id
 * @property int $tipologia_id
 * @property bool $ricorrente
 * @property string|null $giorno_settimana
 * @property string|null $data_evento
 * @property string|null $data_inizio
 * @property string|null $data_fine
 * @property string $ora_inizio
 * @property string $ora_fine
 * @property int $aula_id
 * @property int|null $docente_id
 * @property int|null $materia_id
 * @property int|null $socio_id
 * @property int|null $socio_occasionale_id
 * @property int|null $iscrizione_id
 * @property string|null $titolo
 * @property string|null $descrizione
 * @property string|null $note
 * @property bool $attivo
 * @property bool $confermato
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class EventoCalendario extends Model
{
    /**
     * Nome della tabella
     */
    protected $table = 'eventi_calendario';
    
    /**
     * Attributi mass-assignable
     */
    protected $fillable = [
        'tipologia_id', 'ricorrente', 'giorno_settimana', 'data_evento',
        'data_inizio', 'data_fine', 'ora_inizio', 'ora_fine',
        'aula_id', 'docente_id', 'materia_id', 'socio_id',
        'socio_occasionale_id', 'iscrizione_id',
        'titolo', 'descrizione', 'note', 'attivo', 'confermato',
        'created_by', 'updated_by'
    ];
    
    /**
     * Cast degli attributi
     */
    protected $casts = [
        'ricorrente' => 'boolean',
        'attivo' => 'boolean',
        'confermato' => 'boolean',
        'data_evento' => 'date',
        'data_inizio' => 'date',
        'data_fine' => 'date',
    ];
    
    /**
     * Relazione: Tipologia evento
     */
    public function tipologia()
    {
        return $this->belongsTo(TipologiaEvento::class, 'tipologia_id');
    }
    
    /**
     * Relazione: Aula
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
    
    /**
     * Relazione: Docente
     */
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
    
    /**
     * Relazione: Materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
    
    /**
     * Relazione: Socio
     */
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
    
    /**
     * Relazione: Iscrizione
     * TODO: Implementare quando necessario per fase 2
     */
    // public function iscrizione()
    // {
    //     return $this->belongsTo(Iscrizione::class, 'iscrizione_id');
    // }
    
    /**
     * Scope: Solo eventi attivi
     */
    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
    
    /**
     * Scope: Eventi confermati
     */
    public function scopeConfermati($query)
    {
        return $query->where('confermato', true);
    }
    
    /**
     * Scope: Eventi ricorrenti
     */
    public function scopeRicorrenti($query)
    {
        return $query->where('ricorrente', true);
    }
    
    /**
     * Scope: Eventi singoli
     */
    public function scopeSingoli($query)
    {
        return $query->where('ricorrente', false);
    }
    
    /**
     * Scope: Eventi per una specifica data
     */
    public function scopePerData($query, $date, $giornoSettimana = null)
    {
        if (!$giornoSettimana) {
            $mappaGiorni = [
                'Monday' => 'lunedi',
                'Tuesday' => 'martedi',
                'Wednesday' => 'mercoledi',
                'Thursday' => 'giovedi',
                'Friday' => 'venerdi',
                'Saturday' => 'sabato',
                'Sunday' => 'domenica'
            ];
            $giornoEng = date('l', strtotime($date));
            $giornoSettimana = $mappaGiorni[$giornoEng] ?? 'lunedi';
        }
        
        return $query->where(function($q) use ($date, $giornoSettimana) {
            $q->where(function($sq) use ($date) {
                // Eventi singoli per questa data
                $sq->where('ricorrente', false)
                   ->where('data_evento', $date);
            })
            ->orWhere(function($sq) use ($date, $giornoSettimana) {
                // Eventi ricorrenti per questo giorno
                $sq->where('ricorrente', true)
                   ->where('giorno_settimana', $giornoSettimana)
                   ->where(function($dsq) use ($date) {
                       $dsq->whereNull('data_inizio')
                          ->orWhere('data_inizio', '<=', $date);
                   })
                   ->where(function($dsq) use ($date) {
                       $dsq->whereNull('data_fine')
                          ->orWhere('data_fine', '>=', $date);
                   });
            });
        });
    }
    
    /**
     * Scope: Eventi per aula
     */
    public function scopePerAula($query, $aulaId)
    {
        return $query->where('aula_id', $aulaId);
    }
    
    /**
     * Scope: Eventi per docente
     */
    public function scopePerDocente($query, $docenteId)
    {
        return $query->where('docente_id', $docenteId);
    }
    
    /**
     * Scope: Eventi per socio
     */
    public function scopePerSocio($query, $sociId)
    {
        return $query->where('socio_id', $sociId);
    }
    
    /**
     * Accessor: Nome partecipante
     */
    public function getPartecipanteAttribute()
    {
        if ($this->socio) {
            return $this->socio->nome_completo;
        }
        // TODO: Aggiungere socio occasionale quando model sarà creato
        return null;
    }
}