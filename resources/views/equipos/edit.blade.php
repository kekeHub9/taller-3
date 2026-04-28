@extends('layouts.app')

@section('title', 'Editar Equipo')
@section('breadcrumb', 'Editar Equipo')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Editar Equipo: {{ $equipo->nombre }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('equipos.update', $equipo->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Serie <span class="text-danger">*</span></label>
                    <input type="text" name="numero_serie" class="form-control" value="{{ $equipo->numero_serie }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Equipo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="{{ $equipo->nombre }}" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="Diagnóstico" {{ $equipo->tipo == 'Diagnóstico' ? 'selected' : '' }}>Diagnóstico</option>
                        <option value="Monitoreo" {{ $equipo->tipo == 'Monitoreo' ? 'selected' : '' }}>Monitoreo</option>
                        <option value="Tratamiento" {{ $equipo->tipo == 'Tratamiento' ? 'selected' : '' }}>Tratamiento</option>
                        <option value="Laboratorio" {{ $equipo->tipo == 'Laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                        <option value="Imagenología" {{ $equipo->tipo == 'Imagenología' ? 'selected' : '' }}>Imagenología</option>
                        <option value="Otro" {{ $equipo->tipo == 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control" value="{{ $equipo->marca }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control" value="{{ $equipo->modelo }}">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departamento <span class="text-danger">*</span></label>
                    <select name="departamento" class="form-select" required>
                        <option value="UCI" {{ $equipo->departamento == 'UCI' ? 'selected' : '' }}>UCI</option>
                        <option value="Laboratorio" {{ $equipo->departamento == 'Laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                        <option value="Cardiología" {{ $equipo->departamento == 'Cardiología' ? 'selected' : '' }}>Cardiología</option>
                        <option value="Radiología" {{ $equipo->departamento == 'Radiología' ? 'selected' : '' }}>Radiología</option>
                        <option value="Urgencias" {{ $equipo->departamento == 'Urgencias' ? 'selected' : '' }}>Urgencias</option>
                        <option value="Hospitalización" {{ $equipo->departamento == 'Hospitalización' ? 'selected' : '' }}>Hospitalización</option>
                        <option value="Quirófano" {{ $equipo->departamento == 'Quirófano' ? 'selected' : '' }}>Quirófano</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select" required>
                        <option value="Activo" {{ $equipo->estado == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Mantenimiento" {{ $equipo->estado == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        <option value="Reparación" {{ $equipo->estado == 'Reparación' ? 'selected' : '' }}>Reparación</option>
                        <option value="Baja" {{ $equipo->estado == 'Baja' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha Adquisición</label>
                    <input type="date" name="fecha_adquisicion" class="form-control" value="{{ $equipo->fecha_adquisicion }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor" class="form-control" value="{{ $equipo->proveedor }}">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Costo (USD)</label>
                    <input type="number" step="0.01" name="costo" class="form-control" value="{{ $equipo->costo }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vida Útil (años)</label>
                    <input type="number" name="vida_util" class="form-control" value="{{ $equipo->vida_util }}">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Última Calibración</label>
                    <input type="date" name="ultima_calibracion" class="form-control" value="{{ $equipo->ultima_calibracion }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Próxima Calibración</label>
                    <input type="date" name="proxima_calibracion" class="form-control" value="{{ $equipo->proxima_calibracion }}">
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Actualizar Equipo</button>
                <a href="{{ route('equipos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection