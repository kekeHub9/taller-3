@extends('layouts.app')
@section('title', 'Reportes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reportes</li>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h5>Reportes del Sistema</h5>
        <p>Seleccione un reporte:</p>
        <div class="list-group">
            <a href="#" class="list-group-item">Mantenimiento Proximo</a>
            <a href="#" class="list-group-item">Costos por Departamento</a>
            <a href="#" class="list-group-item">Disponibilidad de Equipos</a>
        </div>
    </div>
</div>
@endsection
