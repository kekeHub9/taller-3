<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    
    protected $fillable = [
        'equipo_id',
        'departamento',
        'responsable',
        'cargo',
        'fecha_asignacion',
        'fecha_devolucion',  // ← AGREGAR ESTE
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_devolucion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    // Calcular días transcurridos
    public function getDiasTranscurridosAttribute(): int
    {
        $inicio = Carbon::parse($this->fecha_asignacion);
        $fin = $this->fecha_devolucion ? Carbon::parse($this->fecha_devolucion) : Carbon::now();
        return $inicio->diffInDays($fin);
    }

    // Scope para asignaciones activas
    public function scopeActivas($query)
    {
        return $query->where('estado', 'Activa');
    }

    // Scope para historial de un equipo
    public function scopeHistorialEquipo($query, $equipoId)
    {
        return $query->where('equipo_id', $equipoId)
                     ->orderBy('fecha_asignacion', 'desc');
    }
}