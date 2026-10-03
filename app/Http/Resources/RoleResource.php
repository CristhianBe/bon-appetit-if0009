<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // El contrato externo de la API sigue usando "nombre" (no se cambia para quien ya
            // consume este endpoint), aunque por dentro spatie/laravel-permission guarda la
            // columna como "name" (así es como el paquete identifica los roles internamente).
            'nombre' => $this->name,
            'descripcion' => $this->descripcion,
        ];
    }
}
