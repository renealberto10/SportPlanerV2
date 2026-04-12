<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'fecha_solicitud'     => $this->fecha_solicitud,
            'actividad'           => $this->actividad,
            'escenario_id'        => $this->escenario_id,
            'escenario'           => $this->whenLoaded('escenario', fn() => $this->escenario ? ['id' => $this->escenario->id, 'nombre' => $this->escenario->nombre] : null),
            'escenario_texto'     => $this->escenario_texto,
            'solicita'            => $this->solicita,
            'fecha_calendarizada' => $this->fecha_calendarizada,
            'hora'                => $this->hora,
            'tecnico_id'          => $this->tecnico_id,
            'tecnico'             => $this->whenLoaded('tecnico', fn() => $this->tecnico ? ['id' => $this->tecnico->id, 'nombre_completo' => $this->tecnico->nombre_completo] : null),
            'seguimiento'         => $this->seguimiento,
            'prioridad'           => $this->prioridad,
            'estado'              => $this->estado,
            'notas'               => $this->notas,
            'emails_invitar'      => $this->emails_invitar,
            'created_at'          => $this->created_at,
        ];
    }
}
