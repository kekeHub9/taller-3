@extends('layouts.app')

@section('title', 'Equipos Biomédicos')
@section('breadcrumb', 'Equipos')

@push('head-scripts')
<style>
    .btn-action {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
    }
    .modal-lg-custom {
        max-width: 800px;
    }
    .table-equipos td {
        vertical-align: middle;
        font-size: 0.85rem;
    }
    .table-equipos th {
        font-size: 0.8rem;
        background-color: #f8f9fc;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="fas fa-microscope me-2"></i>Lista de Equipos
    </h4>
    <!-- CAMBIADO: Botón normal que redirige a create -->
    <a href="{{ route('equipos.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Añadir Equipo
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-equipos mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>N° Serie</th>
                        <th>Nombre</th>
                        <th>Marca/Modelo</th>
                        <th>Tipo</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <th>Próx. Calibración</th>
                        <th style="width: 100px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="equiposTableBody">
                    @foreach($equipos as $equipo)
                    <tr id="equipo-row-{{ $equipo->id }}">
                        <td>{{ $equipo->id }}</td>
                        <td><code>{{ $equipo->numero_serie }}</code></td>
                        <td>
                            <strong>{{ $equipo->nombre }}</strong>
                            @if($equipo->modelo)
                            <br><small class="text-muted">{{ $equipo->modelo }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $equipo->marca ?? 'N/A' }}<br>
                            <small>{{ $equipo->modelo ?? '' }}</small>
                        </td>
                        <td>{{ $equipo->tipo ?? 'N/A' }}</td>
                        <td>{{ $equipo->departamento ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ 
                                $equipo->estado == 'Activo' ? 'success' : 
                                ($equipo->estado == 'Mantenimiento' ? 'warning' : 
                                ($equipo->estado == 'Reparación' ? 'danger' : 'secondary')) 
                            }}">
                                {{ $equipo->estado }}
                            </span>
                        </td>
                        <td>
                            @if($equipo->proxima_calibracion)
                                {{ $equipo->proxima_calibracion->format('d/m/Y') }}
                                @php $dias = now()->diffInDays($equipo->proxima_calibracion, false); @endphp
                                @if($dias < 7 && $dias > 0)
                                    <span class="badge bg-warning text-dark">en {{ $dias }}d</span>
                                @elseif($dias <= 0)
                                    <span class="badge bg-danger">vencida</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- CAMBIADO: Editar redirige a edit -->
                                <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn btn-outline-info btn-action" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- CAMBIADO: Eliminar con formulario oculto -->
                                <button class="btn btn-outline-danger btn-action" onclick="deleteEquipo({{ $equipo->id }})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="{{ route('equipos.show', $equipo->id) }}" class="btn btn-outline-secondary btn-action" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($equipos->isEmpty())
<div class="text-center py-4 text-muted">
    <i class="fas fa-inbox fa-3x mb-2"></i>
    <p>No hay equipos registrados. ¡Agrega tu primer equipo!</p>
</div>
@endif
@endsection

@push('scripts')
<script>
function deleteEquipo(id) {
    if (confirm('¿Estás seguro de eliminar este equipo?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/equipos/' + id;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush