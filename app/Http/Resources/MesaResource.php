<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'capacidad' => $this->capacidad,
            'estado_mesa_id' => $this->estado_mesa_id,
            'estado' => new EstadoMesaResource($this->whenLoaded('estado')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
