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
        Schema::create('cambios_piezas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->nullable()->constrained('mantenimientos')->nullOnDelete();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->foreignId('escenario_id')->constrained('escenarios')->cascadeOnDelete();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->string('descripcion_pieza');
            $table->enum('tipo_pieza', ['led', 'tarjeta_señal', 'ventilador', 'cable', 'fuente_poder', 'conector', 'bocina', 'modulo', 'otro']);
            $table->string('serie_instalada')->nullable();
            $table->string('serie_retirada')->nullable();
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete();
            $table->date('fecha');
            // Bodega confirmation
            $table->enum('estado_bodega', ['pendiente', 'recibido'])->default('pendiente');
            $table->string('recibido_por')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_piezas');
    }
};
