<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeriodoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Las rutas aquí son cargadas por RouteServiceProvider dentro del grupo "api".
| Todas las rutas tendrán el prefijo /api automáticamente.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tu ruta personalizada
Route::get('/verificar-periodo', [PeriodoController::class, 'verificarPeriodoActivo']);