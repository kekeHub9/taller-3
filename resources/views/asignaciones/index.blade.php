@extends('layouts.app')

@section('title', 'Gestión de Asignaciones')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Asignaciones de Equipos</li>
@endsection

@section('header-buttons')
    <a href="{{ route('asignaciones.create') }}" class="btn btn-primary">
        <i class="fas fa-handshake me-1"></i>Nueva Asignación
    </a>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-file-export me-1"></i>Exportar
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('pdf')">
                <i class="fas fa-file-pdf text-danger me-2"></i>PDF
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('excel')">
                <i class="fas fa-file-excel text-success me-2"></i>Excel
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('csv')">
                <i class="fas fa-file-csv text-primary me-2"></i>CSV
            </a></li>
        </ul>
    </div>
@endsection

@push('head-scripts')
<style>
.asignacion-card {
    border-left: 4px solid;
    transition: all 0.2s;
}
.asignacion-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.estado-activa { border-left-color: #198754; }
.estado-devuelta { border-left-color: #6c757d; }
.estado-vencida { border-left-color: #dc3545; }
.stats-card {
    border-radius: 10px;
    transition: transform 0.3s;
}
.stats-card:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@section('content')
<!-- Asegurar que todas las variables existen -->
@php
    // Si las variables no vienen del controlador, creamos valores por defecto
    if (!isset($estadisticas)) {
        $estadisticas = [
            'total' => 0,
            'activas' => 0,
            'devueltas' => 0,
            'vencidas' => 0,
            'promedio_dias' => 0,
            'porcentaje_activas' => 0
        ];
    }
    
    if (!isset($asignaciones)) {
        // Crear una colección vacía
        $asignaciones = new \Illuminate\Pagination\LengthAwarePaginator(
            [], // items vacíos
            0,  // total
            15, // per page
            1   // current page
        );
    }
    
    if (!isset($departamentos)) {
        $departamentos = [];
    }
@endphp

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-primary">Total Asignaciones</h6>
                        <h2 class="mb-0">{{ $estadisticas['total'] ?? 0 }}</h2>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-success">Activas</h6>
                        <h2 class="mb-0">{{ $estadisticas['activas'] ?? 0 }}</h2>
                        <small class="text-muted">{{ $estadisticas['porcentaje_activas'] ?? 0 }}% del total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-secondary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-secondary">Devueltas</h6>
                        <h2 class="mb-0">{{ $estadisticas['devueltas'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-warning">Promedio de Días</h6>
                        <h2 class="mb-0">{{ $estadisticas['promedio_dias'] ?? 0 }}</h2>
                        <small class="text-muted">por asignación</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Busqueda</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Departamento</label>
                <select class="form-select" name="departamento">
                    <option value="">Todos los departamentos</option>
                    @foreach($departamentos ?? [] as $depto)
                    <option value="{{ $depto }}" {{ request('departamento') == $depto ? 'selected' : '' }}>
                        {{ $depto }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="Activa" {{ request('estado') == 'Activa' ? 'selected' : '' }}>Activa</option>
                    <option value="Devuelta" {{ request('estado') == 'Devuelta' ? 'selected' : '' }}>Devuelta</option>
                    <option value="Vencida" {{ request('estado') == 'Vencida' ? 'selected' : '' }}>Vencida</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Responsable</label>
                <input type="text" class="form-control" name="responsable" 
                       value="{{ request('responsable') }}" placeholder="Nombre del responsable">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Ordenar por</label>
                <select class="form-select" name="orden">
                    <option value="fecha_asignacion" {{ request('orden') == 'fecha_asignacion' ? 'selected' : '' }}>
                        Fecha de Asignación
                    </option>
                    <option value="departamento" {{ request('orden') == 'departamento' ? 'selected' : '' }}>
                        Departamento
                    </option>
                    <option value="responsable" {{ request('orden') == 'responsable' ? 'selected' : '' }}>
                        Responsable
                    </option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" name="fecha_desde" 
                       value="{{ request('fecha_desde') }}">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" 
                       value="{{ request('fecha_hasta') }}">
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <div class="btn-group w-100" role="group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Buscar
                    </button>
                    <a href="{{ route('asignaciones.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Asignaciones Activas</h5>
        <span class="badge bg-primary">{{ $asignaciones->total() ?? 0 }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Equipo</th>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th>Fecha Asignación</th>
                        <th>Días Transcurridos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignaciones ?? [] as $asignacion)
                    <tr class="{{ 
                        $asignacion->estado == 'Activa' ? 'table-success' : 
                        ($asignacion->estado == 'Vencida' ? 'table-danger' : '') 
                    }}">
                        <td>
                            <strong>{{ $asignacion->equipo->nombre ?? 'N/A' }}</strong><br>
                            <small class="text-muted">Serie: {{ $asignacion->equipo->numero_serie ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $asignacion->departamento ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ $asignacion->responsable ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $asignacion->cargo ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->format('d/m/Y') }}<br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->diffForHumans() }}
                            </small>
                            <!--esta vaina formatea y muestra una fecha de dos maneras diferentes en una tabla -->
                        </td>
                        <td>
                            @php
                                $dias = \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->diffInDays(now());
                            @endphp
                            <span class="badge bg-{{ 
                                $dias > 30 ? 'danger' : 
                                ($dias > 15 ? 'warning' : 'success') 
                            }}">
                                {{ $dias }} días
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ 
                                ($asignacion->estado ?? '') == 'Activa' ? 'success' : 
                                (($asignacion->estado ?? '') == 'Devuelta' ? 'secondary' : 'danger') 
                            }}">
                                {{ $asignacion->estado ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(($asignacion->estado ?? '') == 'Activa')
                                <form action="{{ route('asignaciones.devolver', $asignacion->id ?? 0) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" 
                                            title="Devolver equipo">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                                
                                <a href="{{ route('asignaciones.historial', $asignacion->equipo_id ?? 0) }}" 
                                   class="btn btn-outline-info" title="Ver historial">
                                    <i class="fas fa-history"></i>
                                </a>
                                
                                <button class="btn btn-outline-primary" 
                                        onclick="verDetalles({{ $asignacion->id ?? 0 }})"
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay asignaciones registradas</h5>
                            <p class="text-muted mb-0">
                                <a href="{{ route('asignaciones.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Crear primera asignación
                                </a>
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(($asignaciones ?? collect())->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Mostrando {{ ($asignaciones ?? collect())->firstItem() ?? 0 }} a {{ ($asignaciones ?? collect())->lastItem() ?? 0 }} 
                de {{ ($asignaciones ?? collect())->total() ?? 0 }} registros
            </div>
            <div>
                {{ ($asignaciones ?? collect())->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Asignación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesContent">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportarReporte(formato) {
    if (!confirm(`¿Exportar reporte en formato ${formato.toUpperCase()}?`)) {
        return;
    }
    
    // Obtener filtros actuales(no funciona bien)
    const filtros = new URLSearchParams(window.location.search);
    filtros.set('formato', formato);
    
    // Redirigir a la ruta de exportación
    window.location.href = `{{ route('asignaciones.exportar') }}?${filtros.toString()}`;
}

function verDetalles(asignacionId) {
    if (!asignacionId || asignacionId === 0) {
        alert('ID de asignación no válido');
        return;
    }
    
    fetch(`/api/asignaciones/${asignacionId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            const modal = document.getElementById('detallesModal');
            const content = document.getElementById('detallesContent');
            
            // Datos seguros
            const equipoNombre = data.equipo?.nombre || 'N/A';
            const equipoSerie = data.equipo?.numero_serie || 'N/A';
            const equipoTipo = data.equipo?.tipo || 'N/A';
            const equipoEstado = data.equipo?.estado || 'N/A';
            const depto = data.departamento || 'N/A';
            const responsable = data.responsable || 'N/A';
            const cargo = data.cargo || 'N/A';
            const fecha = data.fecha_asignacion ? new Date(data.fecha_asignacion).toLocaleDateString('es-ES') : 'N/A';
            const estado = data.estado || 'N/A';
            const observaciones = data.observaciones || '';
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Equipo</h6>
                        <p><strong>Nombre:</strong> ${equipoNombre}</p>
                        <p><strong>Número de Serie:</strong> ${equipoSerie}</p>
                        <p><strong>Tipo:</strong> ${equipoTipo}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-${equipoEstado == 'Activo' ? 'success' : 'warning'}">${equipoEstado}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Información de Asignación</h6>
                        <p><strong>Departamento:</strong> ${depto}</p>
                        <p><strong>Responsable:</strong> ${responsable}</p>
                        <p><strong>Cargo:</strong> ${cargo}</p>
                        <p><strong>Fecha Asignación:</strong> ${fecha}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-${estado == 'Activa' ? 'success' : 'secondary'}">${estado}</span></p>
                    </div>
                </div>
                ${observaciones ? `
                <div class="mt-3">
                    <h6>Observaciones</h6>
                    <p class="border rounded p-3">${observaciones}</p>
                </div>
                ` : ''}
                ${data.historial && data.historial.length > 0 ? `
                <div class="mt-3">
                    <h6>Historial Reciente</h6>
                    <div class="list-group">
                        ${data.historial.map(item => `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>${item.departamento || 'N/A'}</span>
                                <small class="text-muted">${item.fecha_asignacion ? new Date(item.fecha_asignacion).toLocaleDateString('es-ES') : 'N/A'}</small>
                            </div>
                            <small class="text-muted">${item.responsable || 'N/A'} - ${item.estado || 'N/A'}</small>
                        </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            `;
            
            new bootstrap.Modal(modal).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-seleccionar fecha 
    const fechaInputs = document.querySelectorAll('input[type="date"]');
    fechaInputs.forEach(input => {
        if (!input.value) {
            if (input.name === 'fecha_desde') {
                input.value = new Date().toISOString().split('T')[0];
            }
        }
    });
    
    // Mostrar/ocultar campos
    const estadoSelect = document.querySelector('select[name="estado"]');
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            console.log('Estado cambiado a:', this.value);
        });
    }
});
</script>
@endpush