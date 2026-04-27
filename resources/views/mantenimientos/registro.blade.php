@extends('layouts.app')

@section('title', 'Registro de Incidencias')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mantenimientos.agenda') }}">Mantenimiento</a></li>
    <li class="breadcrumb-item active">Registro</li>
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
<style>
.incidencia-card {
    border-left: 4px solid #dc3545;
    transition: all 0.2s;
}
.incidencia-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.costo-badge {
    font-size: 0.9rem;
    padding: 0.25rem 0.75rem;
}
.tiempo-badge {
    font-size: 0.9rem;
    padding: 0.25rem 0.75rem;
}
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.3s;
}
.stats-card:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@section('content')
<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger">Registrados</h6>
                        <h2 class="mb-0">{{ $estadisticas['total_incidencias'] }}</h2>
                    </div>
                    <i class="fas fa-bug fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-warning">Costo Total</h6>
                        <h2 class="mb-0">${{ number_format($estadisticas['costo_total'], 2) }}</h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-info">Tiempo Inactivo</h6>
                        <h2 class="mb-0">{{ $estadisticas['tiempo_total'] }}h</h2>
                    </div>
                    <i class="fas fa-clock fa-2x text-info opacity-50"></i>
                </div>
           