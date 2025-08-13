<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AsignacionController;
use App\Http\Controllers\Admin\EstudianteController; 
use App\Http\Controllers\Admin\ResponsableController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\VinculacionController;
use App\Http\Controllers\Admin\RegistroHorasController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta raíz que muestra el login
Route::get('/', function () {
    return view('auth.login');
});

// Rutas de autenticación (login/logout)
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Ruta pública para verificar si hay un período de registro activo
Route::get('/verificar-periodo-activo', [PeriodoController::class, 'verificarPeriodoActivo'])
    ->name('verificar.periodo.activo');

// Rutas protegidas que requieren inicio de sesión
Route::middleware(['auth'])->group(function () {
    
    // Redirección automática después del login según el rol del usuario
    Route::get('/home', function () {
        $user = auth()->user();
        
        // Verifica si el usuario tiene un rol asignado
        if (!$user->rol) {
            return redirect()->route('login')->with('error', 'No tienes un rol asignado');
        }
        
        return match($user->rol->nombre_rol) {
            'admin' => redirect()->route('admin.dashboard'),
            'encargado' => redirect()->route('responsable.dashboard'),
            'estudiante' => redirect()->route('estudiante.dashboard'),
            'vinculacion' => redirect()->route('vinculacion.dashboard'),
            default => redirect()->route('login')->with('error', 'Rol no reconocido'),
        };
    })->name('home');

        // 'middleware' protege estas rutas para que solo admins puedan entrar
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // Esta será la URL que tu JS llamará para obtener los datos
        Route::get('/dashboard-data', [AdminController::class, 'dashboardData'])->name('dashboard.data');

        Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
            Route::get('/', [AsignacionController::class, 'index'])->name('index');
            Route::get('/sin-asignar', [AsignacionController::class, 'obtenerEstudiantesSinAsignar'])->name('estudiantes.sin_asignar');
            Route::get('/responsables', [AsignacionController::class, 'obtenerResponsablesDisponibles'])->name('responsables.disponibles');
            Route::get('/lista', [AsignacionController::class, 'obtenerAsignaciones'])->name('lista');
            Route::post('/asignar', [AsignacionController::class, 'asignar'])->name('asignar');
            Route::post('/eliminar', [AsignacionController::class, 'eliminar'])->name('eliminar'); // Usamos POST para la eliminación por simplicidad con AJAX
        });
        Route::prefix('estudiantes')->name('estudiantes.')->group(function () {
            Route::get('/', [EstudianteController::class, 'index'])->name('index');
            Route::get('/listar', [EstudianteController::class, 'listar'])->name('listar');
            Route::get('/{id}', [EstudianteController::class, 'obtener'])->name('obtener'); // Para obtener un estudiante
            Route::post('/agregar', [EstudianteController::class, 'agregar'])->name('agregar');
            Route::post('/editar/{id}', [EstudianteController::class, 'editar'])->name('editar'); // Usamos POST para simplificar AJAX
            Route::post('/eliminar', [EstudianteController::class, 'eliminar'])->name('eliminar');
            Route::get('/{id}/registros', [EstudianteController::class, 'obtenerRegistros'])->name('registros');
            Route::post('/cambiar-estado', [EstudianteController::class, 'cambiarEstado'])->name('cambiar_estado');
        });

            Route::prefix('responsables')->name('responsables.')->group(function () {
                Route::get('/', [ResponsableController::class, 'index'])->name('index');
                Route::get('/listar', [ResponsableController::class, 'listar'])->name('listar');
                Route::get('/{id}', [ResponsableController::class, 'obtener'])->name('obtener');
                Route::post('/agregar', [ResponsableController::class, 'agregar'])->name('agregar');
                Route::post('/editar/{id}', [ResponsableController::class, 'editar'])->name('editar');
                Route::post('/eliminar', [ResponsableController::class, 'eliminar'])->name('eliminar');
                Route::post('/cambiar-estado', [ResponsableController::class, 'cambiarEstado'])->name('cambiar_estado');
        });

            Route::prefix('gestion-administradores')->name('administradores.')->group(function () {
                Route::get('/', [AdminManagementController::class, 'index'])->name('index');
                Route::get('/listar', [AdminManagementController::class, 'listar'])->name('listar');
                Route::get('/{id}', [AdminManagementController::class, 'obtener'])->name('obtener');
                Route::post('/agregar', [AdminManagementController::class, 'agregar'])->name('agregar');
                Route::post('/editar/{id}', [AdminManagementController::class, 'editar'])->name('editar');
                Route::post('/eliminar', [AdminManagementController::class, 'eliminar'])->name('eliminar');
                Route::post('/cambiar-estado', [AdminManagementController::class, 'cambiarEstado'])->name('cambiar_estado');
        });
            Route::prefix('gestion-vinculacion')->name('vinculacion.')->group(function () {
                Route::get('/', [VinculacionController::class, 'index'])->name('index');
                Route::get('/listar', [VinculacionController::class, 'listar'])->name('listar');
                Route::get('/{id}', [VinculacionController::class, 'obtener'])->name('obtener');
                Route::post('/agregar', [VinculacionController::class, 'agregar'])->name('agregar');
                Route::post('/editar/{id}', [VinculacionController::class, 'editar'])->name('editar');
                Route::post('/eliminar', [VinculacionController::class, 'eliminar'])->name('eliminar');
                Route::post('/cambiar-estado', [VinculacionController::class, 'cambiarEstado'])->name('cambiar_estado');
        });

            Route::prefix('registro-horas')->name('registro_horas.')->group(function () {
                Route::get('/', [RegistroHorasController::class, 'index'])->name('index');
                Route::get('/listar-progreso', [RegistroHorasController::class, 'listarEstudiantesProgreso'])->name('listar_progreso');
                Route::get('/detalle-estudiante/{id}', [RegistroHorasController::class, 'obtenerDetalleEstudiante'])->name('detalle_estudiante');
                Route::get('/listar-responsables', [RegistroHorasController::class, 'listarResponsables'])->name('listar_responsables');
                Route::get('/obtener-registro/{id}', [RegistroHorasController::class, 'obtenerRegistro'])->name('obtener_registro');
                Route::post('/agregar-registro', [RegistroHorasController::class, 'agregarRegistro'])->name('agregar_registro');
                Route::post('/editar-registro/{id}', [RegistroHorasController::class, 'editarRegistro'])->name('editar_registro');
                Route::post('/eliminar-registro', [RegistroHorasController::class, 'eliminarRegistro'])->name('eliminar_registro');
                Route::post('/liberar-servicio', [RegistroHorasController::class, 'liberarServicio'])->name('liberar_servicio');
        });





    });



});