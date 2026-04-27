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
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#equipoModal" onclick="resetForm()">
        <i class="fas fa-plus me-1"></i>Añadir Equipo
    </button>
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
                                <button class="btn btn-outline-info btn-action" onclick="editEquipo({{ $equipo->id }})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
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

<!-- MODAL PARA CREAR/EDITAR EQUIPO -->
<div class="modal fade" id="equipoModal" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg-custom modal-lg">
        <div class="modal-content">
            <form id="equipoForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="equipo_id" id="equipoId" value="">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="equipoModalLabel">
                        <i class="fas fa-microscope me-2"></i>
                        <span id="modalTitle">Nuevo Equipo</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Serie <span class="text-danger">*</span></label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Equipo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-select form-select-sm" required>
                                <option value="">Seleccionar</option>
                                <option value="Diagnóstico">Diagnóstico</option>
                                <option value="Monitoreo">Monitoreo</option>
                                <option value="Tratamiento">Tratamiento</option>
                                <option value="Laboratorio">Laboratorio</option>
                                <option value="Imagenología">Imagenología</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" id="marca" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" id="modelo" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento" id="departamento" class="form-select form-select-sm" required>
                                <option value="">Seleccionar</option>
                                <option value="UCI">UCI</option>
                                <option value="Laboratorio">Laboratorio</option>
                                <option value="Cardiología">Cardiología</option>
                                <option value="Radiología">Radiología</option>
                                <option value="Urgencias">Urgencias</option>
                                <option value="Hospitalización">Hospitalización</option>
                                <option value="Quirófano">Quirófano</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select form-select-sm" required>
                                <option value="Activo">Activo</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Reparación">Reparación</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Adquisición</label>
                            <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Proveedor</label>
                            <input type="text" name="proveedor" id="proveedor" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo (USD)</label>
                            <input type="number" step="0.01" name="costo" id="costo" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Última Calibración</label>
                            <input type="date" name="ultima_calibracion" id="ultima_calibracion" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Próxima Calibración</label>
                            <input type="date" name="proxima_calibracion" id="proxima_calibracion" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vida Útil (años)</label>
                            <input type="number" name="vida_util" id="vida_util" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>
                        <span id="submitBtnText">Guardar Equipo</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let editingId = null;

function resetForm() {
    editingId = null;
    document.getElementById('modalTitle').innerText = 'Nuevo Equipo';
    document.getElementById('submitBtnText').innerText = 'Guardar Equipo';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('equipoForm').action = '{{ route("equipos.store") }}';
    document.getElementById('equipoForm').reset();
    document.getElementById('equipoId').value = '';
}

function editEquipo(id) {
    editingId = id;
    
    fetch(`/api/equipos/${id}`)
        .then(response => response.json())
        .then(equipo => {
            document.getElementById('modalTitle').innerText = 'Editar Equipo';
            document.getElementById('submitBtnText').innerText = 'Actualizar Equipo';
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('equipoForm').action = `/equipos/${id}`;
            document.getElementById('equipoId').value = equipo.id;
            
            document.getElementById('numero_serie').value = equipo.numero_serie;
            document.getElementById('nombre').value = equipo.nombre;
            document.getElementById('tipo').value = equipo.tipo;
            document.getElementById('marca').value = equipo.marca || '';
            document.getElementById('modelo').value = equipo.modelo || '';
            document.getElementById('departamento').value = equipo.departamento;
            document.getElementById('estado').value = equipo.estado;
            document.getElementById('fecha_adquisicion').value = equipo.fecha_adquisicion || '';
            document.getElementById('proveedor').value = equipo.proveedor || '';
            document.getElementById('costo').value = equipo.costo || '';
            document.getElementById('ultima_calibracion').value = equipo.ultima_calibracion || '';
            document.getElementById('proxima_calibracion').value = equipo.proxima_calibracion || '';
            document.getElementById('vida_util').value = '';
            
            new bootstrap.Modal(document.getElementById('equipoModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function deleteEquipo(id) {
    if (confirm('¿Estás seguro de eliminar este equipo? Esta acción no se puede deshacer.')) {
        fetch(`/equipos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`equipo-row-${id}`).remove();
                showToast('Equipo eliminado exitosamente', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

document.getElementById('equipoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const method = document.getElementById('formMethod').value;
    const url = form.action;
    
    let fetchOptions = {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    };
    
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, fetchOptions)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('equipoModal')).hide();
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
});

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>
@endpush