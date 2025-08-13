<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudianteResponsable extends Model
{
    use HasFactory;

    protected $table = 'estudiantes_responsables';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'responsable_id',
        'fecha_asignacion'
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