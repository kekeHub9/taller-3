@extends('layouts.app')

@section('title', 'Gestión de Asignaciones')
@section('breadcrumb')
    
    <li class="breadcrumb-item active">Asignaciones</li>
@endsection

@section('header-buttons')
    <a href="{{ route('asignaciones.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-handshake me-1"></i>Nueva Asignación
    </a>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-file-export me-1"></i>Exportar
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('pdf')">PDF</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('excel')">Excel</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportarReporte('csv')">CSV</a></li>
        </ul>
    </div>
@endsection

@push('head-scripts')
<style>
    /* Tarjetas pequeñas */
    .stats-card-small {
        transition: all 0.2s;
        border-radius: 8px;
    }
    .stats-card-small .card-body {
        padding: 0.6rem 0.8rem !important;
    }
    .stats-card-small h2 {
        font-size: 1.5rem !important;
        margin-bottom: 0 !important;
    }
    .stats-card-small h6 {
        font-size: 0.65rem !important;
        margin-bottom: 0.2rem !important;
    }
    .stats-card-small small {
        font-size: 0.6rem !important;
    }
    .stats-card-small .fa-2x {
        font-size: 1.3rem !important;
    }
    
    /* Filtro contraíble */
    .filter-header {
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-header:hover {
        background-color: #f8f9fc;
    }
    .filter-icon {
        transition: transform 0.3s;
    }
    .filter-icon.collapsed {
        transform: rotate(0deg);
    }
    .filter-icon:not(.collapsed) {
        transform: rotate(180deg);
    }
    
    /* Tabla compacta */
    .table-compact td, .table-compact th {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
    .badge-sm {
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
    }
    .btn-group-sm .btn {
        padding: 0.1rem 0.3rem;
        font-size: 0.6rem;
    }
</style>
@endpush

@section('content')
@php
    if (!isset($estadisticas)) {
        $estadisticas = [
            'total' => 0, 'activas' => 0, 'devueltas' => 0,
            'vencidas' => 0, 'promedio_dias' => 0, 'porcentaje_activas' => 0
        ];
    }
    if (!isset($asignaciones)) {
        $asignaciones = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1);
    }
    if (!isset($departamentos)) {
        $departamentos = [];
    }
@endphp

<!-- TARJETAS PEQUEÑAS -->
<div class="row mb-3">
    <div class="col-6 col-md-3 mb-2">
        <div class="card stats-card-small border-start border-primary border-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-primary text-uppercase fw-bold">Total</h6>
                        <h2 class="fw-bold">{{ $estadisticas['total'] ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-2">
        <div class="card stats-card-small border-start border-success border-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-success text-uppercase fw-bold">Activas</h6>
                        <h2 class="fw-bold">{{ $estadisticas['activas'] ?? 0 }}</h2>
                        <small class="text-muted">{{ $estadisticas['porcentaje_activas'] ?? 0 }}%</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-2">
        <div class="card stats-card-small border-start border-secondary border-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-secondary text-uppercase fw-bold">Devueltas</h6>
                        <h2 class="fw-bold">{{ $estadisticas['devueltas'] ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-undo fa-2x text-secondary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3 mb-2">
        <div class="card stats-card-small border-start border-warning border-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-warning text-uppercase fw-bold">Promedio días</h6>
                        <h2 class="fw-bold">{{ $estadisticas['promedio_dias'] ?? 0 }}</h2>
                        <small class="text-muted">por asignación</small>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILTRO CONTRAÍBLE -->
<div class="card shadow-sm mb-3">
    <div class="card-header filter-header py-2" onclick="toggleFilter()">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                <span class="badge bg-secondary ms-2" id="filtrosActivosCount">0</span>
            </h6>
            <i class="fas fa-chevron-down filter-icon" id="filterIcon"></i>
        </div>
    </div>
    <div class="collapse" id="filterCollapse">
        <div class="card-body py-2">
            <form method="GET" id="filterForm">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Departamento</label>
                        <select class="form-select form-select-sm" name="departamento" onchange="aplicarFiltros()">
                            <option value="">Todos</option>
                            @foreach($departamentos ?? [] as $depto)
                            <option value="{{ $depto }}" {{ request('departamento') == $depto ? 'selected' : '' }}>
                                {{ $depto }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Estado</label>
                        <select class="form-select form-select-sm" name="estado" onchange="aplicarFiltros()">
                            <option value="">Todos</option>
                            <option value="Activa" {{ request('estado') == 'Activa' ? 'selected' : '' }}>Activa</option>
                            <option value="Devuelta" {{ request('estado') == 'Devuelta' ? 'selected' : '' }}>Devuelta</option>
                            <option value="Vencida" {{ request('estado') == 'Vencida' ? 'selected' : '' }}>Vencida</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Responsable</label>
                        <input type="text" class="form-control form-control-sm" name="responsable" 
                               value="{{ request('responsable') }}" placeholder="Nombre" onkeyup="debounceAplicarFiltros()">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Ordenar por</label>
                        <select class="form-select form-select-sm" name="orden" onchange="aplicarFiltros()">
                            <option value="fecha_asignacion" {{ request('orden') == 'fecha_asignacion' ? 'selected' : '' }}>Fecha</option>
                            <option value="departamento" {{ request('orden') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                            <option value="responsable" {{ request('orden') == 'responsable' ? 'selected' : '' }}>Responsable</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small mb-0">Fecha Desde</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_desde" 
                               value="{{ request('fecha_desde') }}" onchange="aplicarFiltros()">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small mb-0">Fecha Hasta</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_hasta" 
                               value="{{ request('fecha_hasta') }}" onchange="aplicarFiltros()">
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                            <a href="{{ route('asignaciones.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TABLA DE ASIGNACIONES -->
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Lista de Asignaciones</h6>
        <span class="badge bg-primary">{{ $asignaciones->total() ?? 0 }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-compact mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Equipo</th>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th>Fecha Asignación</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignaciones ?? [] as $asignacion)
                    <tr>
                        <td>
                            <strong>{{ $asignacion->equipo->nombre ?? 'N/A' }}</strong>
                            <br><small class="text-muted">Serie: {{ $asignacion->equipo->numero_serie ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $asignacion->departamento ?? 'N/A' }}</td>
                        <td>
                            {{ $asignacion->responsable ?? 'N/A' }}
                            <br><small class="text-muted">{{ $asignacion->cargo ?? '' }}</small>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->format('d/m/Y') }}
                            <br><small>{{ \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $dias = \Carbon\Carbon::parse($asignacion->fecha_asignacion ?? now())->diffInDays(now());
                            @endphp
                            <span class="badge bg-{{ $dias > 30 ? 'danger' : ($dias > 15 ? 'warning' : 'success') }} badge-sm">
                                {{ $dias }} días
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ ($asignacion->estado ?? '') == 'Activa' ? 'success' : 'secondary' }} badge-sm">
                                {{ $asignacion->estado ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(($asignacion->estado ?? '') == 'Activa')
                                <form action="{{ route('asignaciones.devolver', $asignacion->id ?? 0) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Devolver">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('asignaciones.historial', $asignacion->equipo_id ?? 0) }}" class="btn btn-outline-info" title="Historial">
                                    <i class="fas fa-history"></i>
                                </a>
                                <button class="btn btn-outline-primary" onclick="verDetalles({{ $asignacion->id ?? 0 }})" title="Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">
                            <i class="fas fa-inbox text-muted mb-2"></i>
                            <p class="small text-muted mb-0">No hay asignaciones registradas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(($asignaciones ?? collect())->hasPages())
    <div class="card-footer py-1">
        <div class="d-flex justify-content-between align-items-center small">
            <div>Mostrando {{ ($asignaciones ?? collect())->firstItem() ?? 0 }} a {{ ($asignaciones ?? collect())->lastItem() ?? 0 }} de {{ ($asignaciones ?? collect())->total() ?? 0 }}</div>
            <div>{{ ($asignaciones ?? collect())->links() }}</div>
        </div>
    </div>
    @endif
</div>

<!-- MODAL DETALLES -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Detalles de Asignación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesContent"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Recordar estado del filtro
function toggleFilter() {
    const collapse = document.getElementById('filterCollapse');
    const icon = document.getElementById('filterIcon');
    
    if (collapse.classList.contains('show')) {
        collapse.classList.remove('show');
        icon.classList.remove('collapsed');
        localStorage.setItem('filterCollapsed', 'true');
    } else {
        collapse.classList.add('show');
        icon.classList.add('collapsed');
        localStorage.setItem('filterCollapsed', 'false');
    }
}

// Cargar estado guardado
document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('filterCollapsed');
    const collapse = document.getElementById('filterCollapse');
    const icon = document.getElementById('filterIcon');
    
    if (savedState === 'true') {
        collapse.classList.remove('show');
        icon.classList.remove('collapsed');
    } else {
        collapse.classList.add('show');
        icon.classList.add('collapsed');
    }
    
    // Contar filtros activos
    contarFiltrosActivos();
});

function contarFiltrosActivos() {
    const params = new URLSearchParams(window.location.search);
    let count = 0;
    ['departamento', 'estado', 'responsable', 'fecha_desde', 'fecha_hasta', 'orden'].forEach(key => {
        if (params.get(key) && params.get(key) !== '') count++;
    });
    const badge = document.getElementById('filtrosActivosCount');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

function aplicarFiltros() {
    document.getElementById('filterForm').submit();
}

let timeout;
function debounceAplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => aplicarFiltros(), 500);
}

function exportarReporte(formato) {
    if (!confirm(`¿Exportar reporte en formato ${formato.toUpperCase()}?`)) return;
    const filtros = new URLSearchParams(window.location.search);
    filtros.set('formato', formato);
    window.location.href = `{{ route('asignaciones.exportar') }}?${filtros.toString()}`;
}

function verDetalles(asignacionId) {
    if (!asignacionId || asignacionId === 0) {
        alert('ID no válido');
        return;
    }
    
    fetch(`/api/asignaciones/${asignacionId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('detallesContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Información del Equipo</h6>
                        <p><strong>Nombre:</strong> ${data.equipo?.nombre || 'N/A'}</p>
                        <p><strong>Serie:</strong> ${data.equipo?.numero_serie || 'N/A'}</p>
                        <p><strong>Tipo:</strong> ${data.equipo?.tipo || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Información de Asignación</h6>
                        <p><strong>Departamento:</strong> ${data.departamento || 'N/A'}</p>
                        <p><strong>Responsable:</strong> ${data.responsable || 'N/A'}</p>
                        <p><strong>Cargo:</strong> ${data.cargo || 'N/A'}</p>
                        <p><strong>Fecha:</strong> ${data.fecha_asignacion || 'N/A'}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-${data.estado == 'Activa' ? 'success' : 'secondary'}">${data.estado}</span></p>
                    </div>
                </div>
                ${data.observaciones ? `<div class="mt-2"><h6 class="fw-bold">Observaciones</h6><p>${data.observaciones}</p></div>` : ''}
            `;
            new bootstrap.Modal(document.getElementById('detallesModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar detalles');
        });
}
</script>
@endpush