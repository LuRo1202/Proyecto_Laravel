<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes_responsables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('estudiante_id');
            $table->unsignedInteger('responsable_id');
            $table->timestamp('fecha_asignacion')->useCurrent();
            
            $table->foreign('estudiante_id')->references('estudiante_id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('responsable_id')->references('responsable_id')->on('responsables')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes_responsables');
    }
};