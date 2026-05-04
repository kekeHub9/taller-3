<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte: Estado actual de equipos
     */
    public function estadoEquipos()
    {
        $equipos = Equipo::select('id', 'nombre', 'numero_serie', 'modelo', 'estado')
            ->orderBy('nombre')
            ->get();
        
        // Estadísticas rápidas
        $stats = [
            'total' => $equipos->count(),
            'activos' => $equipos->where('estado', 'Activo')->count(),
            'mantenimiento' => $equipos->where('estado', 'Reparación')->count(),
            'baja' => $equipos->where('estado', 'Baja')->count(),
        ];
        
        return view('reportes.estado-equipos', compact('equipos', 'stats'));
    }
    /**
    * Reporte: Equipos próximos a mantenimiento
    */
    public function mantenimientoProximo()
    {
    $proximosMantenimientos = Mantenimiento::with('equipo')
        ->where('estado', 'Pendiente')
        ->where('fecha_programada', '>=', now())
        ->where('fecha_programada', '<=', now()->addDays(30))
        ->orderBy('fecha_programada', 'asc')
        ->get();

    $stats = [
        'total' => 0,
        'proximos' => 0,
        'vencidos' => 0,
        'completados' => 0,
    ];

    return view('reportes.mantenimiento-proximo', compact('proximosMantenimientos', 'stats'));
    }
}

