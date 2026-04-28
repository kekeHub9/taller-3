@extends('layouts.app')

@section('title', 'Nuevo Equipo')
@section('breadcrumb', 'Nuevo Equipo')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Nuevo Equipo Biomédico</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('equipos.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Serie <span class="text-danger">*</span></label>
                    <input type="text" name="numero_serie" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Equipo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="Diagnóstico">Diagnóstico</option>
                        <option value="Monitoreo">Monitoreo</option>
                        <option value="Tratamiento">Tratamiento</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Imagenología">Imagenología</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departamento <span class="text-danger">*</span></label>
                    <select name="departamento" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="UCI">UCI</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Cardiología">Cardiología</option>
                        <option value="Radiología">Radiología</option>
                        <option value="Urgencias">Urgencias</option>
                        <option value="Hospitalización">Hospitalización</option>
                        <option value="Quirófano">Quirófano</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select" required>
                        <option value="Activo">Activo</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Reparación">Reparación</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha Adquisición</label>
                    <input type="date" name="fecha_compra" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Costo (USD)</label>
                    <input type="number" step="0.01" name="costo" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vida Útil (años)</label>
                    <input type="number" name="vida_util" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Última Calibración</label>
                    <input type="date" name="ultima_calibracion" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Próxima Calibración</label>
                    <input type="date" name="proxima_calibracion" class="form-control">
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Guardar Equipo</button>
                <a href="{{ route('equipos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection