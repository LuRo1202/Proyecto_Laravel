<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Asignación de Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-active: #ffffff;
            --sidebar-link-bg-hover-active: rgba(255, 255, 255, 0.1);
            --sidebar-active-border: #8856dd;
            --sidebar-active-bg: #8856dd;
            --primary-dark: #8856dd;
            --primary-light: #ece0ff;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: var(--sidebar-link-color) !important; padding: 0.75rem 1rem; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; }
        .sidebar .nav-link:hover { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-link-bg-hover-active); }
        .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg); border-left: 5px solid var(--sidebar-active-border); padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .user-info-name { font-weight: 600; font-size: 1.05rem; margin-bottom: 0.5rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 50px; }
        .main-content { min-height: 100vh; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center; display: none; }
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
        .table-hover > tbody > tr:hover, .table > tbody > tr.selected { background-color: var(--primary-light) !important; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--primary-dark) !important; color: white !important; border-color: var(--primary-dark) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-light) !important; color: #333 !important; border-color: var(--primary-light) !important; }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" role="status"><span class="visually-hidden">Cargando...</span></div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="{{ asset('imagenes/data.png') }}" alt="Logo" class="img-fluid" style="max-height: 90px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>
                    <div class="user-info">
                        <div class="user-info-name">
                            @if(Auth::user()->administrador)
                                {{ Auth::user()->administrador->nombre }} {{ Auth::user()->administrador->apellido_paterno }}
                            @else
                                {{ Auth::user()->correo }}
                            @endif
                        </div>
                        <span class="user-info-role">Administrador</span>
                    </div>
                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Inicio</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('admin.asignaciones.index') }}"><i class="bi bi-people-fill me-2"></i>Asignar Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.estudiantes.index') }}"><i class="bi bi-person-vcard me-2"></i>Gestión Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-badge me-2"></i>Gestión Responsables</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-gear me-2"></i>Gestión Administradores</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i>Gestión Personal de Vinculación</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clock-history me-2"></i>Registro Horas</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
            
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="#" id="btn-logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Asignación de Estudiantes a Responsables</h1>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header"><i class="bi bi-person-plus-fill me-2"></i>Estudiantes sin Asignar</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tablaEstudiantes" class="table table-hover" style="width:100%">
                                        <thead><tr><th><input type="checkbox" id="selectAllEstudiantes"></th><th>Matrícula</th><th>Nombre</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 d-flex flex-column justify-content-center align-items-center">
                         <div class="text-center">
                            <p class="mb-2"><strong><span id="contadorEstudiantes">0</span></strong> estudiantes</p>
                            <button class="btn btn-primary btn-lg" id="btnAsignar" disabled>Asignar <i class="bi bi-arrow-right-circle-fill"></i></button>
                            <p class="mt-2 text-muted small">A:</p>
                            <strong id="responsableSeleccionado" class="text-success"></strong>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header"><i class="bi bi-person-check-fill me-2"></i>Responsables Disponibles</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tablaResponsables" class="table table-hover" style="width:100%">
                                        <thead><tr><th>Nombre</th><th>Cargo</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Asignaciones Actuales</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaAsignaciones" class="table table-hover" style="width:100%">
                                <thead><tr><th>Estudiante</th><th>Responsable</th><th>Fecha</th><th>Acciones</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/adminjs/asignacion_estudiantes.js') }}"></script>
</body>
</html>