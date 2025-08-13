<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->increments('solicitud_id');
            $table->unsignedInteger('estudiante_id');
            $table->unsignedInteger('entidad_id');
            $table->unsignedInteger('programa_id');
            $table->unsignedInteger('periodo_id');
            $table->string('funcionario_responsable', 255)->default('');
            $table->string('cargo_funcionario', 255)->default('');
            $table->date('fecha_solicitud');
            $table->text('actividades');
            $table->time('horario_lv_inicio')->nullable();
            $table->time('horario_lv_fin')->nullable();
            $table->time('horario_sd_inicio')->nullable();
            $table->time('horario_sd_fin')->nullable();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->integer('horas_requeridas')->default(480);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->unsignedInteger('aprobado_por')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado_carta_aceptacion', ['Pendiente', 'Aprobada', 'Rechazada', 'Generada'])->default('Pendiente');
            $table->enum('estado_carta_presentacion', ['Pendiente', 'Aprobada', 'Rechazada'])->default('Pendiente');
            $table->enum('estado_carta_termino', ['Pendiente', 'Aprobada', 'Rechazada'])->default('Pendiente');

            $table->foreign('estudiante_id')->references('estudiante_id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('entidad_id')->references('entidad_id')->on('entidades_receptoras')->onDelete('restrict');
            $table->foreign('programa_id')->references('programa_id')->on('programas')->onDelete('restrict');
            $table->foreign('periodo_id')->references('periodo_id')->on('periodos_registro')->onDelete('restrict');
            $table->foreign('aprobado_por')->references('usuario_id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};