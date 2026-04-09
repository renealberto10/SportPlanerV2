<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscenarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'direccion' => $this->direccion,
            'activo' => $this->activo,
            'tiene_colosseo' => $this->tiene_colosseo,
            'equipos_count' => $this->whenCounted('equipos'),
            'mantenimientos_count' => $this->whenCounted('mantenimientos'),
            'eventos_count' => $this->whenCounted('eventos'),
            'created_at' => $this->created_at,
        ];
    }
}
