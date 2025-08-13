<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recuperacion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('correo', 255);
            $table->string('token', 100);
            $table->dateTime('expira');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recuperacion');
    }
};