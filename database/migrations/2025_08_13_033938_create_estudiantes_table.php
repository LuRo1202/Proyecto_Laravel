<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('estudiante_id');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->string('matricula', 20);
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('carrera', 50)->nullable();
            $table->integer('cuatrimestre')->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('curp', 18)->nullable();
            $table->integer('edad')->nullable();
            $table->string('facebook', 100)->nullable();
            $table->decimal('porcentaje_creditos', 5, 2)->nullable();
            $table->decimal('promedio', 3, 2)->nullable();
            $table->text('domicilio')->nullable();
            $table->enum('sexo', ['Masculino', 'Femenino', 'Otro'])->nullable();
            $table->integer('horas_requeridas')->default(480);
            $table->decimal('horas_completadas', 5, 2)->default(0.00);
            $table->boolean('activo')->default(true);

            $table->foreign('usuario_id')->references('usuario_id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};