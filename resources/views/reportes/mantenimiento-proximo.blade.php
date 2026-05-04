@extends('layouts.app')

@section('title', 'Mantenimiento Próximo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Mantenimiento Próximo</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>
            Equipos con Mantenimiento Próximo
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Reporte en construcción. Próximamente disponible.
        </div>
        
        {{-- Puedes agregar contenido estático de ejemplo --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Serie</th>
                        <th>Próximo Mantenimiento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection