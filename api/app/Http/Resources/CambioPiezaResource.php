<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CambioPiezaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'mantenimiento_id' => $this->mantenimiento_id,
            'evento_id'        => $this->evento_id,
            'escenario_id'     => $this->escenario_id,
            'escenario'        => $this->whenLoaded('escenario', fn() => $this->escenario ? ['id' => $this->escenario->id, 'nombre' => $this->escenario->nombre] : null),
            'equipo_id'        => $this->equipo_id,
            'equipo'           => $this->whenLoaded('equipo', fn() => $this->equipo ? ['id' => $this->equipo->id, 'nombre' => $this->equipo->nombre] : null),
            'descripcion_pieza'=> $this->descripcion_pieza,
            'tipo_pieza'       => $this->tipo_pieza,
            'serie_instalada'  => $this->serie_instalada,
            'serie_retirada'   => $this->serie_retirada,
            'tecnico_id'       => $this->tecnico_id,
            'tecnico'          => $this->whenLoaded('tecnico', fn() => $this->tecnico ? ['id' => $this->tecnico->id, 'nombre_completo' => $this->tecnico->nombre_completo] : null),
            'fecha'            => $this->fecha,
            'estado_bodega'    => $this->estado_bodega,
            'recibido_por'     => $this->recibido_por,
            'fecha_recepcion'  => $this->fecha_recepcion,
            'notas'            => $this->notas,
            'created_at'       => $this->created_at,
        ];
    }
}
