<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\MantenimientoController;


// RUTAS PÚBLICAS
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// RUTAS PROTEGIDAS (requieren autenticación)
Route::middleware(['auth'])->group(function () {

    Route::get('/api/asignaciones/{id}', [AsignacionController::class, 'getApiDetalles'])->name('api.asignacion.detalles');
    // pal deshboard
    Route::get('/api/dashboard-data', [DashboardController::class, 'apiData'])->name('api.dashboard');
    // Rutas API para equipos (AJAX)
    Route::get('/api/equipos', [EquipoController::class, 'getEquipos'])->name('api.equipos');
    Route::get('/api/equipos/{id}', [EquipoController::class, 'getEquipo'])->name('api.equipo');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('equipos', EquipoController::class);
    
    Route::prefix('asignaciones')->group(function () {

        Route::get('/', [AsignacionController::class, 'index'])->name('asignaciones.index');
        Route::get('/create', [AsignacionController::class, 'create'])->name('asignaciones.create');
        Route::post('/', [AsignacionController::class, 'store'])->name('asignaciones.store');
        
        Route::post('/{id}/devolver', [AsignacionController::class, 'devolver'])->name('asignaciones.devolver');
        Route::get('/equipo/{equipo_id}/historial', [AsignacionController::class, 'historial'])->name('asignaciones.historial');
        
        Route::get('/exportar', [AsignacionController::class, 'exportar'])->name('asignaciones.exportar');
        
        // API para detalles (para modal)
        Route::get('/api/{id}', function($id) {
            $asignacion = \App\Models\Asignacion::with('equipo')->findOrFail($id);
            $historial = \App\Models\Asignacion::where('equipo_id', $asignacion->equipo_id)
                ->where('id', '!=', $id)
                ->orderBy('fecha_asignacion', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                ...$asignacion->toArray(),
                'historial' => $historial
            ]);
        });
    });
    
    Route::prefix('mantenimientos')->group(function () {
        // (vista calendario)
        Route::get('/agenda', [MantenimientoController::class, 'agenda'])->name('mantenimientos.agenda');
        
        // Registro historico 
        Route::get('/registro', [MantenimientoController::class, 'registro'])->name('mantenimientos.registro');
        
        // Crear nuevo mantenimiento 
        Route::post('/store', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
        
        // Completar mantenimiento
        Route::post('/{id}/completar', [MantenimientoController::class, 'completar'])->name('mantenimientos.completar');
        
        // FullCalendar
        Route::get('/api/calendario', [MantenimientoController::class, 'apiCalendario'])->name('mantenimientos.api.calendario');
        
        // CRUD adicional
        Route::put('/{id}', [MantenimientoController::class, 'update'])->name('mantenimientos.update');
        Route::delete('/{id}', [MantenimientoController::class, 'destroy'])->name('mantenimientos.destroy');
    });
    
    Route::prefix('reportes')->group(function () {
        Route::get('/mantenimiento', function () {
            return view('reportes.mantenimiento-proximo');
        })->name('reportes.mantenimiento');
        
        Route::get('/costos', function () {
            return view('reportes.costos-departamento');
        })->name('reportes.costos');
        
        Route::get('/disponibilidad', function () {
            return view('reportes.disponibilidad-equipos');
        })->name('reportes.disponibilidad');
    });
    
    Route::prefix('auditoria')->group(function () {
        Route::get('/logs', function () {
            return view('auditoria.logs');
        })->name('auditoria.logs');
        
        Route::get('/usuarios', function () {
            return view('auditoria.usuarios');
        })->name('auditoria.usuarios');
    });
});

Route::get('/test-login', function() {
    Auth::loginUsingId(1); // Login automático con usuario, es magico w
    return redirect('/dashboard');
});