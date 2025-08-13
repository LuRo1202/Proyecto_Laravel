<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documentos', function (Blueprint $table) {
            $table->increments('tipo_documento_id');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('requerido')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_documentos');
    }
};