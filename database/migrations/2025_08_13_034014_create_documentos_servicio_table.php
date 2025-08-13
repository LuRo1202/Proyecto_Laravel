<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_servicio', function (Blueprint $table) {
            $table->increments('documento_id');
            $table->unsignedInteger('solicitud_id');
            $table->unsignedInteger('tipo_documento_id');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 255);
            $table->string('tipo_archivo', 255);
            $table->timestamp('fecha_subida')->useCurrent();
            $table->dateTime('fecha_validacion')->nullable();
            $table->unsignedInteger('validado_por')->nullable()->comment('ID del usuario de vinculación que validó');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('observaciones')->nullable();

            $table->foreign('solicitud_id')->references('solicitud_id')->on('solicitudes')->onDelete('cascade');
            $table->foreign('tipo_documento_id')->references('tipo_documento_id')->on('tipos_documentos')->onDelete('restrict');
            $table->foreign('validado_por')->references('usuario_id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_servicio');
    }
};