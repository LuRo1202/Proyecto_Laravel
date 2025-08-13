<?php

namespace App\Http\Controllers;

use App\Models\PeriodoRegistro;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    /**
     * Verifica si hay un período de registro activo.
     */
    public function verificarPeriodoActivo()
    {
        $periodoActivo = PeriodoRegistro::where('estado', 'activo')
                                        ->where('fecha_fin', '>=', now())
                                        ->first();

        return response()->json([
            'activo' => !is_null($periodoActivo)
        ]);
    }
}