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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escenario_id')->constrained('escenarios')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('tecnico')->nullable();
            $table->enum('tipo', ['preventivo', 'correctivo', 'operativo'])->default('preventivo');
            $table->text('actividades')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado'])->default('completado');
            $table->decimal('horas', 5, 2)->default(0);
            $table->string('personal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
