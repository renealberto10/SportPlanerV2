<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TecnicoResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'               => $this->id,
            'nombre'           => $this->nombre,
            'apellido'         => $this->apellido,
            'nombre_completo'  => $this->nombre_completo,
            'telefono'         => $this->telefono,
            'email'            => $this->email,
            'especialidad'     => $this->especialidad,
            'activo'           => $this->activo,
            'notas'            => $this->notas,
            'mantenimientos_count' => $this->whenCounted('mantenimientos'),
            'created_at'       => $this->created_at,
        ];
    }
}
