@extends('layouts.app')

@section('title', 'Historial de Mantenimientos')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mantenimientos.agenda') }}">Mantenimiento</a></li>
    <li class="breadcrumb-item active">Historial</li>
@endsection

@section('header-buttons')
    <a href="{{ route('mantenimientos.agenda') }}" class="btn btn-outline-primary">
        <i class="fas fa-calendar-alt me-1"></i>Volver a Agenda
    </a>
    <button class="btn btn-primary" onclick="exportarReporte()">
        <i class="fas fa-file-export me-1"></i>Exportar Reporte
    </button>
@endsection

@push('head-scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
.historial-card {
    border-radius: 12px;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.historial-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.stat-card {
    border-radius: 16px;
    border: none;
    transition: all 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
.badge-estado {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
.estado-Pendiente { background: #fff3cd; color: #856404; }
.estado-Completado { background: #d4edda; color: #155724; }
.estado-En-proceso { background: #cff4fc; color: #055160; }
.estado-Cancelado { background: #f8d7da; color: #721c24; }

/* Modal mejorado */
.modal-historial {
    max-width: 800px;
}
.timeline-item {
    border-left: 3px solid;
    margin-bottom: 1rem;
    padding-left: 1rem;
    position: relative;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -7px;
    top: 0;
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: currentColor;
}
.timeline-Pendiente { border-left-color: #ffc107; color: #ffc107; }
.timeline-Completado { border-left-color: #198754; color: #198754; }
.timeline-En-proceso { border-left-color: #0dcaf0; color: #0dcaf0; }
.timeline-Cancelado { border-left-color: #6c757d; color: #6c757d; }
</style>
@endpush

@section('content')
<!-- Tarjetas de estadísticas -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card stat-card bg-primary bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-primary">Total Registros</small>
                        <h3 class="mb-0">{{ $estadisticas['total_registros'] }}</h3>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-danger bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-danger">Incidencias</small>
                        <h3 class="mb-0">{{ $estadisticas['total_incidencias'] }}</h3>
                    </div>
                    <i class="fas fa-bug fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-warning">Costo Total</small>
                        <h4 class="mb-0">${{ number_format($estadisticas['costo_total'], 0) }}</h4>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-info">Tiempo Inactivo</small>
                        <h3 class="mb-0">{{ $estadisticas['tiempo_total'] }}h</h3>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Equipo</label>
                <select name="equipo_id" class="form-select">
                    <option value="">Todos los equipos</option>
                    @foreach($equipos as $equipo)
                        <option value="{{ $equipo->id }}" {{ request('equipo_id') == $equipo->id ? 'selected' : '' }}>
                            {{ $equipo->nombre }} - {{ $equipo->numero_serie ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="Preventivo" {{ request('tipo') == 'Preventivo' ? 'selected' : '' }}>Preventivo</option>
                    <option value="Correctivo" {{ request('tipo') == 'Correctivo' ? 'selected' : '' }}>Correctivo</option>
                    <option value="Calibración" {{ request('tipo') == 'Calibración' ? 'selected' : '' }}>Calibración</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Completado" {{ request('estado') == 'Completado' ? 'selected' : '' }}>Completado</option>
                    <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                    <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de mantenimientos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Mantenimientos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaHistorial">
                <thead class="table-light">
                    <tr>
                        <th>Equipo</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha Prog.</th>
                        <th>Fecha Real.</th>
                        <th>Técnico</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mantenimientos as $mant)
                    <tr>
                        <td>
                            <strong>{{ $mant->equipo->nombre ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $mant->equipo->numero_serie ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $mant->tipo }}</span>
                        </td>
                        <td>
                            <span class="badge-estado estado-{{ str_replace(' ', '-', $mant->estado) }}">
                                {{ $mant->estado }}
                            </span>
                        </td>
                        <td>{{ $mant->fecha_programada ? \Carbon\Carbon::parse($mant->fecha_programada)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $mant->fecha_realizacion ? \Carbon\Carbon::parse($mant->fecha_realizacion)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $mant->tecnico ?? '—' }}</td>
                        <td>${{ number_format($mant->costo ?? 0, 2) }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalle({{ $mant->equipo_id }})">
                                <i class="fas fa-eye"></i> Historial
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay mantenimientos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $mantenimientos->appends(request()->query())->links() }}
    </div>
</div>

<!-- MODAL PARA HISTORIAL DEL EQUIPO -->
<div class="modal fade" id="historialModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-historial modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>
                    <span id="modalEquipoNombre">Historial del Equipo</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContenido">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando historial...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="exportarHistorial()">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
// DataTable opcional (para mejor ordenamiento)
$(document).ready(function() {
    if ($('#tablaHistorial tbody tr').length > 5) {
        $('#tablaHistorial').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[3, 'desc']] // Ordenar por fecha programada
        });
    }
});

// Función para ver historial completo del equipo en modal
function verDetalle(equipoId) {
    const modal = new bootstrap.Modal(document.getElementById('historialModal'));
    const modalContenido = document.getElementById('modalContenido');
    
    // Mostrar loading
    modalContenido.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando historial del equipo...</p>
        </div>
    `;
    
    modal.show();
    
    // Cargar datos via AJAX
    fetch(`/mantenimientos/historial-equipo/${equipoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.historial.length === 0) {
                modalContenido.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Este equipo no tiene mantenimientos registrados</p>
                    </div>
                `;
                document.getElementById('modalEquipoNombre').innerHTML = data.equipo.nombre;
                return;
            }
            
            // Generar HTML del historial
            let html = `
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Equipo</small>
                            <p class="mb-0"><strong>${data.equipo.nombre}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">N° Serie / Modelo</small>
                            <p class="mb-0">${data.equipo.numero_serie} | ${data.equipo.modelo}</p>
                        </div>
                    </div>
                </div>
                <div class="timeline">
            `;
            
            data.historial.forEach(item => {
                const estadoClass = item.estado.replace(' ', '-');
                const fecha = item.fecha_realizacion !== 'Pendiente' ? item.fecha_realizacion : item.fecha_programada;
                
                html += `
                    <div class="timeline-item timeline-${estadoClass} mb-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-secondary">${item.tipo}</span>
                                <span class="badge-estado estado-${estadoClass} ms-2">${item.estado}</span>
                            </div>
                            <small class="text-muted">${fecha}</small>
                        </div>
                        <p class="mb-1 mt-2"><strong>Técnico:</strong> ${item.tecnico}</p>
                        <p class="mb-1"><strong>Descripción:</strong> ${item.descripcion}</p>
                        ${item.solucion !== 'No registrada' ? `<p class="mb-1"><strong>Solución:</strong> ${item.solucion}</p>` : ''}
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">$${item.costo}</span>
                            <span class="badge bg-info ms-2">${item.tiempo_inactivo} horas</span>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            modalContenido.innerHTML = html;
            document.getElementById('modalEquipoNombre').innerHTML = data.equipo.nombre;
        })
        .catch(error => {
            console.error('Error:', error);
            modalContenido.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Error al cargar el historial</p>
                </div>
            `;
        });
}

// Exportar reporte actual
function exportarReporte() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/mantenimientos/exportar?${params.toString()}`;
}

// Exportar historial del modal
function exportarHistorial() {
    const equipoNombre = document.getElementById('modalEquipoNombre').innerText;
    alert(`Exportando historial de: ${equipoNombre}`);
    // Aquí puedes implementar la exportación a PDF/Excel
}

// Función global para cerrar modal (por si se necesita)
window.cerrarModal = function() {
    bootstrap.Modal.getInstance(document.getElementById('historialModal'))?.hide();
}
</script>
@endpush