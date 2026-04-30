@extends('layouts.app')

@section('title', 'Historial de Asignaciones')
@section('breadcrumb', 'Historial')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Historial de Asignaciones - {{ $equipo->nombre }}
        </h5>
        <small class="text-muted">Serie: {{ $equipo->numero_serie }}</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th>Cargo</th>
                        <th>Fecha Asignación</th>
                        <th>Fecha Devolución</th>
                        <th>Días</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial as $asig)
                    <tr>
                        <td>{{ $asig->departamento }}</td>
                        <td>{{ $asig->responsable }}</td>
                        <td>{{ $asig->cargo }}</td>
                        <td>{{ $asig->fecha_asignacion?->format('d/m/Y') }}</td>
                        <td>{{ $asig->fecha_devolucion?->format('d/m/Y') ?? 'En curso' }}</td>
                        <td>{{ $asig->dias_transcurridos }} días</td>
                        <td>
                            <span class="badge bg-{{ $asig->estado == 'Activa' ? 'success' : 'secondary' }}">
                                {{ $asig->estado }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted"></i>
                            <p class="mt-2">No hay historial de asignaciones para este equipo</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $historial->links() }}
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>
@endsection