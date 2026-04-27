@extends('layouts.app')

@section('title', 'Nuevo Equipo Biomédico')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('equipos.index') }}">Equipos</a></li>
    <li class="breadcrumb-item active">Nuevo Equipo</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Registro de Nuevo Equipo Biomédico</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('equipos.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <!-- Columna 1 -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Información General</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Número de Serie *</label>
                        <input type="text" class="form-control" name="numero_serie" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Equipo *</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo *</label>
                        <select class="form-select" name="tipo" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Diagnóstico">Diagnóstico por Imagen</option>
                            <option value="Laboratorio">Laboratorio Clínico</option>
                            <option value="Soporte vital">Soporte Vital</option>
                            <option value="Imagenología">Imagenología</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo">
                        </div>
                    </div>
                </div>
                
                <!-- Columna 2 -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Información Técnica y Financiera</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Compra *</label>
                            <input type="date" class="form-control" name="fecha_compra" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo de Adquisición ($)</label>
                            <input type="number" step="0.01" class="form-control" name="costo">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vida Útil Estimada (años)</label>
                            <input type="number" class="form-control" name="vida_util">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento *</label>
                            <select class="form-select" name="departamento" required>
                                <option value="">Seleccionar</option>
                                <option value="UCI">Unidad de Cuidados Intensivos</option>
                                <option value="Laboratorio">Laboratorio de Hematología</option>
                                <option value="Cardiologia">Cardiología</option>
                                <option value="Neurologia">Neurología</option>
                                <option value="Emergencias">Emergencias</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Última Calibración</label>
                            <input type="date" class="form-control" name="ultima_calibracion">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Próxima Calibración</label>
                            <input type="date" class="form-control" name="proxima_calibracion">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estado *</label>
                        <select class="form-select" name="estado" required>
                            <option value="Activo">Activo</option>
                            <option value="Mantenimiento">En Mantenimiento</option>
                            <option value="Reparación">En Reparación</option>
                            <option value="Baja">Dado de Baja</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Observaciones -->
            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" name="observaciones" rows="3"></textarea>
            </div>
            
            <!-- Botones -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('equipos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar Equipo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection