<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntidadReceptora extends Model
{
    use HasFactory;

    protected $table = 'entidades_receptoras';
    protected $primaryKey = 'entidad_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo_entidad',
        'unidad_administrativa',
        'domicilio',
        'municipio',
        'telefono',
        'funcionario_responsable',
        'cargo_funcionario'
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'entidad_id', 'entidad_id');
    }
}