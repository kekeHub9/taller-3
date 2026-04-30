<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Equipo;
use App\Services\NotificacionService; 
use Illuminate\Http\Request;
use Carbon\Carbon;

class MantenimientoController extends Controller
{
    protected $notificacionService;
    
    // crea una instancia y ve q necesita el service
    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }
    
    /**
     * Agenda de mantenimientos
     */
    public function agenda(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        
        $mantenimientos = Mantenimiento::with('equipo')
            ->whereBetween('fecha_programada', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_programada')
            ->get()
            ->groupBy(function($mant) {
                return Carbon::parse($mant->fecha_programada)->format('Y-m-d');
            });
        
        $equipos = Equipo::where('estado', '!=', 'Baja')->get();
        
        // Alertas automáticas 
        $alertas = $this->notificacionService->obtenerAlertasMantenimiento();
        
        return view('mantenimientos.agenda', compact('mantenimientos', 'equipos', 'fechaInicio', 'fechaFin', 'alertas'));
    }
    
    /**
     * Registro/Historial de mantenimientos (MODIFICADO - muestra TODOS los mantenimientos)
     */
    public function registro(Request $request)
    {
        // Cambio: mostrar TODOS los mantenimientos, no solo correctivos
        $query = Mantenimiento::with('equipo');
        
        // Filtros
        if ($request->filled('equipo_id')) {
            $query->where('equipo_id', $request->equipo_id);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $mantenimientos = $query->orderBy('fecha_programada', 'desc')->paginate(15);
        $equipos = Equipo::all();
        
        // Estadísticas generales mejoradas
        $estadisticas = [
            'total_registros' => Mantenimiento::count(),
            'total_incidencias' => Mantenimiento::where('tipo', 'Correctivo')->count(),
            'costo_total' => Mantenimiento::sum('costo') ?? 0,
            'tiempo_total' => Mantenimiento::sum('tiempo_inactivo') ?? 0,
            'pendientes' => Mantenimiento::where('estado', 'Pendiente')->count(),
            'completados' => Mantenimiento::where('estado', 'Completado')->count(),
            'promedio_reparacion' => Mantenimiento::where('tipo', 'Correctivo')->avg('tiempo_inactivo') ?? 0
        ];
        
        return view('mantenimientos.registro', compact('mantenimientos', 'equipos', 'estadisticas'));
    }
    
    /**
     * NUEVO MÉTODO: Obtener historial completo de un equipo (para el modal)
     */
    public function historialEquipo($id)
    {
        $equipo = Equipo::with(['mantenimientos' => function($query) {
            $query->orderBy('fecha_programada', 'desc');
        }])->findOrFail($id);
        
        $historial = $equipo->mantenimientos->map(function($mant) {
            return [
                'id' => $mant->id,
                'tipo' => $mant->tipo,
                'estado' => $mant->estado,
                'fecha_programada' => $mant->fecha_programada ? Carbon::parse($mant->fecha_programada)->format('d/m/Y') : 'N/A',
                'fecha_realizacion' => $mant->fecha_realizacion ? Carbon::parse($mant->fecha_realizacion)->format('d/m/Y') : 'Pendiente',
                'tecnico' => $mant->tecnico ?? 'No asignado',
                'descripcion' => $mant->descripcion ?? 'Sin descripción',
                'solucion' => $mant->solucion ?? 'No registrada',
                'costo' => number_format($mant->costo ?? 0, 2),
                'tiempo_inactivo' => $mant->tiempo_inactivo ?? 0,
            ];
        });
        
        return response()->json([
            'equipo' => [
                'nombre' => $equipo->nombre,
                'numero_serie' => $equipo->numero_serie ?? 'N/A',
                'modelo' => $equipo->modelo ?? 'N/A'
            ],
            'historial' => $historial
        ]);
    }
    
    /**
     * Guardar nuevo mantenimiento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'tipo' => 'required|in:Preventivo,Correctivo,Calibración',
            'fecha_programada' => 'required|date',
            'tecnico' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'costo' => 'nullable|numeric|min:0',
            'tiempo_inactivo' => 'nullable|integer|min:0'
        ]);
        
        $validated['estado'] = 'Pendiente';
        
        $mantenimiento = Mantenimiento::create($validated);
        
        $this->notificacionService->notificarNuevoMantenimiento($mantenimiento);
        
        if ($request->tipo == 'Correctivo') {
            Equipo::where('id', $request->equipo_id)->update(['estado' => 'Reparación']);
        }
        
        return redirect()->route('mantenimientos.agenda')
            ->with('success', 'Mantenimiento programado exitosamente');
    }
    
    /**
     * Actualizar mantenimiento
     */
    public function update(Request $request, $id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        
        $validated = $request->validate([
            'fecha_realizacion' => 'required|date',
            'solucion' => 'required|string',
            'costo' => 'required|numeric|min:0',
            'tiempo_inactivo' => 'required|integer|min:0',
            'estado' => 'required|in:Completado,Cancelado'
        ]);
        
        $mantenimiento->update($validated);
        
        $this->notificacionService->notificarMantenimientoCompletado($mantenimiento);
        
        if ($mantenimiento->tipo == 'Correctivo' && $request->estado == 'Completado') {
            Equipo::where('id', $mantenimiento->equipo_id)->update(['estado' => 'Activo']);
            
            if ($mantenimiento->tipo == 'Calibración') {
                $proxima = Carbon::parse($request->fecha_realizacion)->addYear();
                Equipo::where('id', $mantenimiento->equipo_id)->update([
                    'ultima_calibracion' => $request->fecha_realizacion,
                    'proxima_calibracion' => $proxima
                ]);
            }
        }
        
        return redirect()->route('mantenimientos.registro')
            ->with('success', 'Mantenimiento actualizado exitosamente');
    }
    
    /**
     * Eliminar mantenimiento
     */
    public function destroy($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        
        // Solo permitir eliminar si está pendiente
        if ($mantenimiento->estado != 'Pendiente') {
            return back()->with('error', 'Solo se pueden eliminar mantenimientos pendientes');
        }
        
        $mantenimiento->delete();
        
        return redirect()->route('mantenimientos.agenda')
            ->with('success', 'Mantenimiento eliminado exitosamente');
    }
    
    /**
     * Completar mantenimiento rápidamente
     */
    public function completar($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        
        $mantenimiento->update([
            'fecha_realizacion' => now(),
            'solucion' => 'Mantenimiento preventivo realizado según protocolo',
            'estado' => 'Completado'
        ]);
        
        $this->notificacionService->notificarMantenimientoCompletado($mantenimiento);
        
        return back()->with('success', 'Mantenimiento marcado como completado');
    }
    
    /**
     * API para el calendario
     */
    public function apiCalendario(Request $request)
    {
        $start = $request->input('start', now()->startOfMonth());
        $end = $request->input('end', now()->endOfMonth());
        
        $mantenimientos = Mantenimiento::with('equipo')
            ->whereBetween('fecha_programada', [$start, $end])
            ->get()
            ->map(function($mant) {
                return [
                    'id' => $mant->id,
                    'title' => ($mant->equipo->nombre ?? 'Equipo') . ' - ' . $mant->tipo,
                    'start' => $mant->fecha_programada,
                    'end' => $mant->fecha_programada,
                    'color' => $this->getColorByEstado($mant->estado),
                    'extendedProps' => [
                        'tecnico' => $mant->tecnico,
                        'estado' => $mant->estado,
                        'equipo' => $mant->equipo->numero_serie ?? ''
                    ]
                ];
            });
        
        return response()->json($mantenimientos);
    }
    
    /**
     * Exportar reporte (NUEVO MÉTODO)
     */
    public function exportarReporte(Request $request)
    {
        $query = Mantenimiento::with('equipo');
        
        if ($request->filled('equipo_id')) {
            $query->where('equipo_id', $request->equipo_id);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $mantenimientos = $query->orderBy('fecha_programada', 'desc')->get();
        
        // Exportar a CSV
        $filename = 'reporte_mantenimientos_' . Carbon::now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        // Cabeceras
        fputcsv($handle, ['Equipo', 'Tipo', 'Estado', 'Fecha Programada', 'Fecha Realización', 'Técnico', 'Costo', 'Tiempo Inactivo', 'Descripción']);
        
        // Datos
        foreach ($mantenimientos as $mant) {
            fputcsv($handle, [
                $mant->equipo->nombre ?? 'N/A',
                $mant->tipo,
                $mant->estado,
                $mant->fecha_programada ? Carbon::parse($mant->fecha_programada)->format('d/m/Y') : 'N/A',
                $mant->fecha_realizacion ? Carbon::parse($mant->fecha_realizacion)->format('d/m/Y') : 'Pendiente',
                $mant->tecnico ?? 'N/A',
                $mant->costo ?? 0,
                $mant->tiempo_inactivo ?? 0,
                $mant->descripcion ?? ''
            ]);
        }
        
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Obtener color según estado para el calendario
     */
    private function getColorByEstado($estado)
    {
        return match($estado) {
            'Pendiente' => '#ffc107',     // Amarillo
            'En proceso' => '#0dcaf0',    // Azul claro
            'Completado' => '#198754',    // Verde
            'Cancelado' => '#6c757d',     // Gris
            default => '#0d6efd'          // Azul
        };
    }
}