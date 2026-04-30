<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabCore - @yield('title')</title>
    
    <!-- pal diseño libreria importada de Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- libreria importada de Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- chart js pa los graficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
</head>
<body>
    <!-- Navbar (la barra azul de arriba del proyecto en pagina principal) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-hospital-alt me-2"></i>NeuroVida - LabCore
            </a>
            
            <!-- menu pal usuario  -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> {{ Auth::user()->name ?? 'Usuario' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i> Inicio 
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('equipos*') ? 'active' : '' }}" href="{{ route('equipos.index') }}">
                                <i class="fas fa-microscope me-2"></i> Equipos Biomedicos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('asignaciones*') ? 'active' : '' }}" href="{{ route('asignaciones.index') }}">
                                <i class="fas fa-handshake me-2"></i> Asignaciones
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('mantenimientos*') ? 'active' : '' }}" href="{{ route('mantenimientos.agenda') }}">
                                <i class="fas fa-tools me-2"></i> Mantenimiento
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('reportes*') ? 'active' : '' }}" href="{{ route('reportes.mantenimiento') }}">
                                <i class="fas fa-chart-bar me-2"></i> Reportes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('auditoria*') ? 'active' : '' }}" href="{{ route('auditoria.logs') }}">
                                <i class="fas fa-clipboard-check me-2"></i> Auditoria
                            </a>
                        </li>
                    </ul>
                    
                    <!-- filtro por departamentos (aun no funciona) -->
                    <div class="mt-4 p-3 bg-white border rounded">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1">
                            <span>Filtrar por Departamento</span>
                        </h6>
                        <select class="form-select form-select-sm" id="filter-departamento">
                            <option value="">Todos</option>
                            <option value="UCI">UCI</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="cardiologia">Cardiologia</option>
                        </select>
                    </div>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
              
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio </a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4">@yield('title')</h2>
                    <div>
                        @yield('header-buttons')
                    </div>
                </div>
                
                <!-- Alertas (aun no funcionan) -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- mas Bootstrap pero en JS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>