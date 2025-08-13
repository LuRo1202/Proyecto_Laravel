<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('usuario_id');
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->unsignedInteger('rol_id');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->dateTime('ultimo_login')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('tipo_usuario', 20)->nullable();

            $table->foreign('rol_id')->references('rol_id')->on('roles')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};