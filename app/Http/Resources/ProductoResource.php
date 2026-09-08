<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponible' => $this->disponible,
            'imagen_url' => $this->imagen_url,
            // ->format('Y-m-d'): el cast 'date' del modelo lo entrega como objeto Carbon; sin
            // formatear saldría con hora (00:00:00) en el JSON.
            'disponible_desde' => $this->disponible_desde?->format('Y-m-d'),
            'categoria' => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
