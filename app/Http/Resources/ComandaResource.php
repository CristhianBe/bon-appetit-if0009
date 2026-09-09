<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComandaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mesa_id' => $this->mesa_id,
            'mesa' => new MesaResource($this->whenLoaded('mesa')),
            'user_id' => $this->user_id,
            'mesero' => new UserResource($this->whenLoaded('mesero')),
            'estado_comanda_id' => $this->estado_comanda_id,
            'estado' => new EstadoComandaResource($this->whenLoaded('estadoComanda')),
            'total' => $this->total,
            'cerrada_en' => $this->cerrada_en,
            'detalles' => DetalleComandaResource::collection($this->whenLoaded('detalles')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
