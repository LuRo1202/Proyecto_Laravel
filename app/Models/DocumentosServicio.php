<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosServicio extends Model
{
    use HasFactory;

    protected $table = 'documentos_servicio';
    protected $primaryKey = 'documento_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id',
        'tipo_documento_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'fecha_subida',
        'fecha_validacion',
        'validado_por',
        'estado',
        'observaciones'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id', 'solicitud_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id', 'tipo_documento_id');
    }

    public function validador()
    {
        return $this->belongsTo(Usuario::class, 'validado_por', 'usuario_id');
    }
}