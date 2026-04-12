<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('mantenimientos', 'tecnico_id')) {
            Schema::table('mantenimientos', function (Blueprint $table) {
                $table->foreignId('tecnico_id')->nullable()->after('tecnico')->constrained('tecnicos')->nullOnDelete();
            });
        }
    }
    public function down(): void {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Tecnico::class);
            $table->dropColumn('tecnico_id');
        });
    }
};
