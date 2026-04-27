@extends('layouts.app')

@section('title', 'BioManage Sys')
@section('breadcrumb')

@push('head-scripts')
<style>
.chart-container {
    position: relative;
    height: 100px;
    width: 100%;
}
.alert-card {
    border-left: 3px solid;
    transition: transform 0.2s;
    padding: 0.4rem 0.6rem !important;
}
.alert-card:hover {
    transform: translateX(3px);
}
.alert-alta { border-left-color: #dc3545; }
.alert-media { border-left-color: #ffc107; }
.alert-baja { border-left-color: #6c757d; }
.event-timeline {
    position: relative;
    padding-left: 20px;
}
.event-timeline::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.event-item {
    position: relative;
    margin-bottom: 0.8rem;
}
.event-item::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #0d6efd;
}
/* Tarjetas pequeñas */
.card-small-stats {
    transition: all 0.2s;
}
.card-small-stats .card-body {
    padding: 0.6rem 1rem !important;
}
.card-small-stats .h5 {
    font-size: 1.3rem !important;
    margin-bottom: 0 !important;
}
.card-small-stats .text-xs {
    font-size: 0.6rem !important;
}
.card-small-stats .fa-2x {
    font-size: 1.5rem !important;
}
/* Alertas compactas */
.alertas-compactas {
    max-height: 250px;
    overflow-y: auto;
}
.alertas-compactas .list-group-item {
    padding: 0.4rem 0.6rem !important;
}
.alertas-compactas .fa-lg {
    font-size: 0.9rem !important;
}
.alertas-compactas h6 {
    font-size: 0.75rem !important;
    margin-bottom: 0 !important;
}
.alertas-compactas .small {
    font-size: 0.65rem !important;
}
/* Accordion */
.accordion-button:not(.collapsed) {
    background-color: #f8f9fc;
    color: #4e73df;
}
.accordion-button {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}
.accordion-body {
    padding: 0.5rem;
}
.grafico-card .card-header {
    padding: 0.5rem 1rem;
}
.grafico-card .card-body {
    padding: 0.75rem;
}
</style>
@endpush

@section('content')
<!-- TARJETAS ESTADÍSTICAS PEQUEÑAS -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-primary border-3 shadow-sm card-small-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">Equipos Activos</div>
                        <div class="h5 fw-bold text-gray-800">{{ $equiposActivos }}</div>
                        <div class="small text-muted">
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $totalEquipos - $equiposActivos }}</span>
                            <span> otros</span>
                        </div>
                    </div>
                    <div><i class="fas fa-microscope fa-2x text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-warning border-3 shadow-sm card-small-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">Mant. Pendientes</div>
                        <div class="h5 fw-bold text-gray-800">{{ $mantenimientosPendientes }}</div>
                        <div class="small text-muted">
                            <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ count(array_filter($alertas, fn($a) => $a['prioridad'] == 'alta')) }}</span>
                            <span> críticos</span>
                        </div>
                    </div>
                    <div><i class="fas fa-tools fa-2x text-warning"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-danger border-3 shadow-sm card-small-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs fw-bold text-danger text-uppercase mb-1">En Reparación</div>
                        <div class="h5 fw-bold text-gray-800">{{ $enReparacion }}</div>
                        <div class="small text-muted">
                            <span class="text-info"><i class="fas fa-clock me-1"></i>
                                @php
                                    $promedio = $enReparacion > 0 ? array_sum(array_column(array_filter($alertas, fn($a) => $a['tipo'] == 'reparacion'), 'dias_reparacion')) / $enReparacion : 0;
                                @endphp
                                {{ round($promedio) }}d
                            </span>
                            <span> promedio</span>
                        </div>
                    </div>
                    <div><i class="fas fa-wrench fa-2x text-danger"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-success border-3 shadow-sm card-small-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Departamentos</div>
                        <div class="h5 fw-bold text-gray-800">{{ $totalDepartamentos }}</div>
                        <div class="small text-muted">
                            @php
                                $deptos = array_keys($equiposPorDepartamento);
                                $topDepto = count($deptos) > 0 ? $deptos[0] : 'N/A';
                            @endphp
                            <span class="text-primary"><i class="fas fa-hospital me-1"></i>{{ $topDepto }}</span>
                            <span> más equipos</span>
                        </div>
                    </div>
                    <div><i class="fas fa-hospital fa-2x text-success"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ALERTAS + ACTIVIDAD RECIENTE (juntas en fila superior) -->
