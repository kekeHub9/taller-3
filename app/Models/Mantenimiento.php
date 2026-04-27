<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipo_id',
        'tipo',
        'fecha_programada',
        'fecha_realizacion',
        'tecnico',
        'costo',
        'descripcion',
        'solucion',
        'tiempo_inactivo',
        'estado'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizacion' => 'date',
        'costo' => 'decimal:2',
        'tiempo_inactivo' => 'integer'
    ];

    /**
     * Valores por defecto para los atributos
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'estado' => 'Pendiente',
        'costo' => 0.00,
        'tiempo_inactivo' => 0
    ];

    /**
     * un mantenimiento pertenece a un equipo.
     *
     * @return BelongsTo
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * para mantenimientos pendientes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    /**
     * para mantenimientos completados.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'Completado');
    }

    /**
     * para mantenimientos por tipo. filtropredefinido de laravel aaea
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $tipo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Verificar si el mantenimiento está vencido.
     *
     * @return bool
     */
    public function estaVencido(): bool
    {
        return $this->estado === 'Pendiente' && 
               $this->fecha_programada < now();
    }

    /**
     * Calcular días de retraso, aunq ni se usa aea
     *
     * @return int|null
     */
    public function diasRetraso(): ?int
    {
        if (!$this->estaVencido()) {
            return null;
        }

        return now()->diffInDays($this->fecha_programada);
    }

    /**
     * Obtener el color según el estado.
     *
     * @return string
     */
    public function colorEstado(): string
    {
        return match($this->estado) {
            'Pendiente' => 'warning',
            'En proceso' => 'info',
            'Completado' => 'success',
            'Cancelado' => 'secondary',
            default => 'light'
        };
    }
}