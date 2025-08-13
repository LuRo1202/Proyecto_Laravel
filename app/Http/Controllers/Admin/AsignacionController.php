<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsignacionController extends Controller
{
    /**
     * Muestra la vista principal de asignación de estudiantes.
     */
    public function index()
    {
        return view('admin.asignacion_estudiantes');
    }

    /**
     * Obtiene la lista de estudiantes que aún no tienen un responsable asignado.
     */
    public function obtenerEstudiantesSinAsignar()
    {
        $estudiantes = DB::table('estudiantes')
            ->select('estudiante_id', 'matricula', DB::raw("CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo"))
            ->where('activo', 1)
            ->whereNotIn('estudiante_id', function ($query) {
                $query->select('estudiante_id')->from('estudiantes_responsables');
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $estudiantes]);
    }

    /**
     * Obtiene la lista de responsables activos.
     */
    public function obtenerResponsablesDisponibles()
    {
        $responsables = DB::table('responsables')
            ->select('responsable_id', DB::raw("CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo"), 'cargo')
            ->where('activo', 1)
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $responsables]);
    }

    /**
     * Obtiene la lista de todas las asignaciones existentes.
     */
    public function obtenerAsignaciones()
    {
        $asignaciones = DB::table('estudiantes_responsables as er')
            ->join('estudiantes as e', 'er.estudiante_id', '=', 'e.estudiante_id')
            ->join('responsables as r', 'er.responsable_id', '=', 'r.responsable_id')
            ->select(
                'er.id as asignacion_id',
                DB::raw("CONCAT(e.nombre, ' ', e.apellido_paterno) as estudiante"),
                DB::raw("CONCAT(r.nombre, ' ', r.apellido_paterno) as responsable"),
                'er.fecha_asignacion'
            )
            ->orderBy('er.fecha_asignacion', 'desc')
            ->get();

        return response()->json(['data' => $asignaciones]);
    }

    /**
     * Crea una nueva asignación de estudiante a responsable.
     */
    public function asignar(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,estudiante_id',
            'responsable_id' => 'required|integer|exists:responsables,responsable_id',
        ]);

        // Verificar si ya existe la asignación para evitar conflictos
        $existente = DB::table('estudiantes_responsables')
            ->where('estudiante_id', $validated['estudiante_id'])
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Este estudiante ya tiene una asignación activa.'
            ], 409); // 409 Conflict
        }

        $asignacionId = DB::table('estudiantes_responsables')->insertGetId([
            'estudiante_id' => $validated['estudiante_id'],
            'responsable_id' => $validated['responsable_id'],
            'fecha_asignacion' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asignación creada correctamente.',
            'asignacion_id' => $asignacionId
        ]);
    }

    /**
     * Elimina una asignación existente.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate([
            'asignacion_id' => 'required|integer|exists:estudiantes_responsables,id',
        ]);

        $deleted = DB::table('estudiantes_responsables')->where('id', $validated['asignacion_id'])->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Asignación eliminada correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'No se encontró la asignación o ya fue eliminada.'], 404);
    }
}