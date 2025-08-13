<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Personal de Vinculación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #2c3e50; --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-active: #ffffff; --sidebar-link-bg-hover-active: rgba(255, 255, 255, 0.1);
            --sidebar-active-border: #8856dd; --sidebar-active-bg: #8856dd;
            --primary-dark: #8856dd; --primary-light: #ece0ff;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); }
        .sidebar .nav-link { color: var(--sidebar-link-color) !important; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-link-bg-hover-active); }
        .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg) !important; border-left: 5px solid var(--sidebar-active-border) !important; padding-left: calc(1rem - 5px) !important; }
        .btn-action-group { display: flex; gap: 5px; justify-content: center; }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-info-name { font-weight: 600; font-size: 1.05rem; margin-bottom: 0.5rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 50px; }
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
        .table-hover > tbody > tr:hover { background-color: var(--primary-light) !important; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--primary-dark) !important; color: white !important; border-color: var(--primary-dark) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-light) !important; color: #333 !important; border-color: var(--primary-light) !important; }
        #tablaVinculacion th { background-color: #f1f2f4; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="{{ asset('imagenes/data.png') }}" alt="Logo" class="img-fluid" style="max-height: 90px;">
                        <h5 class="text-white mt-2">Servicio Social</h5>
                    </div>
                    <div class="user-info">
                        <div class="user-info-name">@if(Auth::user()->administrador){{ Auth::user()->administrador->nombre }} {{ Auth::user()->administrador->apellido_paterno }}@else{{ Auth::user()->correo }}@endif</div>
                        <span class="user-info-role">Administrador</span>
                    </div>
                    <ul class="nav flex-column px-3 mt-3">
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.asignaciones.index') }}"><i class="bi bi-people-fill me-2"></i>Asignar Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.estudiantes.index') }}"><i class="bi bi-person-vcard me-2"></i>Gestión Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.responsables.index') }}"><i class="bi bi-person-badge me-2"></i>Gestión Responsables</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.administradores.index') }}"><i class="bi bi-person-gear me-2"></i>Gestión Administradores</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('admin.vinculacion.index') }}"><i class="bi bi-link-45deg me-2"></i>Gestión Personal de Vinculación</a></li>
                        {{-- Otros enlaces --}}
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

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Personal de Vinculación</h1>
                    <button class="btn btn-outline-secondary btn-sm" id="btn-refresh">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-table me-2"></i>Lista de Personal de Vinculación</span>
                        <button class="btn btn-light btn-sm" id="btnAgregarVinculacion">
                            <i class="bi bi-plus-lg me-1"></i> Agregar
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaVinculacion" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th><th>Apellido Paterno</th><th>Apellido Materno</th>
                                        <th>Correo</th><th>Teléfono</th><th>Estado</th><th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalVinculacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVinculacionLabel">Agregar Personal de Vinculación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formVinculacion" novalidate>
                        <input type="hidden" id="vinculacion_id">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre(s)*</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="apellido_paterno" class="form-label">Apellido Paterno*</label>
                                <input type="text" class="form-control" id="apellido_paterno" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" id="apellido_materno">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico*</label>
                            <input type="email" class="form-control" id="correo" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena">
                            <small class="text-muted" id="passwordHelp">Dejar en blanco para no cambiar.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarVinculacion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/adminjs/gestion_vinculacion.js') }}"></script>
</body>
</html>