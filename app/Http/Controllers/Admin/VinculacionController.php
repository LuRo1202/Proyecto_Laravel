<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VinculacionController extends Controller
{
    /**
     * Muestra la vista principal de gestión de personal de vinculación.
     */
    public function index()
    {
        return view('admin.gestion_vinculacion');
    }

    /**
     * Proporciona la lista del personal de vinculación para DataTables.
     */
    public function listar()
    {
        $personal = DB::table('vinculacion as v')
            ->join('usuarios as u', 'v.usuario_id', '=', 'u.usuario_id')
            ->select(
                'v.vinculacion_id',
                'v.nombre',
                'v.apellido_paterno',
                'v.apellido_materno',
                'v.telefono',
                'v.activo',
                'u.correo'
            )
            ->orderBy('v.apellido_paterno')
            ->orderBy('v.nombre')
            ->get();

        return response()->json(['data' => $personal]);
    }

    /**
     * Obtiene los detalles de un miembro del personal por su ID.
     */
    public function obtener($id)
    {
        $personal = DB::table('vinculacion as v')
            ->join('usuarios as u', 'v.usuario_id', '=', 'u.usuario_id')
            ->where('v.vinculacion_id', $id)
            ->select('v.*', 'u.correo')
            ->first();

        if ($personal) {
            return response()->json(['success' => true, 'data' => $personal]);
        }
        return response()->json(['success' => false, 'message' => 'Personal no encontrado.'], 404);
    }

    /**
     * Agrega un nuevo miembro del personal de vinculación.
     */
    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $usuarioId = DB::table('usuarios')->insertGetId([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'rol_id' => 4, // Rol de vinculación
                'tipo_usuario' => 'vinculacion',
                'activo' => 1,
            ]);

            DB::table('vinculacion')->insert([
                'usuario_id' => $usuarioId,
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
                'activo' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Personal de vinculación agregado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al agregar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edita un miembro del personal existente.
     */
    public function editar(Request $request, $id)
    {
        $personal = DB::table('vinculacion')->where('vinculacion_id', $id)->first();
        if (!$personal) {
            return response()->json(['success' => false, 'message' => 'Personal no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'correo' => ['required', 'email', Rule::unique('usuarios')->ignore($personal->usuario_id, 'usuario_id')],
            'contrasena' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            DB::table('vinculacion')->where('vinculacion_id', $id)->update([
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
            ]);

            $userData = ['correo' => $validated['correo']];
            if (!empty($validated['contrasena'])) {
                $userData['contrasena'] = Hash::make($validated['contrasena']);
            }
            DB::table('usuarios')->where('usuario_id', $personal->usuario_id)->update($userData);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Personal actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina a un miembro del personal de vinculación.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate(['vinculacion_id' => 'required|integer|exists:vinculacion,vinculacion_id']);
        $personal = DB::table('vinculacion')->where('vinculacion_id', $validated['vinculacion_id'])->first();

        DB::beginTransaction();
        try {
            DB::table('vinculacion')->where('vinculacion_id', $personal->vinculacion_id)->delete();
            DB::table('usuarios')->where('usuario_id', $personal->usuario_id)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Personal eliminado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un miembro del personal.
     */
    public function cambiarEstado(Request $request)
    {
        $validated = $request->validate([
            'vinculacion_id' => 'required|integer|exists:vinculacion,vinculacion_id',
            'activo' => 'required|boolean',
        ]);

        $personal = DB::table('vinculacion')->where('vinculacion_id', $validated['vinculacion_id'])->first();

        DB::table('vinculacion')->where('vinculacion_id', $validated['vinculacion_id'])->update(['activo' => $validated['activo']]);
        DB::table('usuarios')->where('usuario_id', $personal->usuario_id)->update(['activo' => $validated['activo']]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }
}