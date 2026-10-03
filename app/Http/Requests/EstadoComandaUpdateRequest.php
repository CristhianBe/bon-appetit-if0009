<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstadoComandaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registro = $this->route('estadoComanda');

        return [
            'codigo' => [
                'sometimes', 'required', 'string', 'max:20',
                Rule::unique('estados_comanda', 'codigo')->ignore($registro?->id),
            ],
            'nombre' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un registro con ese código.',
        ];
    }
}
