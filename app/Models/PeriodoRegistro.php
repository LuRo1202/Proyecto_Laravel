<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoRegistro extends Model
{
    use HasFactory;

    protected $table = 'periodos_registro';
    protected $primaryKey = 'periodo_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fecha_creacion'
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'periodo_id', 'periodo_id');
    }
}