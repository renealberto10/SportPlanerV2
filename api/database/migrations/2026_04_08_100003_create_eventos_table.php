<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escenario_id')->constrained('escenarios')->cascadeOnDelete();
            $table->string('nombre');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->enum('tipo', ['deportivo', 'cultural', 'produccion', 'otro'])->default('deportivo');
            $table->enum('estado', ['programado', 'en_curso', 'realizado', 'cancelado'])->default('programado');
            $table->text('descripcion')->nullable();
            $table->string('personal')->nullable();
            $table->text('equipos_notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
