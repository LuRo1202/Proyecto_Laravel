<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'usuario_id';
    public $timestamps = false;

    protected $fillable = [
        'correo',
        'contrasena',
        'rol_id',
        'ultimo_login',
        'activo',
        'tipo_usuario',
    ];

    protected $hidden = [
        'contrasena',
    ];
    
    // Sobreescribe el método de Laravel para usar tu columna de contraseña
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'rol_id');
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'usuario_id', 'usuario_id');
    }

    public function responsable()
    {
        return $this->hasOne(Responsable::class, 'usuario_id', 'usuario_id');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'usuario_id', 'usuario_id');
    }

    public function vinculacion()
    {
        return $this->hasOne(Vinculacion::class, 'usuario_id', 'usuario_id');
    }

    // --- CÓDIGO AÑADIDO ---
    /**
     * Accesor para obtener el nombre completo del usuario basado en su rol.
     * Esto te permite usar Auth::user()->nombre_completo en tus vistas.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        $nombreCompleto = '';

        // Usamos la relación 'rol' que ya tienes para saber qué perfil buscar
        switch ($this->rol->nombre_rol) {
            case 'admin':
                // Accedemos a la relación 'administrador' y construimos el nombre
                if ($this->administrador) {
                    $nombreCompleto = trim($this->administrador->nombre . ' ' . $this->administrador->apellido_paterno . ' ' . $this->administrador->apellido_materno);
                }
                break;
            
            // Puedes agregar más casos para otros roles en el futuro
            // case 'estudiante':
            //     if ($this->estudiante) {
            //         $nombreCompleto = trim($this->estudiante->nombre . ' ' . $this->estudiante->apellido_paterno);
            //     }
            //     break;
        }

        // Si por alguna razón no se encuentra el nombre, devolvemos el correo
        return !empty($nombreCompleto) ? $nombreCompleto : $this->correo;
    }
    // --- FIN DEL CÓDIGO AÑADIDO ---
}