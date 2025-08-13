<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * Muestra la vista principal de gestión de administradores.
     */
    public function index()
    {
        return view('admin.gestion_administradores');
    }

    /**
     * Proporciona la lista de todos los administradores para DataTables.
     */
    public function listar()
    {
        $admins = DB::table('administradores as a')
            ->join('usuarios as u', 'a.usuario_id', '=', 'u.usuario_id')
            ->select(
                'a.admin_id',
                'a.nombre',
                'a.apellido_paterno',
                'a.apellido_materno',
                'u.correo',
                'u.ultimo_login',
                'a.activo'
            )
            ->orderBy('a.apellido_paterno')
            ->orderBy('a.nombre')
            ->get();

        return response()->json(['data' => $admins]);
    }

    /**
     * Obtiene los detalles de un administrador específico.
     */
    public function obtener($id)
    {
        $admin = DB::table('administradores as a')
            ->join('usuarios as u', 'a.usuario_id', '=', 'u.usuario_id')
            ->where('a.admin_id', $id)
            ->select('a.*', 'u.correo')
            ->first();

        if ($admin) {
            return response()->json(['success' => true, 'data' => $admin]);
        }
        return response()->json(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
    }

    /**
     * Agrega un nuevo administrador.
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
                'rol_id' => 1, // Rol de admin
                'tipo_usuario' => 'admin',
                'activo' => 1,
            ]);

            DB::table('administradores')->insert([
                'usuario_id' => $usuarioId,
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
                'activo' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Administrador agregado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al agregar: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Edita un administrador existente.
     */
    public function editar(Request $request, $id)
    {
        $admin = DB::table('administradores')->where('admin_id', $id)->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'correo' => ['required', 'email', Rule::unique('usuarios')->ignore($admin->usuario_id, 'usuario_id')],
            'contrasena' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            DB::table('administradores')->where('admin_id', $id)->update([
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
            ]);

            $userData = ['correo' => $validated['correo']];
            if (!empty($validated['contrasena'])) {
                $userData['contrasena'] = Hash::make($validated['contrasena']);
            }
            DB::table('usuarios')->where('usuario_id', $admin->usuario_id)->update($userData);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Administrador actualizado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un administrador.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate(['admin_id' => 'required|integer|exists:administradores,admin_id']);
        
        $admin = DB::table('administradores')->where('admin_id', $validated['admin_id'])->first();
        
        // Evitar que un admin se elimine a sí mismo
        if ($admin->usuario_id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.'], 403);
        }

        DB::beginTransaction();
        try {
            DB::table('administradores')->where('admin_id', $admin->admin_id)->delete();
            DB::table('usuarios')->where('usuario_id', $admin->usuario_id)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Administrador eliminado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un administrador.
     */
    public function cambiarEstado(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:administradores,admin_id',
            'activo' => 'required|boolean',
        ]);

        $admin = DB::table('administradores')->where('admin_id', $validated['admin_id'])->first();
        
        // Evitar que un admin se desactive a sí mismo
        if ($admin->usuario_id === auth()->id() && !$validated['activo']) {
            return response()->json(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 403);
        }

        DB::table('administradores')->where('admin_id', $validated['admin_id'])->update(['activo' => $validated['activo']]);
        DB::table('usuarios')->where('usuario_id', $admin->usuario_id)->update(['activo' => $validated['activo']]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }
}