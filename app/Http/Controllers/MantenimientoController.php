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
    
    //Agenda de mantenimientos
    
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
    
    
      //Registro de incidencias

    public function registro(Request $request)
    {
        $query = Mantenimiento::with('equipo')
            ->where('tipo', 'Correctivo');
            
        if ($request->filled('equipo_id')) {
            $query->where('equipo_id', $request->equipo_id);
        }
        
        if ($request->filled('mes')) {
            $query->whereMonth('fecha_realizacion', $request->mes);
        }
        
        $incidencias = $query->orderBy('fecha_realizacion', 'desc')->paginate(15);
        $equipos = Equipo::all();
        
        // Calculo de estadísticas
        $estadisticas = [
            'total_incidencias' => $incidencias->total(),
            'costo_total' => $incidencias->sum('costo'),
            'tiempo_total' => $incidencias->sum('tiempo_inactivo'),
            'promedio_reparacion' => $incidencias->avg('tiempo_inactivo') ?? 0
        ];
        
        return view('mantenimientos.registro', compact('incidencias', 'equipos', 'estadisticas'));
    }
    
    /*Guardar nuevo mantenimiento */
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
    
    
    public function apiCalendario(Request $request)
    {
        $mantenimientos = Mantenimiento::with('equipo')
            ->whereBetween('fecha_programada', [
                $request->input('start', now()->startOfMonth()),
                $request->input('end', now()->endOfMonth())
            ])
            ->get()
            ->map(function($mant) {
                return [
                    'id' => $mant->id,
                    'title' => $mant->equipo->nombre . ' - ' . $mant->tipo,
                    'start' => $mant->fecha_programada,
                    'end' => $mant->fecha_programada,
                    'color' => $this->getColorByEstado($mant->estado),
                    'extendedProps' => [
                        'tecnico' => $mant->tecnico,
                        'estado' => $mant->estado,
                        'equipo' => $mant->equipo->numero_serie
                    ]
                ];
            });
        
        return response()->json($mantenimientos);
    }
    
    private function getColorByEstado($estado)
    {
        return match($estado) {
            'Pendiente' => '#ffc107', // Amarillo
            'En proceso' => '#0dcaf0', // Azul claro
            'Completado' => '#198754', // Verde
            'Cancelado' => '#6c757d', // Gris
            default => '#0d6efd'
        };
    }
}