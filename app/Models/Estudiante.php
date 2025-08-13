<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';
    protected $primaryKey = 'estudiante_id';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'matricula',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'carrera',
        'cuatrimestre',
        'telefono',
        'curp',
        'edad',
        'facebook',
        'porcentaje_creditos',
        'promedio',
        'domicilio',
        'sexo',
        'horas_requeridas',
        'horas_completadas',
        'activo'
    ];
    
    protected $appends = ['horas_restantes'];
    
    // Accessor para calcular las horas restantes
    public function getHorasRestantesAttribute()
    {
        return $this->horas_requeridas - $this->horas_completadas;
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'estudiante_id', 'estudiante_id');
    }

    public function registrosHoras()
    {
        return $this->hasMany(RegistroHoras::class, 'estudiante_id', 'estudiante_id');
    }

    public function responsables()
    {
        return $this->belongsToMany(Responsable::class, 'estudiantes_responsables', 'estudiante_id', 'responsable_id')->withPivot('fecha_asignacion');
    }
}