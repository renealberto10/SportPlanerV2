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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escenario_id')->nullable()->constrained('escenarios')->nullOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['pantalla', 'bocina', 'consola', 'servidor', 'otro'])->default('pantalla');
            $table->string('modelo')->nullable();
            $table->string('serie')->nullable();
            $table->enum('estado', ['operativo', 'mantenimiento', 'falla', 'baja'])->default('operativo');
            $table->date('fecha_instalacion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
