<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    use HasFactory;

    protected $table = 'responsables';
    protected $primaryKey = 'responsable_id';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'cargo',
        'departamento',
        'telefono',
        'activo'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }
    
    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_responsables', 'responsable_id', 'estudiante_id')->withPivot('fecha_asignacion');
    }
}