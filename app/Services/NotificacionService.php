<?php 
namespace App\Services;
use App\Models\Mantenimiento;
use App\Models\Equipo;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
   
    private $observers = [];
    
    public function __construct()
    {
        // aqui registrariamos clases observadoras
        Log::info('Servicio de notificaciones inicializado');
    }
    
    
    public function notificarNuevoMantenimiento(Mantenimiento $mantenimiento)
    {
        $mensaje = "Nuevo mantenimiento programado: " . 
                  $mantenimiento->equipo->nombre . 
                  " para " . $mantenimiento->fecha_programada->format('d/m/Y');
        
        // Simulación de notificación a administradores ()nofuncabien
        Log::channel('mantenimiento')->info($mensaje);
        
        // Aquí podrías enviar emails, notificaciones push, etc.
        $this->dispararEvento('mantenimiento.programado', [
            'mantenimiento' => $mantenimiento,
            'mensaje' => $mensaje
        ]);
        
        return true;
    }
    
    
    public function notificarMantenimientoCompletado(Mantenimiento $mantenimiento)
    {
        $mensaje = "Mantenimiento completado: " . 
                  $mantenimiento->equipo->nombre . 
                  " - Costo: $" . number_format($mantenimiento->costo, 2) .
                  " - Tiempo: " . $mantenimiento->tiempo_inactivo . " horas";
        
        Log::channel('mantenimiento')->info($mensaje);
        
        $this->dispararEvento('mantenimiento.completado', [
            'mantenimiento' => $mantenimiento,
            'mensaje' => $mensaje
        ]);
        
        return true;
    }
    
  
    public function obtenerAlertasMantenimiento()
    {
        $alertas = [];
        $hoy = now();
        
        // mantenimientos vencidos
        $vencidos = Mantenimiento::with('equipo')
            ->where('estado', 'Pendiente')
            ->where('fecha_programada', '<', $hoy)
            ->get();
            
        foreach ($vencidos as $mant) {
            $dias = $hoy->diffInDays($mant->fecha_programada);
            $alertas[] = [
                'tipo' => 'vencido',
                'prioridad' => 'alta',
                'mensaje' => "Mantenimiento vencido hace {$dias} días: " . $mant->equipo->nombre,
                'fecha' => $mant->fecha_programada->format('d/m/Y'),
                'equipo' => $mant->equipo->numero_serie,
                'accion' => route('mantenimientos.agenda')
            ];
        }
        
        // proximos (3 días)
        $proximos = Mantenimiento::with('equipo')
            ->where('estado', 'Pendiente')
            ->whereBetween('fecha_programada', [$hoy, $hoy->copy()->addDays(3)])
            ->get();
            
        foreach ($proximos as $mant) {
            $dias = $hoy->diffInDays($mant->fecha_programada);
            $alertas[] = [
                'tipo' => 'proximo',
                'prioridad' => $dias == 0 ? 'alta' : 'media',
                'mensaje' => "Mantenimiento en {$dias} días: " . $mant->equipo->nombre,
                'fecha' => $mant->fecha_programada->format('d/m/Y'),
                'equipo' => $mant->equipo->numero_serie,
                'accion' => route('mantenimientos.agenda')
            ];
        }
        
        // sin mantenimiento en más de 6 meses
        $seisMesesAtras = $hoy->copy()->subMonths(6);
        $equiposSinMantenimiento = Equipo::where('estado', 'Activo')
            ->whereDoesntHave('mantenimientos', function($query) use ($seisMesesAtras) {
                $query->where('fecha_realizacion', '>=', $seisMesesAtras)
                      ->where('tipo', 'Preventivo');
            })
            ->get();
            
        foreach ($equiposSinMantenimiento as $equipo) {
            $alertas[] = [
                'tipo' => 'sin_mantenimiento',
                'prioridad' => 'media',
                'mensaje' => "Equipo sin mantenimiento preventivo en 6 meses: " . $equipo->nombre,
                'fecha' => $equipo->updated_at->format('d/m/Y'),
                'equipo' => $equipo->numero_serie,
                'accion' => route('equipos.show', $equipo->id)
            ];
        }
        
        return $alertas;
    }
    
    private function dispararEvento($evento, $data)
    {
       
        
        $observadores = [
            'mantenimiento.programado' => ['AdminNotifier', 'TecnicoNotifier'],
            'mantenimiento.completado' => ['AdminNotifier', 'AuditorNotifier']
        ];
        
        if (isset($observadores[$evento])) {
            foreach ($observadores[$evento] as $observer) {
                Log::info("Evento {$evento} notificado a: {$observer}", $data);
            }
        }
    }
}