<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Mantenimiento;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipo::query();
        
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_serie', 'like', "%{$request->search}%")
                  ->orWhere('nombre', 'like', "%{$request->search}%")
                  ->orWhere('modelo', 'like', "%{$request->search}%");
            });
        }
        
        $equipos = $query->orderBy('created_at', 'desc')->get();
        
        $equiposCriticos = $equipos->filter(function($equipo) {
            return $equipo->estado == 'Mantenimiento' 
                || ($equipo->proxima_calibracion 
                    && now()->diffInDays($equipo->proxima_calibracion) < 7);
        });
        
        return view('equipos.index', compact('equipos', 'equiposCriticos'));
    }

    // API: Obtener todos los equipos (para AJAX)
    public function getEquipos()
    {
        $equipos = Equipo::orderBy('created_at', 'desc')->get();
        return response()->json($equipos);
    }

    // API: Obtener un equipo específico
    public function getEquipo($id)
    {
        $equipo = Equipo::findOrFail($id);
        return response()->json($equipo);
    }

    public function create()
    {
        return view('equipos.create');
    }

    // Store con soporte AJAX
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_serie' => 'required|unique:equipos',
            'nombre' => 'required|max:255',
            'tipo' => 'required',
            'marca' => 'nullable|max:100',
            'modelo' => 'nullable|max:100',
            'fecha_adquisicion' => 'nullable|date',
            'proveedor' => 'nullable|max:255',
            'costo' => 'nullable|numeric|min:0',
            'departamento' => 'required',
            'estado' => 'required',
            'ultima_calibracion' => 'nullable|date',
            'proxima_calibracion' => 'nullable|date|after_or_equal:ultima_calibracion',
        ]);
        
        // Calcular depreciación si hay costo y fecha aun no se usa pero
        if ($request->costo && $request->fecha_adquisicion && $request->vida_util ?? false) {
            $anosTranscurridos = now()->diffInYears($request->fecha_adquisicion);
            $depreciacionAnual = $request->costo / $request->vida_util;
            $depreciacionTotal = min($depreciacionAnual * $anosTranscurridos, $request->costo);
            $validated['depreciacion'] = $depreciacionTotal;
        }
        
        $equipo = Equipo::create($validated);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'equipo' => $equipo, 'message' => 'Equipo creado exitosamente']);
        }
        
        return redirect()->route('equipos.index')
            ->with('success', 'Equipo registrado exitosamente');
    }

    public function show($id)
    {
        $equipo = Equipo::findOrFail($id);
        
        $mantenimientos = Mantenimiento::where('equipo_id', $id)
            ->orderBy('fecha_programada', 'desc')
            ->get();
            
        $asignaciones = Asignacion::where('equipo_id', $id)
            ->orderBy('fecha_asignacion', 'desc')
            ->get();
        
        return view('equipos.show', compact('equipo', 'mantenimientos', 'asignaciones'));
    }

    public function edit($id)
    {
      $equipo = Equipo::findOrFail($id);
    
        if (request()->ajax()) {
        return response()->json($equipo);
        }   
    
        return view('equipos.edit', compact('equipo'));
    }

    // Update con soporte AJAX
    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        
        $validated = $request->validate([
            'numero_serie' => 'required|unique:equipos,numero_serie,' . $id,
            'nombre' => 'required|max:255',
            'tipo' => 'required',
            'marca' => 'nullable|max:100',
            'modelo' => 'nullable|max:100',
            'fecha_adquisicion' => 'nullable|date',
            'proveedor' => 'nullable|max:255',
            'costo' => 'nullable|numeric|min:0',
            'departamento' => 'required',
            'estado' => 'required',
            'ultima_calibracion' => 'nullable|date',
            'proxima_calibracion' => 'nullable|date|after_or_equal:ultima_calibracion',
        ]);
        
        $equipo->update($validated);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'equipo' => $equipo, 'message' => 'Equipo actualizado exitosamente']);
        }
        
        return redirect()->route('equipos.show', $id)
            ->with('success', 'Equipo actualizado exitosamente');
    }

    // Destroy con soporte AJAX
    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Equipo eliminado exitosamente']);
        }
        
        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado exitosamente');
    }
    
    public function calcularDepreciacion($id)
    {
        $equipo = Equipo::findOrFail($id);
        
        if ($equipo->costo && $equipo->vida_util) {
            $anosTranscurridos = now()->diffInYears($equipo->fecha_adquisicion);
            $depreciacionAnual = $equipo->costo / $equipo->vida_util;
            $depreciacionTotal = min($depreciacionAnual * $anosTranscurridos, $equipo->costo);
            
            $equipo->update(['depreciacion' => $depreciacionTotal]);
            
            return response()->json([
                'depreciacion' => $depreciacionTotal,
                'valor_actual' => $equipo->costo - $depreciacionTotal
            ]);
        }
        
        return response()->json(['error' => 'No se puede calcular'], 400);
    }
}