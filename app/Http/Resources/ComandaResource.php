<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComandaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'mesa_id'           => $this->mesa_id,
            'usuario_id'        => $this->usuario_id,
            'estado_comanda_id' => $this->estado_comanda_id,
            'metodo_pago_id'    => $this->metodo_pago_id,
            'total'             => (float) $this->total,
            'notas'             => $this->notas,
            'mesa'              => new MesaResource($this->whenLoaded('mesa')),
            'usuario'           => new UserResource($this->whenLoaded('usuario')),
            'estado'            => new EstadoComandaResource($this->whenLoaded('estadoComanda')),
            'metodo_pago'       => new MetodoPagoResource($this->whenLoaded('metodoPago')),
            'detalles'          => DetalleComandaResource::collection($this->whenLoaded('detalles')),
            'creado_en'         => $this->created_at?->toIso8601String(),
            'actualizado_en'    => $this->updated_at?->toIso8601String(),
        ];
    }
}