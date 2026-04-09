<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'escenario_id' => $this->escenario_id,
            'escenario' => $this->whenLoaded('escenario', fn() => ['id' => $this->escenario->id, 'nombre' => $this->escenario->nombre]),
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'estado' => $this->estado,
            'fecha_instalacion' => $this->fecha_instalacion,
            'notas' => $this->notas,
            'created_at' => $this->created_at,
        ];
    }
}
