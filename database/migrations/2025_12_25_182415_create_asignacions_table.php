<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('asignaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('equipo_id')->constrained()->onDelete('cascade');
        $table->string('departamento');
        $table->string('responsable');
        $table->string('cargo');
        $table->date('fecha_asignacion');
        $table->date('fecha_devolucion')->nullable();
        $table->text('observaciones')->nullable();
        $table->enum('estado', ['Activa', 'Devuelta', 'Vencida']);
        $table->timestamps();
    });
}
};
