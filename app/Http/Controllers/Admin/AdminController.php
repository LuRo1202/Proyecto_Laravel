<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ¡Importante! Para hacer consultas a la base de datos

class AdminController extends Controller
{
    /**
     * Muestra el panel de administración principal.
     */
    public function index()
    {
        // Simplemente devuelve la vista. Laravel se encarga de que solo
        // los usuarios autenticados y con el rol correcto lleguen aquí.
        return view('admin.admin'); // Asumiendo que tu vista se llama admin.blade.php
    }

    /**
     * Proporciona los datos para el dashboard vía AJAX.
     * Esta es la nueva versión de tu antiguo 'dashboard_data.php'.
     */
    public function dashboardData()
    {
        try {
            // Usamos el constructor de consultas de Laravel para más seguridad y legibilidad
            $totalEstudiantes = DB::table('estudiantes')->where('activo', 1)->count();
            $totalResponsables = DB::table('responsables')->where('activo', 1)->count();
            $horasPendientes = DB::table('registroshoras')->where('estado', 'pendiente')->sum('horas_acumuladas');
            $totalAsignaciones = DB::table('estudiantes_responsables')->count();

            $estudiantesPorCarrera = DB::table('estudiantes')
                ->select('carrera', DB::raw('count(*) as cantidad'))
                ->where('activo', 1)
                ->whereNotNull('carrera') // Evita agrupar nulos
                ->groupBy('carrera')
                ->orderBy('cantidad', 'desc')
                ->get();

            $estudiantesHorasPendientes = DB::table('estudiantes')
                ->select(
                    'nombre',
                    'apellido_paterno',
                    'apellido_materno',
                    'horas_completadas',
                    'horas_requeridas'
                )
                ->where('activo', 1)
                ->orderByRaw('(horas_requeridas - horas_completadas) DESC')
                ->limit(5)
                ->get();

            // Devolvemos una respuesta JSON estandarizada
            return response()->json([
                'success' => true,
                'data' => [
                    'totalEstudiantes' => $totalEstudiantes,
                    'totalResponsables' => $totalResponsables,
                    'horasPendientes' => $horasPendientes ?? 0,
                    'totalAsignaciones' => $totalAsignaciones,
                    'estudiantesPorCarrera' => $estudiantesPorCarrera,
                    'estudiantesHorasPendientes' => $estudiantesHorasPendientes,
                ]
            ]);

        } catch (\Exception $e) {
            // En caso de error, devolvemos un JSON con el mensaje
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del dashboard: ' . $e->getMessage()
            ], 500); // Código de error 500: Internal Server Error
        }
    }
}