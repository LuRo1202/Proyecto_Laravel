<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    /**
     * Muestra la vista principal de gestión de estudiantes.
     */
    public function index()
    {
        return view('admin.gestion_estudiantes');
    }

    /**
     * Proporciona la lista de todos los estudiantes para DataTables.
     */
    public function listar()
    {
        $estudiantes = DB::table('estudiantes as e')
            ->leftJoin('usuarios as u', 'e.usuario_id', '=', 'u.usuario_id')
            ->select(
                'e.estudiante_id',
                'e.matricula',
                'e.nombre',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.carrera',
                'e.cuatrimestre',
                'e.horas_completadas',
                'e.horas_requeridas',
                'e.activo',
                'u.correo'
            )
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.nombre')
            ->get();

        return response()->json(['data' => $estudiantes]);
    }

    /**
     * Obtiene los detalles de un estudiante específico para el modal de edición.
     */
    public function obtener($id)
    {
        $estudiante = DB::table('estudiantes as e')
            ->join('usuarios as u', 'e.usuario_id', '=', 'u.usuario_id')
            ->where('e.estudiante_id', $id)
            ->select('e.*', 'u.correo')
            ->first();

        if ($estudiante) {
            return response()->json(['success' => true, 'data' => $estudiante]);
        }
        return response()->json(['success' => false, 'message' => 'Estudiante no encontrado.'], 404);
    }

    /**
     * Agrega un nuevo estudiante y su usuario asociado.
     */
    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'matricula' => 'required|string|max:255|unique:estudiantes,matricula',
            'correo' => 'required|email|max:255|unique:usuarios,correo',
            'contrasena' => 'required|string|min:6',
            'carrera' => 'required|string',
            'cuatrimestre' => 'required|integer',
            'telefono' => 'nullable|string|max:15',
            'activo' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $usuarioId = DB::table('usuarios')->insertGetId([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'rol_id' => 3, // Asumimos que el rol de estudiante es 3
                'tipo_usuario' => 'estudiante',
                'activo' => $validated['activo'],
            ]);

            DB::table('estudiantes')->insert([
                'usuario_id' => $usuarioId,
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'matricula' => $validated['matricula'],
                'carrera' => $validated['carrera'],
                'cuatrimestre' => $validated['cuatrimestre'],
                'telefono' => $validated['telefono'],
                'activo' => $validated['activo'],
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Estudiante agregado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al agregar estudiante: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edita un estudiante existente. (MÉTODO CORREGIDO)
     */
    public function editar(Request $request, $id)
    {
        $estudiante = DB::table('estudiantes')->where('estudiante_id', $id)->first();
        if (!$estudiante) {
            return response()->json(['success' => false, 'message' => 'Estudiante no encontrado.'], 404);
        }

        // Validación completa de todos los campos del formulario de edición
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'matricula' => ['required', 'string', 'max:255', Rule::unique('estudiantes')->ignore($id, 'estudiante_id')],
            'correo' => ['required', 'email', 'max:255', Rule::unique('usuarios')->ignore($estudiante->usuario_id, 'usuario_id')],
            'contrasena' => 'nullable|string|min:6',
            'carrera' => 'required|string',
            'cuatrimestre' => 'required|integer',
            'telefono' => 'nullable|string|max:15',
            'activo' => 'required|boolean',
        ]);
        
        DB::beginTransaction();
        try {
            // Actualizar la tabla 'estudiantes'
            DB::table('estudiantes')->where('estudiante_id', $id)->update([
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'matricula' => $validated['matricula'],
                'carrera' => $validated['carrera'],
                'cuatrimestre' => $validated['cuatrimestre'],
                'telefono' => $validated['telefono'],
                'activo' => $validated['activo'],
            ]);

            // Preparar datos para actualizar la tabla 'usuarios'
            $userData = [
                'correo' => $validated['correo'],
                'activo' => $validated['activo']
            ];
            // Si se proporcionó una nueva contraseña, la hasheamos y la añadimos
            if (!empty($validated['contrasena'])) {
                $userData['contrasena'] = Hash::make($validated['contrasena']);
            }
            DB::table('usuarios')->where('usuario_id', $estudiante->usuario_id)->update($userData);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Estudiante actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el estudiante: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un estudiante y su usuario asociado, junto con sus registros dependientes.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate(['estudiante_id' => 'required|integer|exists:estudiantes,estudiante_id']);
        
        DB::beginTransaction();
        try {
            $estudiante = DB::table('estudiantes')->where('estudiante_id', $validated['estudiante_id'])->first();
            
            DB::table('registroshoras')->where('estudiante_id', $estudiante->estudiante_id)->delete();
            DB::table('estudiantes_responsables')->where('estudiante_id', $estudiante->estudiante_id)->delete();
            DB::table('solicitudes')->where('estudiante_id', $estudiante->estudiante_id)->delete();

            DB::table('estudiantes')->where('estudiante_id', $estudiante->estudiante_id)->delete();
            DB::table('usuarios')->where('usuario_id', $estudiante->usuario_id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Estudiante y toda su información asociada han sido eliminados.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el estudiante: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtiene los registros de horas de un estudiante específico.
     */
    public function obtenerRegistros($id)
    {
        $registros = DB::table('registroshoras as rh')
            ->leftJoin('responsables as r', 'rh.responsable_id', '=', 'r.responsable_id')
            ->where('rh.estudiante_id', $id)
            ->select(
                'rh.fecha', 
                DB::raw("TIME_FORMAT(rh.hora_entrada, '%H:%i') as hora_entrada"),
                DB::raw("TIME_FORMAT(rh.hora_salida, '%H:%i') as hora_salida"),
                'rh.horas_acumuladas', 
                'rh.estado',
                DB::raw("CONCAT_WS(' ', r.nombre, r.apellido_paterno) as responsable")
            )
            ->orderBy('rh.fecha', 'desc')
            ->orderBy('rh.hora_entrada', 'desc')
            ->get();
            
        return response()->json(['success' => true, 'data' => $registros]);
    }

    /**
     * Cambia el estado (activo/inactivo) de un estudiante y su usuario.
     */
    public function cambiarEstado(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,estudiante_id',
            'activo' => 'required|boolean',
        ]);

        $estudiante = DB::table('estudiantes')->where('estudiante_id', $validated['estudiante_id'])->first();

        DB::table('estudiantes')->where('estudiante_id', $validated['estudiante_id'])->update(['activo' => $validated['activo']]);
        DB::table('usuarios')->where('usuario_id', $estudiante->usuario_id)->update(['activo' => $validated['activo']]);

        return response()->json(['success' => true, 'message' => 'Estado del estudiante actualizado correctamente.']);
    }
}