<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // El campo que manda el cliente sigue llamándose "nombre" (no se cambia el
            // contrato); RoleController lo traduce a "name" al crear el modelo, que es como
            // lo guarda spatie/laravel-permission por dentro.
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ];
    }
}