<div class="row mb-3">
    <!-- ALERTAS ACTIVAS -->
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>Alertas Activas
                    <span class="badge bg-warning ms-1">{{ count($alertas) }}</span>
                </h6>
                @if(count($alertas) > 0)
                <a href="{{ route('mantenimientos.agenda') }}" class="btn btn-sm btn-outline-warning py-0 px-1" style="font-size: 0.7rem;">
                    <i class="fas fa-tasks me-1"></i>Gestionar
                </a>
                @endif
            </div>
            <div class="card-body p-0 alertas-compactas">
                <div class="list-group list-group-flush">
                    @forelse($alertas as $alerta)
                    <div class="list-group-item alert-card alert-{{ $alerta['prioridad'] }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="{{ $alerta['icono'] }} text-{{ $alerta['prioridad'] == 'alta' ? 'danger' : ($alerta['prioridad'] == 'media' ? 'warning' : 'secondary') }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $alerta['equipo'] }}</strong>
                                    <small class="text-muted" style="font-size: 0.6rem;">{{ $alerta['fecha'] }}</small>
                                </div>
                                <p class="mb-0 small">{{ $alerta['detalle'] }}</p>
                                <div>
                                    <small class="text-muted" style="font-size: 0.6rem;">Serie: {{ $alerta['serie'] }}</small>
                                    <span class="badge bg-{{ $alerta['prioridad'] == 'alta' ? 'danger' : ($alerta['prioridad'] == 'media' ? 'warning' : 'secondary') }} ms-1" style="font-size: 0.6rem;">{{ ucfirst($alerta['prioridad']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-2">
                        <i class="fas fa-check-circle text-success mb-1"></i>
                        <p class="small text-muted mb-0">¡Todo bajo control!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- ACTIVIDAD RECIENTE DESPLEGABLE -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header py-1">
                <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-history me-1"></i>Actividad Reciente</h6>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="accordionActividad">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-light py-1" style="font-size: 0.75rem;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActividad">
                                <i class="fas fa-clock me-2"></i> Ver últimos eventos ({{ $ultimosEventos->count() }})
                            </button>
                        </h2>
                        <div id="collapseActividad" class="accordion-collapse collapse" data-bs-parent="#accordionActividad">
                            <div class="accordion-body" style="padding: 0.5rem;">
                                <div class="event-timeline">
                                    @forelse($ultimosEventos as $evento)
                                    <div class="event-item">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-{{ $evento['color'] }} bg-opacity-10 p-1">
                                                    <i class="{{ $evento['icono'] }} text-{{ $evento['color'] }}" style="font-size: 0.7rem;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <p class="mb-0 small" style="font-size: 0.7rem;">{{ $evento['descripcion'] }}</p>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted" style="font-size: 0.6rem;">
                                                        @if(isset($evento['tecnico']))
                                                        <i class="fas fa-user me-1"></i>{{ $evento['tecnico'] }}
                                                        @elseif(isset($evento['responsable']))
                                                        <i class="fas fa-user-tie me-1"></i>{{ $evento['responsable'] }}
                                                        @elseif(isset($evento['serie']))
                                                        <i class="fas fa-barcode me-1"></i>{{ $evento['serie'] }}
                                                        @endif
                                                    </small>
                                                    <small class="text-muted" style="font-size: 0.6rem;">{{ $evento['fecha'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-2">
                                        <i class="fas fa-inbox text-muted mb-1"></i>
                                        <p class="small text-muted mb-0">No hay actividad reciente</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center py-0">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('mantenimientos.registro') }}" class="btn btn-outline-primary py-0" style="font-size: 0.7rem;"><i class="fas fa-tools me-1"></i>Mant.</a>
                    <a href="{{ route('asignaciones.index') }}" class="btn btn-outline-primary py-0" style="font-size: 0.7rem;"><i class="fas fa-handshake me-1"></i>Asig.</a>
                    <a href="{{ route('equipos.index') }}" class="btn btn-outline-primary py-0" style="font-size: 0.7rem;"><i class="fas fa-microscope me-1"></i>Eq.</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRaFICOS -->
<div class="row">
    <div class="col-xl-4 col-lg-6 mb-3">
        <div class="card shadow-sm grafico-card">
            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-hospital me-1"></i>Equipos por Departamento</h6>
                <a class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 0.7rem;" href="{{ route('equipos.index') }}">Ver</a>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="departamentoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-6 mb-3">
        <div class="card shadow-sm grafico-card">
            <div class="card-header py-1">
                <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-chart-pie me-1"></i>Estado de Equipos</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="estadoChart"></canvas>
                </div>
                <div class="mt-1 text-center small" style="font-size: 0.6rem;">
                    @foreach($equiposPorEstado as $estado => $cantidad)
                    <span class="me-1">
                        <i class="fas fa-circle me-1 text-{{ $estado == 'Activo' ? 'success' : ($estado == 'Mantenimiento' ? 'warning' : ($estado == 'Reparación' ? 'danger' : 'secondary')) }}" style="font-size: 0.5rem;"></i>
                        {{ $estado }} ({{ $cantidad }})
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-12 mb-3">
        <div class="card shadow-sm grafico-card">
            <div class="card-header py-1">
                <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-chart-line me-1"></i>Mantenimientos (6 meses)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="mantenimientosChart"></canvas>
                </div>
                <div class="mt-1 text-center">
                    <span class="badge bg-success" style="font-size: 0.6rem;">
                        <i class="fas fa-arrow-up me-1"></i>{{ $crecimiento ?? 0 }}%
                    </span>
                    <span class="small text-muted ms-1" style="font-size: 0.6rem;">crecimiento</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EQUIPOS CRÍTICOS DESPLEGABLE -->
@if($equiposCriticos->count() > 0)
<div class="row">
    <div class="col-12 mb-3">
        <div class="card shadow-sm">
            <div class="accordion" id="accordionCriticos">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-light py-1 text-danger" style="font-size: 0.75rem;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCriticos">
                            <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                            <span class="text-danger">Equipos que Requieren Atención ({{ $equiposCriticos->count() }})</span>
                        </button>
                    </h2>
                    <div id="collapseCriticos" class="accordion-collapse collapse" data-bs-parent="#accordionCriticos">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.7rem;">
                                    <thead class="table-light">
                                        <tr><th>Equipo</th><th>Serie</th><th>Departamento</th><th>Estado</th><th>Calibración</th><th>Días</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equiposCriticos as $equipo)
                                        @php $diasRestantes = $equipo->proxima_calibracion ? now()->diffInDays($equipo->proxima_calibracion, false) : null; @endphp
                                        <tr class="{{ $equipo->estado == 'Reparación' ? 'table-danger' : ($diasRestantes !== null && $diasRestantes < 3 ? 'table-warning' : '') }}">
                                            <td><strong>{{ $equipo->nombre }}</strong><br><small>{{ $equipo->modelo }}</small></td>
                                            <td>{{ $equipo->numero_serie }}</td>
                                            <td>{{ $equipo->departamento }}</td>
                                            <td><span class="badge bg-{{ $equipo->estado == 'Activo' ? 'success' : ($equipo->estado == 'Mantenimiento' ? 'warning' : 'danger') }}" style="font-size: 0.6rem;">{{ $equipo->estado }}</span></td>
                                            <td>{{ $equipo->proxima_calibracion ? $equipo->proxima_calibracion->format('d/m/Y') : 'N/A' }}</td>
                                            <td>@if($diasRestantes !== null)<span class="badge bg-{{ $diasRestantes < 0 ? 'danger' : ($diasRestantes < 3 ? 'warning' : ($diasRestantes < 7 ? 'info' : 'success')) }}" style="font-size: 0.6rem;">{{ $diasRestantes < 0 ? 'Vencido' : $diasRestantes . 'd' }}</span>@endif</td>
                                            <td><div class="btn-group btn-group-sm"><a href="{{ route('equipos.show', $equipo->id) }}" class="btn btn-outline-info py-0 px-1" style="font-size: 0.6rem;"><i class="fas fa-eye"></i></a><a href="{{ route('mantenimientos.agenda') }}" class="btn btn-outline-warning py-0 px-1" style="font-size: 0.6rem;"><i class="fas fa-calendar-plus"></i></a></div></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('departamentoChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($equiposPorDepartamento)) !!},
            datasets: [{ label: 'Equipos', data: {!! json_encode(array_values($equiposPorDepartamento)) !!}, backgroundColor: '#0d6efd', borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }, x: { ticks: { font: { size: 8 } } } } }
    });

    new Chart(document.getElementById('estadoChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($equiposPorEstado)) !!},
            datasets: [{ data: {!! json_encode(array_values($equiposPorEstado)) !!}, backgroundColor: ['#198754', '#ffc107', '#dc3545', '#6c757d'], borderWidth: 1 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 8 }, boxWidth: 8 } } }, cutout: '60%' }
    });
    
    const mesesLabels = Object.keys({!! json_encode($mantenimientosPorMes) !!}).map(mes => {
        const [year, month] = mes.split('-');
        return new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'short', year: '2-digit' });
    });
    
    new Chart(document.getElementById('mantenimientosChart'), {
        type: 'line',
        data: {
            labels: mesesLabels,
            datasets: [{ data: {!! json_encode(array_values($mantenimientosPorMes)) !!}, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', fill: true, tension: 0.3, pointRadius: 2 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }, x: { ticks: { font: { size: 8 } } } } }
    });
});
</script>
@endpush