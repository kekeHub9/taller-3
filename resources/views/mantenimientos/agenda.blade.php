@extends('layouts.app')

@section('title', 'Agenda de Mantenimiento')
@section('breadcrumb')
   
    <li class="breadcrumb-item active">Agenda de Mantenimiento</li>
@endsection

@section('header-buttons')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoMantenimientoModal">
        <i class="fas fa-calendar-plus me-1"></i>Programar Mantenimiento
    </button>
    <a href="{{ route('mantenimientos.registro') }}" class="btn btn-outline-secondary">
        <i class="fas fa-history me-1"></i>Ver Historial
    </a>
@endsection

@push('head-scripts')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
<style>
#calendar {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.fc-event {
    cursor: pointer;
}
.mantenimiento-card {
    border-left: 4px solid;
    transition: all 0.2s;
}
.mantenimiento-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.estado-pendiente { border-left-color: #ffc107; }
.estado-proceso { border-left-color: #0dcaf0; }
.estado-completado { border-left-color: #198754; }
.estado-cancelado { border-left-color: #6c757d; }
</style>
@endpush

@section('content')
<!-- Asegurar que todas las variables existen, valida cada vaina -->
@php
    // Variables de filtro (vienen del request)
    $fechaInicio = request('fecha_inicio', '');
    $fechaFin = request('fecha_fin', '');
    
    // Variables de datos (vienen del controlador)
    $mantenimientos = $mantenimientos ?? collect();
    $alertas = $alertas ?? [];
    $equipos = $equipos ?? collect();
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Calendario de Mantenimientos
                </h5>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filtros y Estadísticas</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                    </div>
                </form>
                
                <div class="row mt-4">
                    <div class="col-md-3 text-center">
                        <div class="card border-warning">
                            <div class="card-body py-3">
                                <h6 class="text-warning">Pendientes</h6>
                                <h3 class="mb-0">
                                    {{ $mantenimientos->flatten()->where('estado', 'Pendiente')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card border-info">
                            <div class="card-body py-3">
                                <h6 class="text-info">En Proceso</h6>
                                <h3 class="mb-0">
                                    {{ $mantenimientos->flatten()->where('estado', 'En proceso')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card border-success">
                            <div class="card-body py-3">
                                <h6 class="text-success">Completados</h6>
                                <h3 class="mb-0">
                                    {{ $mantenimientos->flatten()->where('estado', 'Completado')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card border-secondary">
                            <div class="card-body py-3">
                                <h6 class="text-secondary">Cancelados</h6>
                                <h3 class="mb-0">
                                    {{ $mantenimientos->flatten()->where('estado', 'Cancelado')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        
        @if(count($alertas) > 0)
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Alertas Activas
                    <span class="badge bg-warning ms-2">{{ count($alertas) }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($alertas as $alerta)
                    <div class="list-group-item py-3">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle fa-lg text-{{ 
                                    $alerta['prioridad'] == 'alta' ? 'danger' : 'warning' 
                                }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1 small">{{ $alerta['mensaje'] }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $alerta['fecha'] }}</small>
                                    <a href="{{ $alerta['accion'] ?? '#' }}" class="btn btn-sm btn-outline-{{ 
                                        $alerta['prioridad'] == 'alta' ? 'danger' : 'warning' 
                                    }}">
                                        Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Próximos 7 Días
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @php
                        $proximos = $mantenimientos->flatten()
                            ->where('estado', 'Pendiente')
                            ->where('fecha_programada', '>=', now())
                            ->where('fecha_programada', '<=', now()->addDays(7))
                            ->sortBy('fecha_programada');
                    @endphp
                    
                    @forelse($proximos as $mantenimiento)
                    <div class="list-group-item p-3 mantenimiento-card estado-pendiente">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">{{ $mantenimiento->equipo->nombre ?? 'N/A' }}</h6>
                            <span class="badge bg-warning">Pendiente</span>
                        </div>
                        <p class="mb-1 small text-muted">
                            <i class="fas fa-tools me-1"></i>{{ $mantenimiento->tipo ?? 'N/A' }}
                        </p>
                        <p class="mb-2 small">
                            <i class="fas fa-user me-1"></i>{{ $mantenimiento->tecnico ?? 'N/A' }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ isset($mantenimiento->fecha_programada) ? $mantenimiento->fecha_programada->format('d/m/Y') : 'N/A' }}
                            </small>
                            @if(isset($mantenimiento->id))
                            <form action="{{ route('mantenimientos.completar', $mantenimiento->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item p-4 text-center">
                        <i class="fas fa-calendar-check fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No hay mantenimientos programados para los próximos 7 días</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @if($proximos->count() > 0)
            <div class="card-footer text-center py-2">
                <a href="#calendar" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calendar-alt me-1"></i>Ver Calendario Completo
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- nuevo Mantenimiento -->
<div class="modal fade" id="nuevoMantenimientoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>Programar Nuevo Mantenimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('mantenimientos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Equipo *</label>
                            <select class="form-select" name="equipo_id" required>
                                <option value="">Seleccionar tipo...</option>
                                <!-- son de ADORNO, NO FUNCIONAAAAN -->
                                <option value="Preventivo">Grande</option>
                                <option value="Correctivo">Largo</option>
                                <option value="Calibración">Duro</option>
                            </select>
                                @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}">
                                    {{ $equipo->numero_serie ?? '' }} - {{ $equipo->nombre ?? 'N/A' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Mantenimiento *</label>
                            <select class="form-select" name="tipo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="Preventivo">Preventivo</option>
                                <option value="Correctivo">Correctivo</option>
                                <option value="Calibración">Calibración</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Programada *</label>
                            <input type="date" class="form-control" name="fecha_programada" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Técnico Responsable *</label>
                            <input type="text" class="form-control" name="tecnico" 
                                   placeholder="Nombre del técnico" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción *</label>
                        <textarea class="form-control" name="descripcion" rows="3" 
                                  placeholder="Describa el mantenimiento a realizar..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo Estimado ($)</label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="costo" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiempo Estimado (horas)</label>
                            <input type="number" class="form-control" name="tiempo_inactivo" 
                                   placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Programar Mantenimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    //calendario
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '{{ route("mantenimientos.api.calendario") ?? "#" }}',
            eventClick: function(info) {
                const mantenimientoId = info.event.id;
                if (mantenimientoId) {
                    window.location.href = `/mantenimientos/${mantenimientoId}/edit`;
                }
            },
            eventColor: '#0d6efd',
            eventTextColor: 'white',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            }
        });
        
        calendar.render();
    }
    
    const fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    const fechaFinInput = document.querySelector('input[name="fecha_fin"]');
    
    if (fechaInicioInput && fechaFinInput && calendarEl) {
        fechaInicioInput.addEventListener('change', function() {
            calendar.gotoDate(this.value);
        });
        
        fechaFinInput.addEventListener('change', function() {
    
            const endDate = new Date(this.value);
            calendar.gotoDate(endDate);
        });
    }
    
    const tecnicos = ['Douglas Perez', 'Maira Lopez', 'Efrain Rodríguez', 'Laura Martinez', 'Gomez'];
    const tecnicoInput = document.querySelector('input[name="tecnico"]');
    
    if (tecnicoInput) {
        const datalist = document.createElement('datalist');
        datalist.id = 'tecnicosList';
        
        tecnicos.forEach(tecnico => {
            const option = document.createElement('option');
            option.value = tecnico;
            datalist.appendChild(option);
        });
        
        document.body.appendChild(datalist);
        tecnicoInput.setAttribute('list', 'tecnicosList');
    }
    
    const completarForms = document.querySelectorAll('form[action*="completar"]');
    completarForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Marcar este mantenimiento como completado?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush