<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Services\ReportFactory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsignacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Asignacion::with('equipo');
        
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('responsable')) {
            $query->where('responsable', 'ilike', "%{$request->responsable}%");
        }
        
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_asignacion', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_asignacion', '<=', $request->fecha_hasta);
        }

        $orden = $request->input('orden', 'fecha_asignacion');
        $direccion = $request->input('direccion', 'desc');
        $query->orderBy($orden, $direccion);
        
        $asignaciones = $query->paginate(15);
        
    
        $estadisticas = $this->calcularEstadisticas();
        
        $departamentos = Asignacion::select('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');
        
        return view('asignaciones.index', compact('asignaciones', 'estadisticas', 'departamentos'));
    }
    
    public function create()
    {
        // Equipos disponibles (no asignados actualmente)
        $equiposDisponibles = Equipo::where('estado', 'Activo')
            ->whereDoesntHave('asignaciones', function($query) {
                $query->where('estado', 'Activa');
            })
            ->orderBy('nombre')
            ->get();
        
        // Departamentos predefinidos
        $departamentos = [
            'UCI', 'UCI Neonatal', 'Cardiología', 'Neurología',
            'Laboratorio', 'Emergencias', 'Quirófano', 'Imagenología'
        ];
        
        sort($departamentos);
        
        return view('asignaciones.create', compact('equiposDisponibles', 'departamentos'));
    }
    
    
     //Guardar nueva asignación
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'departamento' => 'required|string|max:100',
            'responsable' => 'required|string|max:100',
            'cargo' => 'required|string|max:100',
            'fecha_asignacion' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);
        
        $validated['estado'] = 'Activa';
        
        // Verificar que el equipo no esté ya asignado
        $asignacionActiva = Asignacion::where('equipo_id', $request->equipo_id)
            ->where('estado', 'Activa')
            ->exists();
            
        if ($asignacionActiva) {
            return back()->withErrors([
                'equipo_id' => 'Este equipo ya tiene una asignación activa'
            ])->withInput();
        }
        
        Asignacion::create($validated);
        
        Equipo::where('id', $request->equipo_id)
            ->update(['departamento' => $request->departamento]);
        
        return redirect()->route('asignaciones.index')
            ->with('success', '✅ Equipo asignado exitosamente');
    }
    
    /**
     * Devolver equipo (marcar asignación como completada)
     */
    public function devolver($id)
    {
        $asignacion = Asignacion::findOrFail($id);
        
        if ($asignacion->estado != 'Activa') {
            return back()->with('error', '❌ Esta asignación ya fue devuelta o está vencida');
        }
        
        $asignacion->update([
            'fecha_devolucion' => now(),
            'estado' => 'Devuelta'
        ]);
        
        return back()->with('success', '✅ Equipo devuelto exitosamente');
    }
    
    public function exportar(Request $request)
    {
        $formato = $request->input('formato', 'pdf');
        
        try {
            // Obtener datos para el reporte
            $datos = $this->prepararDatosReporte($request);
            
            // Factory Method pa crear el exportador adecuado
            $exportador = ReportFactory::create($formato);
            
            // Exportar usando la interfaz común
            return $exportador->export($datos);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }
    
    
    public function historial($equipo_id)
    {
        $equipo = Equipo::findOrFail($equipo_id);
        $historial = Asignacion::where('equipo_id', $equipo_id)
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(10);
        
        return view('asignaciones.historial', compact('equipo', 'historial'));
    }
    
    private function calcularEstadisticas()
    {
        $total = Asignacion::count();
        $activas = Asignacion::where('estado', 'Activa')->count();
        $devueltas = Asignacion::where('estado', 'Devuelta')->count();
        $vencidas = Asignacion::where('estado', 'Vencida')->count();
        
        $promedioDias = 0;
        $asignacionesConDevolucion = Asignacion::whereNotNull('fecha_devolucion')->get();
        
        if ($asignacionesConDevolucion->count() > 0) {
            $sumaDias = 0;
            foreach ($asignacionesConDevolucion as $asignacion) {
                try {
                    
                    $fechaInicio = Carbon::parse($asignacion->fecha_asignacion);
                    $fechaFin = Carbon::parse($asignacion->fecha_devolucion);
                    $sumaDias += $fechaInicio->diffInDays($fechaFin);
                } catch (\Exception $e) {
                    // Si falla, continuar con la siguiente
                    continue;
                }
            }
            
            if ($sumaDias > 0) {
                $promedioDias = $sumaDias / $asignacionesConDevolucion->count();
            }
        }
        
        return [
            'total' => $total,
            'activas' => $activas,
            'devueltas' => $devueltas,
            'vencidas' => $vencidas,
            'promedio_dias' => round($promedioDias, 1),
            'porcentaje_activas' => $total > 0 ? round(($activas / $total) * 100, 1) : 0
        ];
    }
    
    private function prepararDatosReporte(Request $request)
    {
        $query = Asignacion::with('equipo');
        
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_asignacion', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_asignacion', '<=', $request->fecha_hasta);
        }
        
        $asignaciones = $query->get();
        
        return [
            'asignaciones' => $asignaciones,
            'filtros' => $request->only(['departamento', 'fecha_desde', 'fecha_hasta']),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'total_registros' => $asignaciones->count(),
            'estadisticas' => $this->calcularEstadisticasReporte($asignaciones)
        ];
    }
    
    private function calcularEstadisticasReporte($asignaciones)
    {
        $porDepartamento = $asignaciones->groupBy('departamento')
            ->map(function($items, $depto) {
                return [
                    'departamento' => $depto,
                    'cantidad' => $items->count(),
                    'activas' => $items->where('estado', 'Activa')->count(),
                    'promedio_dias' => $items->whereNotNull('fecha_devolucion')
                        ->avg(function($item) {
                            try {
                                $fechaInicio = Carbon::parse($item->fecha_asignacion);
                                $fechaFin = Carbon::parse($item->fecha_devolucion);
                                return $fechaInicio->diffInDays($fechaFin);
                            } catch (\Exception $e) {
                                return 0;
                            }
                        }) ?? 0
                ];
            })
            ->sortByDesc('cantidad')
            ->take(5);
        
        $porEstado = $asignaciones->groupBy('estado')
            ->map(function($items, $estado) use ($asignaciones) {
                return [
                    'estado' => $estado,
                    'cantidad' => $items->count(),
                    'porcentaje' => $asignaciones->count() > 0 ? 
                        round(($items->count() / $asignaciones->count()) * 100, 1) : 0
                ];
            });
        
        return [
            'por_departamento' => $porDepartamento,
            'por_estado' => $porEstado,
            'total_asignaciones' => $asignaciones->count(),
            'equipos_diferentes' => $asignaciones->pluck('equipo_id')->unique()->count()
        ];
    }
    
    private function detectarAsignacionesVencidas()
    {
        $hoy = now();
        $asignacionesVencidas = Asignacion::where('estado', 'Activa')->get()
            ->filter(function($asignacion) use ($hoy) {
                try {
                    $fechaAsignacion = Carbon::parse($asignacion->fecha_asignacion);
                    return $fechaAsignacion->diffInDays($hoy) > 30;
                } catch (\Exception $e) {
                    return false;
                }
            });
            
        foreach ($asignacionesVencidas as $asignacion) {
            $asignacion->update(['estado' => 'Vencida']);
        }
        
        return $asignacionesVencidas->count();
    }
    
    private function estadisticasPorMes($year = null)
    {
        $year = $year ?? date('Y');
        
        return Asignacion::selectRaw("
                EXTRACT(MONTH FROM fecha_asignacion) as mes,
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'Activa' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN estado = 'Devuelta' THEN 1 ELSE 0 END) as devueltas
            ")
            ->whereYear('fecha_asignacion', $year)
            ->groupByRaw("EXTRACT(MONTH FROM fecha_asignacion)")
            ->orderBy('mes')
            ->get();
    }
}