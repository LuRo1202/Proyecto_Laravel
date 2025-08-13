<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Horas por Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
        .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg); border-left: 5px solid var(--sidebar-active-border); padding-left: calc(1rem - 5px); }
        .user-info { padding: 1rem; color: #ffffff; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-info-name { font-weight: 600; font-size: 1.05rem; margin-bottom: 0.5rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info-role { background-color: rgba(255,255,255,0.2); font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 50px; }
        .card-header { background-color: var(--primary-dark); color: white; font-weight: 600; }
        .table-hover > tbody > tr:hover { background-color: var(--primary-light) !important; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--primary-dark) !important; color: white !important; border-color: var(--primary-dark) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--primary-light) !important; color: #333 !important; border-color: var(--primary-light) !important; }
        .progress-bar { background-color: var(--primary-dark); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4"><img src="{{ asset('imagenes/data.png') }}" alt="Logo" class="img-fluid" style="max-height: 90px;"><h5 class="text-white mt-2">Servicio Social</h5></div>
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
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.vinculacion.index') }}"><i class="bi bi-person-circle me-2"></i>Gestión Personal de Vinculación</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('admin.registro_horas.index') }}"><i class="bi bi-clock-history me-2"></i>Registro Horas</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
                        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
                        <li class="nav-item">
                           <a class="nav-link text-danger" href="#" id="btn-logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a>
                           <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                       </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Registro y Gestión de Horas</h1>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                       <span><i class="bi bi-table me-2"></i>Progreso de Estudiantes</span>
                       <button class="btn btn-light btn-sm" id="btnActualizarTabla">
                           <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                       </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaEstudiantesHoras" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Matrícula</th><th>Nombre</th><th>Ap. Paterno</th><th>Ap. Materno</th><th>Carrera</th><th>Horas / Progreso</th><th>Estado</th><th>Acciones</th>
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

    <div class="modal fade" id="modalGestionEstudiante" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nombreEstudianteModal"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6" id="progresoContainer"></div>
                        <div class="col-md-6 text-end">
                            <button id="btnAgregarRegistro" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Registro</button>
                            <button id="btnLiberarServicio" class="btn btn-primary"><i class="bi bi-check-circle"></i> Liberar Servicio</button>
                        </div>
                    </div>
                    <hr>
                    <h6>Historial de Registros:</h6>
                    <div class="table-responsive">
                        <table id="tablaDetalleHoras" class="table table-sm table-bordered table-hover" style="width:100%">
                            <thead><tr><th>Fecha</th><th>Horas</th><th>Estado</th><th>Responsable</th><th>Acciones</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAgregarEditarRegistro" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarEditarLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formRegistroHoras" novalidate>
                        <input type="hidden" id="registro_id"><input type="hidden" id="estudiante_id_form">
                        <div class="mb-3"><label for="responsable_id_form" class="form-label">Responsable*</label><select class="form-select" id="responsable_id_form" required></select></div>
                        <div class="mb-3"><label for="fecha_form" class="form-label">Fecha*</label><input type="date" class="form-control" id="fecha_form" required></div>
                        <div class="row"><div class="col-md-6 mb-3"><label for="hora_entrada_form" class="form-label">Hora de Entrada*</label><input type="time" class="form-control" id="hora_entrada_form" required></div><div class="col-md-6 mb-3"><label for="hora_salida_form" class="form-label">Hora de Salida*</label><input type="time" class="form-control" id="hora_salida_form" required></div></div>
                        <div class="mb-3"><label for="horas_acumuladas_form" class="form-label">Horas Acumuladas</label><input type="number" step="0.01" class="form-control" id="horas_acumuladas_form" readonly></div>
                        <div class="mb-3"><label for="estado_form" class="form-label">Estado*</label><select class="form-select" id="estado_form" required><option value="pendiente">Pendiente</option><option value="aprobado">Aprobado</option><option value="rechazado">Rechazado</option></select></div>
                        <div class="mb-3"><label for="observaciones_form" class="form-label">Observaciones</label><textarea class="form-control" id="observaciones_form" rows="2" maxlength="500"></textarea></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarRegistroHoras">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/adminjs/registro_horas.js') }}"></script>
</body>
</html>