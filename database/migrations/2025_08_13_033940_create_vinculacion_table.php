<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vinculacion', function (Blueprint $table) {
            $table->increments('vinculacion_id');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('usuario_id')->references('usuario_id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculacion');
    }
};