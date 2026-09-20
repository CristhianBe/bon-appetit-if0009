<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleComandaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'comanda_id'      => $this->comanda_id,
            'producto_id'     => $this->producto_id,
            'cantidad'        => (int) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'subtotal'        => (float) $this->subtotal,
            'notas'           => $this->notas,
            'producto'        => new ProductoResource($this->whenLoaded('producto')),
        ];
    }
}