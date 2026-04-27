<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Mantenimiento;
use App\Models\Asignacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filtro por departamento
        $departamentoFiltro = $request->get('departamento');
        
        // Query base con filtro
        $equiposQuery = Equipo::query();
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $equiposQuery->where('departamento', $departamentoFiltro);
        }
        
        // Estadísticas principales
        $totalEquipos = $equiposQuery->count();
        $equiposActivos = (clone $equiposQuery)->where('estado', 'Activo')->count();
        $enReparacion = (clone $equiposQuery)->where('estado', 'Reparación')->count();
        
        // Mantenimientos pendientes
        $mantenimientosPendientes = Mantenimiento::where('estado', 'Pendiente')
            ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                $q->whereHas('equipo', function($sq) use ($departamentoFiltro) {
                    $sq->where('departamento', $departamentoFiltro);
                });
            })
            ->count();
        
        // Departamentos disponibles para el filtro
        $departamentosLista = Equipo::select('departamento')
            ->distinct()
            ->whereNotNull('departamento')
            ->pluck('departamento')
            ->toArray();
        sort($departamentosLista);
        array_unshift($departamentosLista, 'Todos');
        
        // Equipos por departamento
        $equiposPorDepartamento = Equipo::select('departamento', DB::raw('count(*) as total'))
            ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                $q->where('departamento', $departamentoFiltro);
            })
            ->groupBy('departamento')
            ->orderBy('total', 'desc')
            ->pluck('total', 'departamento')
            ->toArray();
        
        // Equipos por estado
        $equiposPorEstado = (clone $equiposQuery)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
        
        // Mantenimientos por mes - CORREGIDO PARA POSTGRESQL
        $mantenimientosPorMes = Mantenimiento::select(
                DB::raw("TO_CHAR(fecha_realizacion, 'YYYY-MM') as mes"),
                DB::raw('count(*) as total')
            )
            ->where('fecha_realizacion', '>=', now()->subMonths(6))
            ->whereNotNull('fecha_realizacion')
            ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                $q->whereHas('equipo', function($sq) use ($departamentoFiltro) {
                    $sq->where('departamento', $departamentoFiltro);
                });
            })
            ->groupBy(DB::raw("TO_CHAR(fecha_realizacion, 'YYYY-MM')"))
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();
        
        // Llenar meses faltantes
        $mesesCompletos = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i)->format('Y-m');
            $mesesCompletos[$mes] = $mantenimientosPorMes[$mes] ?? 0;
        }
        $mantenimientosPorMes = $mesesCompletos;
        
        // Costos por departamento
        $costosPorDepartamento = Equipo::select(
            'departamento',
            DB::raw('SUM(costo) as total_costo'),
            DB::raw('SUM(depreciacion) as total_depreciacion')
        )
        ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
            $q->where('departamento', $departamentoFiltro);
        })
        ->groupBy('departamento')
        ->having(DB::raw('SUM(costo)'), '>', 0)
        ->get();
        
        // Alertas
        $alertas = $this->generarAlertas($departamentoFiltro);
        
        // Últimos eventos
        $ultimosEventos = $this->getUltimosEventos($departamentoFiltro);
        
        // Equipos críticos
        $equiposCriticos = Equipo::where(function($query) {
                $query->where('estado', 'Reparación')
                    ->orWhere('estado', 'Mantenimiento')
                    ->orWhere(function($q) {
                        $q->where('proxima_calibracion', '<=', now()->addDays(7))
                          ->where('proxima_calibracion', '>=', now());
                    });
            })
            ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                $q->where('departamento', $departamentoFiltro);
            })
            ->orderBy('proxima_calibracion')
            ->limit(5)
            ->get();
        
        // Total departamentos
        $totalDepartamentos = count($equiposPorDepartamento);
        
        // Calcular crecimiento
        $valoresMeses = array_values($mantenimientosPorMes);
        $crecimiento = count($valoresMeses) > 1 ? 
            round((end($valoresMeses) - reset($valoresMeses)) / max(1, reset($valoresMeses)) * 100) : 0;
        
        return view('dashboard.index', compact(
            'totalEquipos',
            'mantenimientosPendientes', 
            'enReparacion',
            'equiposActivos',
            'totalDepartamentos',
            'equiposPorDepartamento',
            'equiposPorEstado',
            'mantenimientosPorMes',
            'costosPorDepartamento',
            'alertas',
            'ultimosEventos',
            'equiposCriticos',
            'departamentosLista',
            'departamentoFiltro',
            'crecimiento'
        ));
    }
    
    private function generarAlertas($departamentoFiltro = null)
    {
        $alertas = [];
        $now = now();
        
        // 1. Equipos con calibración próxima
        $equiposQuery = Equipo::where('proxima_calibracion', '>=', $now)
            ->where('proxima_calibracion', '<=', $now->copy()->addDays(7));
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $equiposQuery->where('departamento', $departamentoFiltro);
        }
        
        $equiposCalibracion = $equiposQuery->get();
            
        foreach ($equiposCalibracion as $equipo) {
            $dias = $now->diffInDays($equipo->proxima_calibracion);
            $alertas[] = [
                'tipo' => 'calibracion',
                'icono' => 'fas fa-calendar-check',
                'equipo' => $equipo->nombre,
                'serie' => $equipo->numero_serie,
                'detalle' => "Calibración programada para " . $equipo->proxima_calibracion->format('d/m/Y'),
                'fecha' => $equipo->proxima_calibracion->format('d/m/Y'),
                'prioridad' => $dias < 3 ? 'alta' : ($dias < 7 ? 'media' : 'baja'),
                'dias_restantes' => $dias
            ];
        }
        
        // 2. Mantenimientos pendientes vencidos
        $mantenimientosQuery = Mantenimiento::where('estado', 'Pendiente')
            ->where('fecha_programada', '<', $now)
            ->with('equipo');
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $mantenimientosQuery->whereHas('equipo', function($q) use ($departamentoFiltro) {
                $q->where('departamento', $departamentoFiltro);
            });
        }
        
        $mantenimientosVencidos = $mantenimientosQuery->get();
            
        foreach ($mantenimientosVencidos as $mantenimiento) {
            if ($mantenimiento->equipo) {
                $alertas[] = [
                    'tipo' => 'mantenimiento',
                    'icono' => 'fas fa-tools',
                    'equipo' => $mantenimiento->equipo->nombre,
                    'serie' => $mantenimiento->equipo->numero_serie,
                    'detalle' => 'Mantenimiento vencido desde ' . $mantenimiento->fecha_programada->format('d/m/Y'),
                    'fecha' => $mantenimiento->fecha_programada->format('d/m/Y'),
                    'prioridad' => 'alta',
                    'dias_vencido' => $now->diffInDays($mantenimiento->fecha_programada)
                ];
            }
        }
        
        // 3. Equipos en reparación por mucho tiempo
        $equiposReparacionQuery = Equipo::where('estado', 'Reparación')
            ->where('updated_at', '<', $now->copy()->subDays(30));
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $equiposReparacionQuery->where('departamento', $departamentoFiltro);
        }
        
        $equiposReparacionLarga = $equiposReparacionQuery->get();
            
        foreach ($equiposReparacionLarga as $equipo) {
            $dias = $now->diffInDays($equipo->updated_at);
            $alertas[] = [
                'tipo' => 'reparacion',
                'icono' => 'fas fa-wrench',
                'equipo' => $equipo->nombre,
                'serie' => $equipo->numero_serie,
                'detalle' => "En reparación por {$dias} días",
                'fecha' => $equipo->updated_at->format('d/m/Y'),
                'prioridad' => 'media',
                'dias_reparacion' => $dias
            ];
        }
        
        usort($alertas, function($a, $b) {
            $prioridades = ['alta' => 3, 'media' => 2, 'baja' => 1];
            return $prioridades[$b['prioridad']] <=> $prioridades[$a['prioridad']];
        });
        
        return array_slice($alertas, 0, 10);
    }
    
    private function getUltimosEventos($departamentoFiltro = null)
    {
        $eventos = collect();
        
        // Mantenimientos
        $mantenimientosQuery = Mantenimiento::with('equipo')
            ->whereNotNull('fecha_realizacion')
            ->orderBy('fecha_realizacion', 'desc');
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $mantenimientosQuery->whereHas('equipo', function($q) use ($departamentoFiltro) {
                $q->where('departamento', $departamentoFiltro);
            });
        }
        
        $eventos = $eventos->merge($mantenimientosQuery->limit(5)->get()->map(function($mant) {
            return [
                'tipo' => 'mantenimiento',
                'icono' => 'fas fa-tools',
                'color' => 'success',
                'descripcion' => "Mantenimiento realizado a " . ($mant->equipo->nombre ?? 'N/A'),
                'fecha' => $mant->fecha_realizacion->format('d/m/Y H:i'),
                'tecnico' => $mant->tecnico ?? 'No especificado'
            ];
        }));
        
        // Asignaciones
        $asignacionesQuery = Asignacion::with('equipo')->orderBy('created_at', 'desc');
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $asignacionesQuery->whereHas('equipo', function($q) use ($departamentoFiltro) {
                $q->where('departamento', $departamentoFiltro);
            });
        }
        
        $eventos = $eventos->merge($asignacionesQuery->limit(5)->get()->map(function($asig) {
            return [
                'tipo' => 'asignacion',
                'icono' => 'fas fa-handshake',
                'color' => 'primary',
                'descripcion' => "Equipo " . ($asig->equipo->nombre ?? '') . " asignado a " . ($asig->departamento ?? 'Sin departamento'),
                'fecha' => $asig->created_at->format('d/m/Y H:i'),
                'responsable' => $asig->responsable ?? 'No especificado'
            ];
        }));
        
        // Equipos nuevos
        $equiposQuery = Equipo::orderBy('created_at', 'desc');
        
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $equiposQuery->where('departamento', $departamentoFiltro);
        }
        
        $eventos = $eventos->merge($equiposQuery->limit(5)->get()->map(function($eq) {
            return [
                'tipo' => 'equipo',
                'icono' => 'fas fa-microscope',
                'color' => 'info',
                'descripcion' => "Nuevo equipo registrado: " . $eq->nombre,
                'fecha' => $eq->created_at->format('d/m/Y H:i'),
                'serie' => $eq->numero_serie
            ];
        }));
        
        return $eventos->sortByDesc('fecha')->take(8);
    }
    
    // API para actualización en tiempo real
    public function apiData(Request $request)
    {
        $departamentoFiltro = $request->get('departamento');
        
        $equiposQuery = Equipo::query();
        if ($departamentoFiltro && $departamentoFiltro !== 'Todos') {
            $equiposQuery->where('departamento', $departamentoFiltro);
        }
        
        // Mantenimientos por mes para API - CORREGIDO POSTGRESQL
        $mantenimientosData = Mantenimiento::select(
                DB::raw("TO_CHAR(fecha_realizacion, 'YYYY-MM') as mes"),
                DB::raw('count(*) as total')
            )
            ->where('fecha_realizacion', '>=', now()->subMonths(6))
            ->whereNotNull('fecha_realizacion')
            ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                $q->whereHas('equipo', function($sq) use ($departamentoFiltro) {
                    $sq->where('departamento', $departamentoFiltro);
                });
            })
            ->groupBy(DB::raw("TO_CHAR(fecha_realizacion, 'YYYY-MM')"))
            ->orderBy('mes')
            ->pluck('total')
            ->toArray();
        
        // Completar meses faltantes para API
        $mesesCompletosAPI = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesesCompletosAPI[] = $mantenimientosData[$i] ?? 0;
        }
        
        return response()->json([
            'counters' => [
                (clone $equiposQuery)->where('estado', 'Activo')->count(),
                Mantenimiento::where('estado', 'Pendiente')
                    ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                        $q->whereHas('equipo', fn($sq) => $sq->where('departamento', $departamentoFiltro));
                    })->count(),
                (clone $equiposQuery)->where('estado', 'Reparación')->count(),
                (clone $equiposQuery)->distinct('departamento')->count('departamento')
            ],
            'equiposPorDepartamento' => array_values(
                Equipo::select('departamento', DB::raw('count(*) as total'))
                    ->when($departamentoFiltro && $departamentoFiltro !== 'Todos', function($q) use ($departamentoFiltro) {
                        $q->where('departamento', $departamentoFiltro);
                    })
                    ->groupBy('departamento')
                    ->pluck('total')
                    ->toArray()
            ),
            'equiposPorEstado' => array_values(
                (clone $equiposQuery)
                    ->select('estado', DB::raw('count(*) as total'))
                    ->groupBy('estado')
                    ->pluck('total')
                    ->toArray()
            ),
            'mantenimientosPorMes' => $mesesCompletosAPI
        ]);
    }
}