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

    public function getEquipos()
    {
        $equipos = Equipo::orderBy('created_at', 'desc')->get();
        return response()->json($equipos);
    }

    public function getEquipo($id)
    {
        $equipo = Equipo::findOrFail($id);
        return response()->json($equipo);
    }

    public function create()
    {
        return view('equipos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_serie' => 'required|unique:equipos',
            'nombre' => 'required|max:255',
            'tipo' => 'required',
            'marca' => 'nullable|max:100',
            'modelo' => 'nullable|max:100',
            'fecha_compra' => 'nullable|date',
            'proveedor' => 'nullable|max:255',
            'costo' => 'nullable|numeric|min:0',
            'departamento' => 'required',
            'estado' => 'required',
            'ultima_calibracion' => 'nullable|date',
            'proxima_calibracion' => 'nullable|date',
            'vida_util' => 'nullable|integer',
        ]);
        
        $equipo = Equipo::create($validated);
        
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
        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        
        $validated = $request->validate([
            'numero_serie' => 'required|unique:equipos,numero_serie,' . $id,
            'nombre' => 'required|max:255',
            'tipo' => 'required',
            'marca' => 'nullable|max:100',
            'modelo' => 'nullable|max:100',
            'fecha_compra' => 'nullable|date',
            'proveedor' => 'nullable|max:255',
            'costo' => 'nullable|numeric|min:0',
            'departamento' => 'required',
            'estado' => 'required',
            'ultima_calibracion' => 'nullable|date',
            'proxima_calibracion' => 'nullable|date',
            'vida_util' => 'nullable|integer',
        ]);
        
        $equipo->update($validated);
        
        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado exitosamente');
    }

    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->delete();
        
        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado exitosamente');
    }
    
    public function calcularDepreciacion($id)
    {
        $equipo = Equipo::findOrFail($id);
        
        if ($equipo->costo && $equipo->vida_util && $equipo->fecha_compra) {
            $anosTranscurridos = now()->diffInYears($equipo->fecha_compra);
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