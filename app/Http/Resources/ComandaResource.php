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
            'total' => $this->total,
            'cerrada_en' => $this->cerrada_en,
            'mesa' => [
                'id' => $this->mesa->id,
                'numero' => $this->mesa->numero,
            ],
            'mesero' => [
                'id' => $this->mesero->id,
                'nombre' => $this->mesero->name,
            ],
            'estado' => [
                'codigo' => $this->estado->codigo,
                'nombre' => $this->estado->nombre,
            ],
            // whenLoaded: solo aparece en el JSON si el controlador cargó la relación con
            // ->load('detalles') (ej: en show()); en index() no se carga, para no traer de más.
            'detalles' => $this->whenLoaded('detalles', fn () => $this->detalles->map(fn ($d) => [
                'producto_id' => $d->producto_id,
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'notas' => $d->notas,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
