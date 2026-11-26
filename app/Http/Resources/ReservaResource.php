<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mesa_id' => $this->mesa_id,
            'mesa' => $this->whenLoaded('mesa', fn () => $this->mesa ? [
                'id' => $this->mesa->id,
                'numero' => $this->mesa->numero,
                'zona' => $this->mesa->zona,
                'capacidad' => $this->mesa->capacidad,
            ] : null),
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'personas' => $this->personas,
            'zona' => $this->zona,
            'fecha' => $this->fecha?->toDateString(),
            'hora' => $this->hora,
            'estado' => $this->estado,
            'notas' => $this->notas,
            'creado_en' => $this->created_at?->toIso8601String(),
            'actualizado_en' => $this->updated_at?->toIso8601String(),
        ];
    }
}
