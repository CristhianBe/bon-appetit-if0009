<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // "rol" (nombre) reemplaza a "rol_id"/"rol" (objeto): el negocio sigue siendo un
            // solo rol por usuario, así que se expone el nombre directo en vez de un arreglo.
            // whenLoaded('roles') evita una consulta extra si el controlador no cargó la
            // relación (ej. en listados donde no hace falta).
            'rol' => $this->whenLoaded('roles', fn () => $this->roles->first()?->name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
