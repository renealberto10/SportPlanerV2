<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MantenimientoResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'           => $this->id,
            'escenario_id' => $this->escenario_id,
            'escenario'    => $this->whenLoaded('escenario', fn() => ['id' => $this->escenario->id, 'nombre' => $this->escenario->nombre]),
            'tecnico_id'   => $this->tecnico_id,
            'tecnico_obj'  => $this->whenLoaded('tecnicoRel', fn() => $this->tecnicoRel ? ['id' => $this->tecnicoRel->id, 'nombre_completo' => $this->tecnicoRel->nombre_completo] : null),
            'fecha'        => $this->fecha,
            'hora'         => $this->hora,
            'tecnico'      => $this->tecnico,
            'tipo'         => $this->tipo,
            'actividades'  => $this->actividades,
            'observaciones'=> $this->observaciones,
            'estado'       => $this->estado,
            'horas'        => $this->horas,
            'visitas'      => (int) ($this->visitas ?? 1),
            'personal'     => $this->personal,
            'fotos'        => collect($this->fotos ?? [])->map(function ($f) {
                if (is_string($f)) {
                    return [
                        'path'      => $f,
                        'tipo'      => 'despues',
                        'url'       => asset('storage/' . $f),
                        'thumb_url' => url('/api/thumb') . '?w=800&path=' . rawurlencode($f),
                    ];
                }
                $path = $f['path'] ?? '';
                return [
                    'path'      => $path,
                    'tipo'      => in_array($f['tipo'] ?? null, ['antes', 'despues'], true) ? $f['tipo'] : 'despues',
                    'url'       => asset('storage/' . $path),
                    'thumb_url' => url('/api/thumb') . '?w=800&path=' . rawurlencode($path),
                ];
            })->values(),
            'created_at'   => $this->created_at,
        ];
    }
}
