@extends('layouts.app')

@section('title', 'Detalles del Equipo')
@section('breadcrumb', 'Ver Equipo')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-microscope me-2"></i>{{ $equipo->nombre }}
        </h5>
        <div>
            <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Editar
            </a>
            <a href="{{ route('equipos.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%">ID</th>
                        <td>{{ $equipo->id }}</td>
                    </tr>
                    <tr>
                        <th>Número de Serie</th>
                        <td><code>{{ $equipo->numero_serie }}</code></td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td>{{ $equipo->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <td>{{ $equipo->tipo }}</td>
                    </tr>
                    <tr>
                        <th>Marca</th>
                        <td>{{ $equipo->marca ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Modelo</th>
                        <td>{{ $equipo->modelo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Departamento</th>
                        <td>{{ $equipo->departamento }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="badge bg-{{ 
                                $equipo->estado == 'Activo' ? 'success' : 
                                ($equipo->estado == 'Mantenimiento' ? 'warning' : 
                                ($equipo->estado == 'Reparación' ? 'danger' : 'secondary')) 
                            }}">
                                {{ $equipo->estado }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%">Fecha Compra</th>
                        <td>{{ $equipo->fecha_compra ? \Carbon\Carbon::parse($equipo->fecha_compra)->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Proveedor</th>
                        <td>{{ $equipo->proveedor ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Costo (USD)</th>
                        <td>${{ number_format($equipo->costo ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Vida Útil (años)</th>
                        <td>{{ $equipo->vida_util ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Depreciación</th>
                        <td>${{ number_format($equipo->depreciacion ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Última Calibración</th>
                        <td>{{ $equipo->ultima_calibracion ? \Carbon\Carbon::parse($equipo->ultima_calibracion)->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Próxima Calibración</th>
                        <td>{{ $equipo->proxima_calibracion ? \Carbon\Carbon::parse($equipo->proxima_calibracion)->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha Registro</th>
                        <td>{{ $equipo->created_at ? \Carbon\Carbon::parse($equipo->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Mantenimientos -->
<div class="card shadow-sm mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Historial de Mantenimientos</h6>
    </div>
    <div class="card-body">
        @if($mantenimientos->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Técnico</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mantenimientos as $mant)
                    <tr>
                        <td>{{ $mant->fecha_programada ? \Carbon\Carbon::parse($mant->fecha_programada)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $mant->tipo ?? 'N/A' }}</td>
                        <td>{{ $mant->descripcion ?? 'N/A' }}</td>
                        <td>{{ $mant->tecnico ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $mant->estado == 'Completado' ? 'success' : 'warning' }}">
                                {{ $mant->estado ?? 'Pendiente' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center mb-0">No hay mantenimientos registrados para este equipo.</p>
        @endif
    </div>
</div>

<!-- Sección de Asignaciones -->
<div class="card shadow-sm mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-handshake me-2"></i>Historial de Asignaciones</h6>
    </div>
    <div class="card-body">
        @if($asignaciones->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha Asignación</th>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asignaciones as $asig)
                    <tr>
                        <td>{{ $asig->fecha_asignacion ? \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $asig->departamento ?? 'N/A' }}</td>
                        <td>{{ $asig->responsable ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $asig->estado == 'Activa' ? 'success' : 'secondary' }}">
                                {{ $asig->estado ?? 'Inactiva' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center mb-0">No hay asignaciones registradas para este equipo.</p>
        @endif
    </div>
</div>
@endsection