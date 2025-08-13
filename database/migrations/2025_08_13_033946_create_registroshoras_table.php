<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registroshoras', function (Blueprint $table) {
            $table->increments('registro_id');
            $table->unsignedInteger('estudiante_id');
            $table->unsignedInteger('responsable_id');
            $table->date('fecha');
            $table->dateTime('hora_entrada');
            $table->dateTime('hora_salida')->nullable();
            $table->decimal('horas_acumuladas', 5, 2)->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->dateTime('fecha_validacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('estudiante_id')->references('estudiante_id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('responsable_id')->references('responsable_id')->on('responsables')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registroshoras');
    }
};