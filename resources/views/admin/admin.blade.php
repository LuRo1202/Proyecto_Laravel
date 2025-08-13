<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Panel de Administración - Servicio Social</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
  <style>
    :root {
      --sidebar-bg: #2c3e50;
      --sidebar-link-color: rgba(255, 255, 255, 0.8);
      --sidebar-link-hover-active: #ffffff;
      --sidebar-link-bg-hover-active: rgba(255, 255, 255, 0.1);
      --sidebar-active-border: #8856dd;
      --sidebar-active-bg: #8856dd;
    }
    body { background-color: #f8f9fa; font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #343a40; }
    .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .sidebar .nav-link { color: var(--sidebar-link-color) !important; padding: 0.75rem 1rem; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; }
    .sidebar .nav-link:hover { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-link-bg-hover-active); }
    .sidebar .nav-link.active { color: var(--sidebar-link-hover-active) !important; background-color: var(--sidebar-active-bg); border-left: 5px solid var(--sidebar-active-border); padding-left: calc(1rem - 5px); }
    .sidebar .nav-link.text-danger:hover { color: #ffc107 !important; }

    /* --- ESTILOS CORREGIDOS PARA EL PERFIL DE USUARIO --- */
    .user-info {
        padding: 1rem;
        color: #ffffff;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .user-info-name {
        font-weight: 600;
        font-size: 1.05rem; /* Ligeramente más grande */
        margin-bottom: 0.5rem; /* Espacio entre nombre y rol */
        line-height: 1.4;
    }
    .user-info-role {
        background-color: rgba(255,255,255,0.2);
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        border-radius: 50px; /* Estilo píldora */
    }
    /* --- FIN DE ESTILOS CORREGIDOS --- */

    .card-summary { border: none; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; color: white; }
    .card-summary .card-body { padding: 1.5rem; }
    .card-summary .card-icon { font-size: 2.5rem; opacity: 0.8; }
    .card-summary h2 { font-size: 2.2rem; font-weight: 700; }
    .card-summary h5 { font-size: 1.1rem; }
    .bg-warning { color: #212529 !important; }
    .card-chart { border: none; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .card-chart .card-header { font-weight: 600; background-color: #f1f2f4; border-bottom: 1px solid #dee2e6; color: #343a40; padding: 1rem 1.5rem; }
    #carreraChart { max-height: 300px; width: 100%; }
    .table-responsive { border-radius: 12px; overflow: hidden; }
    .table-sm th, .table-sm td { padding: 0.7rem; }
    .progress-thin { height: 20px; background-color: #e9ecef; border-radius: 5px; }
    .progress-bar { background-color: #198754; transition: width 0.6s ease; font-size: 0.85rem; color: white; display: flex; align-items: center; justify-content: center; }
    .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
  </style>
</head>
<body>
  <div class="loading-overlay">
    <div class="spinner-border text-light" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <img src="{{ asset('imagenes/data.png') }}" class="img-fluid" style="max-height: 90px;" alt="Logo"/>
            <h5 class="text-white mt-2">Servicio Social</h5>
          </div>

          <div class="user-info">
            <div class="user-info-name">
                @if(Auth::user()->administrador)
                    {{-- Concatenamos el nombre completo --}}
                    {{ Auth::user()->administrador->nombre }} {{ Auth::user()->administrador->apellido_paterno }} {{ Auth::user()->administrador->apellido_materno }}
                @else
                    {{ Auth::user()->correo }}
                @endif
            </div>
            <span class="user-info-role">Administrador</span>
          </div>
          <ul class="nav flex-column px-3 mt-3">
            <li class="nav-item"><a class="nav-link active" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.asignaciones.index') }}"><i class="bi bi-people-fill me-2"></i>Asignar Estudiantes</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.estudiantes.index') }}"><i class="bi bi-person-vcard me-2"></i>Gestión Estudiantes</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.responsables.index') }}"><i class="bi bi-person-badge me-2"></i>Gestión Responsables</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.administradores.index') }}"><i class="bi bi-person-gear me-2"></i>Gestión Administradores</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.vinculacion.index') }}"><i class="bi bi-person-circle me-2"></i>Gestión Personal de Vinculación</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clock-history me-2"></i>Registro Horas</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-2"></i>Reportes</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-event me-2"></i>Gestionar Períodos</a></li>
            <li class="nav-item mt-4"><a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i>Mi Perfil</a></li>
             <li class="nav-item">
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
          <h1 class="h2">Panel de Administración</h1>
          <button class="btn btn-outline-secondary btn-sm" id="btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
          </button>
        </div>

        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card bg-primary card-summary h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div><h5>Estudiantes</h5><h2 id="total-estudiantes">0</h2></div>
                <i class="bi bi-people card-icon"></i>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-success card-summary h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div><h5>Responsables</h5><h2 id="total-responsables">0</h2></div>
                <i class="bi bi-person-badge card-icon"></i>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-warning card-summary h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div><h5>Horas Pendientes</h5><h2 id="horas-pendientes">0.00</h2></div>
                <i class="bi bi-clock-history card-icon"></i>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-danger card-summary h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div><h5>Asignaciones</h5><h2 id="total-asignaciones">0</h2></div>
                <i class="bi bi-diagram-2 card-icon"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="card mb-4 card-chart">
              <div class="card-header">Estudiantes por Carrera</div>
              <div class="card-body"><canvas id="carreraChart"></canvas></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card mb-4">
              <div class="card-header">Estudiantes con más horas pendientes</div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover table-sm">
                    <thead><tr><th>Estudiante</th><th>Horas completadas</th><th>Progreso</th></tr></thead>
                    <tbody id="estudiantes-table-body"><tr><td colspan="3" class="text-center">Cargando datos...</td></tr></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/adminjs/admin.js') }}"></script>
</body>
</html>