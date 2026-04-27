<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asignacion extends Model
{
    //  ESPECIFICA la tabla (esto es lo más importante)
    protected $table = 'asignaciones';
    
    //  Fillable
    protected $fillable = [
        'equipo_id',
        'departamento',
        'responsable',
        'fecha_asignacion',
        'observaciones'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Esto escribirá en storage/logs/laravel.log
        \Log::info('DEBUG - Tabla de Asignacion: ' . $this->getTable());
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}