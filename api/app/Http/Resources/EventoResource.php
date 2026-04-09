<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'escenario_id' => $this->escenario_id,
            'escenario' => $this->whenLoaded('escenario', fn() => ['id' => $this->escenario->id, 'nombre' => $this->escenario->nombre]),
            'nombre' => $this->nombre,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'descripcion' => $this->descripcion,
            'personal' => $this->personal,
            'equipos_notas' => $this->equipos_notas,
            'created_at' => $this->created_at,
        ];
    }
}
