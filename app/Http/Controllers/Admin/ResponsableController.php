<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResponsableController extends Controller
{
    /**
     * Muestra la vista principal de gestión de responsables.
     */
    public function index()
    {
        return view('admin.gestion_responsables');
    }

    /**
     * Proporciona la lista de todos los responsables para DataTables.
     */
    public function listar()
    {
        $responsables = DB::table('responsables as r')
            ->leftJoin('usuarios as u', 'r.usuario_id', '=', 'u.usuario_id')
            ->select(
                'r.responsable_id',
                'r.nombre',
                'r.apellido_paterno',
                'r.apellido_materno',
                'r.cargo',
                'r.departamento',
                'r.telefono',
                'r.activo',
                'u.correo'
            )
            ->orderBy('r.apellido_paterno')->orderBy('r.nombre')
            ->get();

        return response()->json(['data' => $responsables]);
    }

    /**
     * Obtiene los detalles de un responsable específico para el modal de edición.
     */
    public function obtener($id)
    {
        $responsable = DB::table('responsables as r')
            ->join('usuarios as u', 'r.usuario_id', '=', 'u.usuario_id')
            ->where('r.responsable_id', $id)
            ->select('r.*', 'u.correo')
            ->first();

        if ($responsable) {
            return response()->json(['success' => true, 'data' => $responsable]);
        }
        return response()->json(['success' => false, 'message' => 'Responsable no encontrado.'], 404);
    }

    /**
     * Agrega un nuevo responsable.
     */
    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'correo' => 'required|email|max:255|unique:usuarios,correo',
            'contrasena' => 'required|string|min:6',
            'cargo' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
        ]);

        DB::beginTransaction();
        try {
            $usuarioId = DB::table('usuarios')->insertGetId([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'rol_id' => 2, // Asumimos que el rol de responsable es 2
                'tipo_usuario' => 'encargado',
                'activo' => 1,
            ]);

            DB::table('responsables')->insert([
                'usuario_id' => $usuarioId,
                'nombre' => $validated['nombre'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'cargo' => $validated['cargo'],
                'departamento' => $validated['departamento'],
                'telefono' => $validated['telefono'],
                'activo' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Responsable agregado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al agregar responsable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edita un responsable existente.
     */
    public function editar(Request $request, $id)
    {
        $responsable = DB::table('responsables')->where('responsable_id', $id)->first();
        if (!$responsable) {
            return response()->json(['success' => false, 'message' => 'Responsable no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'correo' => ['required', 'email', Rule::unique('usuarios')->ignore($responsable->usuario_id, 'usuario_id')],
            'contrasena' => 'nullable|string|min:6',
            // ... resto de campos
        ]);
        
        DB::beginTransaction();
        try {
            DB::table('responsables')->where('responsable_id', $id)->update([
                'nombre' => $request->input('nombre'),
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'cargo' => $request->input('cargo'),
                'departamento' => $request->input('departamento'),
                'telefono' => $request->input('telefono'),
            ]);

            $userData = ['correo' => $validated['correo']];
            if (!empty($validated['contrasena'])) {
                $userData['contrasena'] = Hash::make($validated['contrasena']);
            }
            DB::table('usuarios')->where('usuario_id', $responsable->usuario_id)->update($userData);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Responsable actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un responsable.
     */
    public function eliminar(Request $request)
    {
        $validated = $request->validate(['responsable_id' => 'required|integer|exists:responsables,responsable_id']);
        
        $responsable = DB::table('responsables')->where('responsable_id', $validated['responsable_id'])->first();
        
        DB::beginTransaction();
        try {
            // Eliminar de tablas dependientes primero
            DB::table('estudiantes_responsables')->where('responsable_id', $responsable->responsable_id)->delete();
            
            DB::table('responsables')->where('responsable_id', $responsable->responsable_id)->delete();
            DB::table('usuarios')->where('usuario_id', $responsable->usuario_id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Responsable eliminado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar. Es posible que este responsable tenga datos asociados.'], 500);
        }
    }
    
    /**
     * Cambia el estado (activo/inactivo) de un responsable.
     */
    public function cambiarEstado(Request $request)
    {
        $validated = $request->validate([
            'responsable_id' => 'required|integer|exists:responsables,responsable_id',
            'activo' => 'required|boolean',
        ]);

        $responsable = DB::table('responsables')->where('responsable_id', $validated['responsable_id'])->first();

        DB::table('responsables')->where('responsable_id', $validated['responsable_id'])->update(['activo' => $validated['activo']]);
        DB::table('usuarios')->where('usuario_id', $responsable->usuario_id)->update(['activo' => $validated['activo']]);

        return response()->json(['success' => true, 'message' => 'Estado del responsable actualizado.']);
    }
}