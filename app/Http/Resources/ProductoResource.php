<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'categoria_id'   => $this->categoria_id,
            'nombre'         => $this->nombre,
            'descripcion'    => $this->descripcion,
            'precio'         => (float) $this->precio,
            'disponible'     => (bool) $this->disponible,
            'imagen_url'     => $this->imagen_url,
            'categoria'      => new CategoriaResource($this->whenLoaded('categoria')),
            'unidad_medida'  => new UnidadMedidaResource($this->whenLoaded('unidadMedida')),
            'creado_en'      => $this->created_at?->toIso8601String(),
            'actualizado_en' => $this->updated_at?->toIso8601String(),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}