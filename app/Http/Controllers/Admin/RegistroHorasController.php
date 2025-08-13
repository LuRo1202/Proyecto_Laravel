<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroHorasController extends Controller
{
    /**
     * Muestra la vista principal de registro de horas.
     */
    public function index()
    {
        return view('admin.registro_horas');
    }

    /**
     * Lista el progreso de todos los estudiantes activos para la tabla principal.
     */
    public function listarEstudiantesProgreso()
    {
        $estudiantes = DB::table('estudiantes as e')
            ->where('e.activo', 1)
            ->select(
                'e.estudiante_id', 'e.matricula', 'e.nombre', 
                'e.apellido_paterno', 'e.apellido_materno', 'e.carrera', 
                'e.horas_completadas', 'e.horas_requeridas'
            )
            ->orderBy('e.apellido_paterno')->orderBy('e.nombre')
            ->get();

        return response()->json(['data' => $estudiantes]);
    }

    /**
     * Obtiene los detalles y registros de un estudiante específico para el modal.
     */
    public function obtenerDetalleEstudiante($id)
    {
        $estudiante = DB::table('estudiantes')->where('estudiante_id', $id)->first();
        if (!$estudiante) {
            return response()->json(['success' => false, 'message' => 'Estudiante no encontrado'], 404);
        }

        $registros = DB::table('registroshoras as r')
            ->leftJoin('responsables as res', 'r.responsable_id', '=', 'res.responsable_id')
            ->where('r.estudiante_id', $id)
            ->select('r.*', DB::raw("CONCAT_WS(' ', res.nombre, res.apellido_paterno) AS responsable_nombre"))
            ->orderBy('r.fecha', 'desc')->orderBy('r.hora_entrada', 'desc')
            ->get();
        
        return response()->json(['success' => true, 'data' => ['estudiante' => $estudiante, 'registros' => $registros]]);
    }

    /**
     * Obtiene la lista de responsables activos para el formulario.
     */
    public function listarResponsables()
    {
        $responsables = DB::table('responsables')
            ->where('activo', 1)
            ->select('responsable_id', DB::raw("CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre_completo"))
            ->orderBy('apellido_paterno')->orderBy('nombre')
            ->get();
        return response()->json(['success' => true, 'data' => $responsables]);
    }

    /**
     * Obtiene un registro de hora específico para edición.
     */
    public function obtenerRegistro($id)
    {
        $registro = DB::table('registroshoras')
            ->where('registro_id', $id)
            ->select(
                'registro_id', 'estudiante_id', 'responsable_id', 'fecha',
                DB::raw("TIME(hora_entrada) as hora_entrada"),
                DB::raw("TIME(hora_salida) as hora_salida"),
                'horas_acumuladas', 'estado', 'observaciones'
            )->first();

        return $registro
            ? response()->json(['success' => true, 'data' => $registro])
            : response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
    }

    /**
     * Agrega un nuevo registro de horas para un estudiante.
     */
    public function agregarRegistro(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,estudiante_id',
            'responsable_id' => 'required|integer|exists:responsables,responsable_id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            'horas_acumuladas' => 'required|numeric|min:0',
            'estado' => 'required|in:pendiente,aprobado,rechazado',
            'observaciones' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            DB::table('registroshoras')->insert([
                'estudiante_id' => $validated['estudiante_id'],
                'responsable_id' => $validated['responsable_id'],
                'fecha' => $validated['fecha'],
                'hora_entrada' => $validated['fecha'] . ' ' . $validated['hora_entrada'],
                'hora_salida' => $validated['fecha'] . ' ' . $validated['hora_salida'],
                'horas_acumuladas' => $validated['horas_acumuladas'],
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'],
            ]);
            
            $this->recalcularHoras($validated['estudiante_id']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro agregado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al agregar el registro: '.$e->getMessage()], 500);
        }
    }

    /**
     * Edita un registro de horas existente.
     */
    public function editarRegistro(Request $request, $id)
    {
        $validated = $request->validate([
            'responsable_id' => 'required|integer|exists:responsables,responsable_id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            'horas_acumuladas' => 'required|numeric|min:0',
            'estado' => 'required|in:pendiente,aprobado,rechazado',
            'observaciones' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $registro = DB::table('registroshoras')->where('registro_id', $id)->first();
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            DB::table('registroshoras')->where('registro_id', $id)->update([
                'responsable_id' => $validated['responsable_id'],
                'fecha' => $validated['fecha'],
                'hora_entrada' => $validated['fecha'] . ' ' . $validated['hora_entrada'],
                'hora_salida' => $validated['fecha'] . ' ' . $validated['hora_salida'],
                'horas_acumuladas' => $validated['horas_acumuladas'],
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'],
            ]);
            
            $this->recalcularHoras($registro->estudiante_id);
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro: '.$e->getMessage()], 500);
        }
    }

    /**
     * Elimina un registro de horas.
     */
    public function eliminarRegistro(Request $request)
    {
        $validated = $request->validate(['registro_id' => 'required|integer|exists:registroshoras,registro_id']);
        
        DB::beginTransaction();
        try {
            $registro = DB::table('registroshoras')->where('registro_id', $validated['registro_id'])->first();
            DB::table('registroshoras')->where('registro_id', $validated['registro_id'])->delete();
            $this->recalcularHoras($registro->estudiante_id);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marca el servicio social de un estudiante como liberado.
     */
    public function liberarServicio(Request $request)
    {
        $validated = $request->validate(['estudiante_id' => 'required|integer|exists:estudiantes,estudiante_id']);
        DB::table('estudiantes')
            ->where('estudiante_id', $validated['estudiante_id'])
            ->update(['horas_completadas' => DB::raw('horas_requeridas')]);

        return response()->json(['success' => true, 'message' => 'Servicio social liberado exitosamente.']);
    }

    /**
     * Función privada para recalcular las horas totales de un estudiante.
     */
    private function recalcularHoras($estudiante_id)
    {
        $totalHoras = DB::table('registroshoras')
            ->where('estudiante_id', $estudiante_id)
            ->where('estado', 'aprobado')
            ->sum('horas_acumuladas');

        DB::table('estudiantes')
            ->where('estudiante_id', $estudiante_id)
            ->update(['horas_completadas' => $totalHoras]);
    }
}