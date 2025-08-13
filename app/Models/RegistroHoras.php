<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroHoras extends Model
{
    use HasFactory;

    protected $table = 'registroshoras';
    protected $primaryKey = 'registro_id';
    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'responsable_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'horas_acumuladas',
        'estado',
        'fecha_validacion',
        'observaciones',
        'fecha_registro'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'estudiante_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'responsable_id', 'responsable_id');
    }
}