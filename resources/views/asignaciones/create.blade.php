@extends('layouts.app')

@section('title', 'Nueva Asignación')
@section('breadcrumb', 'Nueva Asignación')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Nueva Asignación de Equipo</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('asignaciones.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Equipo <span class="text-danger">*</span></label>
                    <select name="equipo_id" class="form-select" required>
                        <option value="">Seleccionar equipo</option>
                        @foreach($equiposDisponibles as $equipo)
                        <option value="{{ $equipo->id }}">
                            {{ $equipo->nombre }} (Serie: {{ $equipo->numero_serie }})
                        </option>
                        @endforeach
                    </select>
                    @error('equipo_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departamento <span class="text-danger">*</span></label>
                    <select name="departamento" class="form-select" required>
                        <option value="">Seleccionar</option>
                        @foreach($departamentos as $depto)
                        <option value="{{ $depto }}">{{ $depto }}</option>
                        @endforeach
                    </select>
                    @error('departamento')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Responsable <span class="text-danger">*</span></label>
                    <input type="text" name="responsable" class="form-control" value="{{ old('responsable') }}" required>
                    @error('responsable')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cargo <span class="text-danger">*</span></label>
                    <input type="text" name="cargo" class="form-control" value="{{ old('cargo') }}" required>
                    @error('cargo')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha de Asignación <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_asignacion" class="form-control" value="{{ old('fecha_asignacion', date('Y-m-d')) }}" required>
                    @error('fecha_asignacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Asignar Equipo
                </button>
                <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@if($equiposDisponibles->isEmpty())
<div class="alert alert-warning mt-3">
    <i class="fas fa-exclamation-triangle me-2"></i>
    No hay equipos disponibles para asignar. Todos los equipos ya están asignados o están en mantenimiento/reparación.
</div>
@endif
@endsection