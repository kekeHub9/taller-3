<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_serie')->unique();
            $table->string('nombre');
            $table->string('tipo');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->string('proveedor')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('departamento');
            $table->string('estado')->default('Activo');
            $table->date('ultima_calibracion')->nullable();
            $table->date('proxima_calibracion')->nullable();
            $table->integer('vida_util')->nullable();
            $table->decimal('depreciacion', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};