<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'numero'         => $this->numero,
            'capacidad'      => $this->capacidad,
            'estado_mesa_id' => $this->estado_mesa_id,
            'estado'         => new EstadoMesaResource($this->whenLoaded('estadoMesa')),
            'comandas'       => ComandaResource::collection($this->whenLoaded('comandas')),
            'creado_en'      => $this->created_at?->toIso8601String(),
            'actualizado_en' => $this->updated_at?->toIso8601String(),
        ];
    }
}