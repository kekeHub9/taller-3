<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
   //migracion de datos de manteinimiento
{
    Schema::create('mantenimientos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('equipo_id')->constrained()->onDelete('cascade');
        $table->enum('tipo', ['Preventivo', 'Correctivo', 'Calibración']);
        $table->date('fecha_programada');
        $table->date('fecha_realizacion')->nullable();
        $table->string('tecnico');
        $table->decimal('costo', 10, 2)->default(0);
        $table->text('descripcion');
        $table->text('solucion')->nullable();
        $table->integer('tiempo_inactivo')->nullable(); // en horas
        $table->enum('estado', ['Pendiente', 'En proceso', 'Completado', 'Cancelado']);
        $table->timestamps();
    });
}
};
