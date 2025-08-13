<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entidades_receptoras', function (Blueprint $table) {
            $table->increments('entidad_id');
            $table->string('nombre', 100);
            $table->enum('tipo_entidad', ['Federal', 'Estatal', 'Municipal', 'O.N.G.', 'I.E.', 'I.P.']);
            $table->string('unidad_administrativa', 100);
            $table->text('domicilio');
            $table->string('municipio', 50);
            $table->string('telefono', 15);
            $table->string('funcionario_responsable', 100);
            $table->string('cargo_funcionario', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades_receptoras');
    }
};