<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'tipos_documentos';
    protected $primaryKey = 'tipo_documento_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'requerido'
    ];

    public function documentos()
    {
        return $this->hasMany(DocumentosServicio::class, 'tipo_documento_id', 'tipo_documento_id');
    }
}