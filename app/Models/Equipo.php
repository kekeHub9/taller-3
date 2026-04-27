<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipo extends Model
{
    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_serie',
        'nombre',
        'tipo',
        'marca',
        'modelo',
        'fecha_adquisicion',
        'proveedor',
        'costo',
        'departamento',
        'estado',
        'ultima_calibracion',
        'proxima_calibracion'
    ];

    /**
     * Los atributos que deben ser convertidos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_adquisicion' => 'date',
        'ultima_calibracion' => 'date',
        'proxima_calibracion' => 'date',
        'costo' => 'decimal:2'
    ];

    /**
     * Valores por defecto para los atributos.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'estado' => 'Activo',
        'costo' => 0.00
    ];

    /**Un equipo tiene muchas asignaciones.
     *
     * @return HasMany
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'equipo_id');
    }

    /**
     * Un equipo tiene muchos mantenimientos.
     *
     * @return HasMany
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'equipo_id');
    }

    /** Un equipo tiene una asignación activa.
     *
     * @return HasOne
     */
    public function asignacionActiva(): HasOne
    {
        return $this->hasOne(Asignacion::class, 'equipo_id')
            ->where('estado', 'Activa');
    }

    /**
     * para equipos activos.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * filtro para equipos disponibles (sin asignación activa).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisponibles($query)
    {
        return $query->whereDoesntHave('asignacionActiva');
    }

    /**
     * Verificar si el equipo está disponible para asignación.
     *
     * @return bool
     */
    public function estaDisponible(): bool
    {
        return $this->estado === 'Activo' && 
               $this->asignacionActiva === null;
    }

    /**
     * Obtener el último mantenimiento preventivo.
     *
     * @return Mantenimiento|null
     */
    public function ultimoMantenimientoPreventivo(): ?Mantenimiento
    {
        return $this->mantenimientos()
            ->where('tipo', 'Preventivo')
            ->where('estado', 'Completado')
            ->latest('fecha_realizacion')
            ->first();
    }

    /**
     * Verificar si necesita calibración.
     *
     * @return bool
     */
    public function necesitaCalibracion(): bool
    {
        if (!$this->proxima_calibracion) {
            return false;
        }

        return $this->proxima_calibracion <= now();
    }

    /**
     * Calcular días hasta próxima calibración.
     *
     * @return int|null
     */
    public function diasHastaCalibracion(): ?int
    {
        if (!$this->proxima_calibracion) {
            return null;
        }

        return now()->diffInDays($this->proxima_calibracion, false);
    }

    /**
     * Obtener el color según el estado.
     *
     * @return string
     */
    public function colorEstado(): string
    {
        return match($this->estado) {
            'Activo' => 'success',
            'Inactivo' => 'secondary',
            'Reparación' => 'warning',
            'Baja' => 'danger',
            default => 'light'
        };
    }
}