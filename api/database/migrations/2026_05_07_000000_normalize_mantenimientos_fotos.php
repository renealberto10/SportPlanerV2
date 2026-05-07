<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convierte mantenimientos.fotos de array de strings (paths) a array de
 * objetos { path, tipo } donde tipo ∈ { 'antes', 'despues' }.
 *
 * Las fotos legacy (string) se asumen como "despues" (registro post-trabajo),
 * que es como se han venido cargando históricamente.
 */
return new class extends Migration {
    public function up(): void
    {
        $rows = DB::table('mantenimientos')
            ->whereNotNull('fotos')
            ->get(['id', 'fotos']);

        foreach ($rows as $row) {
            $arr = json_decode($row->fotos, true);
            if (!is_array($arr) || empty($arr)) {
                continue;
            }

            $normalized = [];
            $needsUpdate = false;

            foreach ($arr as $item) {
                if (is_string($item)) {
                    $normalized[] = ['path' => $item, 'tipo' => 'despues'];
                    $needsUpdate = true;
                } elseif (is_array($item) && isset($item['path'])) {
                    $normalized[] = [
                        'path' => $item['path'],
                        'tipo' => in_array($item['tipo'] ?? null, ['antes', 'despues'], true)
                            ? $item['tipo']
                            : 'despues',
                    ];
                }
            }

            if ($needsUpdate) {
                DB::table('mantenimientos')
                    ->where('id', $row->id)
                    ->update(['fotos' => json_encode($normalized)]);
            }
        }
    }

    public function down(): void
    {
        // Revertir: dejar solo los paths como strings (modo legacy).
        $rows = DB::table('mantenimientos')
            ->whereNotNull('fotos')
            ->get(['id', 'fotos']);

        foreach ($rows as $row) {
            $arr = json_decode($row->fotos, true);
            if (!is_array($arr)) continue;

            $paths = array_values(array_filter(array_map(
                fn($item) => is_array($item) ? ($item['path'] ?? null) : (is_string($item) ? $item : null),
                $arr,
            )));

            DB::table('mantenimientos')
                ->where('id', $row->id)
                ->update(['fotos' => json_encode($paths)]);
        }
    }
};
