@extends('layouts.app')

@section('title', 'Reportes del Sistema')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@push('head-scripts')
<style>
.report-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 16px;
    cursor: pointer;
}
.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.report-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 28px;
}
.bg-soft-primary { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.bg-soft-success { background: rgba(25, 135, 84, 0.1); color: #198754; }
.bg-soft-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.bg-soft-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
.bg-soft-info { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
.bg-soft-secondary { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Panel de Reportes
                    </h5>
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ now()->format('d/m/Y') }}
                    </small>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Reporte Principal Destacado -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-primary bg-gradient rounded-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-microscope fa-3x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="alert-heading mb-1">📊 Reporte de Estado de Equipos</h5>
                                    <p class="mb-0 small">Consulta en tiempo real el estado actual de todos los equipos biomédicos</p>
                                </div>
                                <div>
                                    <a href="{{ route('reportes.estado-equipos') }}" class="btn btn-light rounded-pill">
                                        Ver Reporte <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Reportes -->
                <div class="row g-4">
                    
                    <!-- Reporte 1: Estado de Equipos -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm" onclick="window.location='{{ route('reportes.estado-equipos') }}'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="report-icon bg-soft-primary">
                                        <i class="fas fa-microscope"></i>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Principal</span>
                                </div>
                                <h5 class="card-title">Estado de Equipos</h5>
                                <p class="card-text text-muted small">
                                    Consulta el estado actual de cada equipo biomédico: Activo, Mantenimiento o De Baja.
                                </p>
                                <div class="mt-3">
                                    <small class="text-primary">
                                        Ver detalles <i class="fas fa-chevron-right ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte 2: Mantenimiento Próximo -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="report-icon bg-soft-warning mb-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h5 class="card-title">Mantenimiento Próximo</h5>
                                <p class="card-text text-muted small">
                                    Equipos que requieren mantenimiento en los próximos días. Planificación anticipada.
                                </p>
                                <div class="mt-3">
                                    <small class="text-warning">
                                        Próximamente <i class="fas fa-clock ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte 3: Costos por Departamento -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="report-icon bg-soft-success mb-3">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h5 class="card-title">Costos por Departamento</h5>
                                <p class="card-text text-muted small">
                                    Análisis de costos operativos de mantenimiento por cada departamento.
                                </p>
                                <div class="mt-3">
                                    <small class="text-success">
                                        Próximamente <i class="fas fa-clock ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte 4: Disponibilidad de Equipos -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="report-icon bg-soft-info mb-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="card-title">Disponibilidad de Equipos</h5>
                                <p class="card-text text-muted small">
                                    Historial de disponibilidad de equipos críticos y tiempo operativo.
                                </p>
                                <div class="mt-3">
                                    <small class="text-info">
                                        Próximamente <i class="fas fa-clock ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte 5: Bitácora de Incidencias -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="report-icon bg-soft-danger mb-3">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h5 class="card-title">Bitácora de Incidencias</h5>
                                <p class="card-text text-muted small">
                                    Registro detallado de reparaciones correctivas y tiempos de inactividad.
                                </p>
                                <div class="mt-3">
                                    <small class="text-danger">
                                        Próximamente <i class="fas fa-clock ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte 6: Exportar Datos -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="report-icon bg-soft-secondary mb-3">
                                    <i class="fas fa-file-export"></i>
                                </div>
                                <h5 class="card-title">Exportar Datos</h5>
                                <p class="card-text text-muted small">
                                    Genera reportes en Excel, PDF o CSV para análisis externo.
                                </p>
                                <div class="mt-3">
                                    <small class="text-secondary">
                                        Próximamente <i class="fas fa-clock ms-1"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas (opcional) -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-chart-simple me-2"></i>Resumen Rápido
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h4 class="text-primary mb-0">0</h4>
                                        <small class="text-muted">Equipos Activos</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-warning mb-0">0</h4>
                                        <small class="text-muted">En Mantenimiento</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-success mb-0">0</h4>
                                        <small class="text-muted">Mantenimientos Hoy</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Efecto hover adicional
document.querySelectorAll('.report-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Si el enlace no existe, no hacer nada
        if (!this.getAttribute('onclick')) {
            console.log('Reporte en construcción');
        }
    });
});
</script>
@endpush