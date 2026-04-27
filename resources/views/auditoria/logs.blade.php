@extends('layouts.app')
@section('title', 'Auditoría')
@section('breadcrumb')
    <li class="breadcrumb-item active">Auditoría</li
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <h5>Logs del Sistema</h5>
        <p>Registro de actividades:</p>
        <table class="table table-sm">
            <tr><td>Hoy 10:30</td><td>esclavo1</td><td>Inicio sesion</td></tr>
            <tr><td>Hoy 09:15</td><td>Técnico</td><td>Actualizo equipo</td></tr>
            <tr><td>Hoy 02:15</td><td>esclavo1</td><td>Actualizo equipo y probo el codigo por curiosidad</td></tr>
        </table>
    </div>
</div>
@endsection
