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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_solicitud');
            $table->text('actividad');
            $table->foreignId('escenario_id')->nullable()->constrained('escenarios')->nullOnDelete();
            $table->string('escenario_texto')->nullable();
            $table->string('solicita');
            $table->date('fecha_calendarizada')->nullable();
            $table->time('hora')->nullable();
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete();
            $table->text('seguimiento')->nullable();
            $table->enum('prioridad', ['alto', 'medio', 'bajo'])->default('medio');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
