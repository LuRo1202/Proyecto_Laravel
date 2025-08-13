<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    protected $primaryKey = 'solicitud_id';
    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'entidad_id',
        'programa_id',
        'periodo_id',
        'funcionario_responsable',
        'cargo_funcionario',
        'fecha_solicitud',
        'actividades',
        'horario_lv_inicio',
        'horario_lv_fin',
        'horario_sd_inicio',
        'horario_sd_fin',
        'periodo_inicio',
        'periodo_fin',
        'horas_requeridas',
        'fecha_registro',
        'estado',
        'fecha_aprobacion',
        'aprobado_por',
        'observaciones',
        'estado_carta_aceptacion',
        'estado_carta_presentacion',
        'estado_carta_termino'
    ];
    
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'estudiante_id');
    }

    public function entidadReceptora()
    {
        return $this->belongsTo(EntidadReceptora::class, 'entidad_id', 'entidad_id');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id', 'programa_id');
    }
    
    public function periodoRegistro()
    {
        return $this->belongsTo(PeriodoRegistro::class, 'periodo_id', 'periodo_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por', 'usuario_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentosServicio::class, 'solicitud_id', 'solicitud_id');
    }
}