<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('rol_id');
            $table->string('nombre_rol', 20)->unique();
            $table->string('descripcion', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};