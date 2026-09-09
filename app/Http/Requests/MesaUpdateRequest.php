<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MesaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mesa = $this->route('mesa');

        return [
            'numero' => [
                'sometimes', 'required', 'integer', 'min:1',
                Rule::unique('mesas', 'numero')->ignore($mesa?->id),
            ],
            'capacidad' => ['sometimes', 'integer', 'min:1'],
            'estado_mesa_id' => ['sometimes', 'required', 'integer', 'exists:estados_mesa,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.unique' => 'Ya existe una mesa con ese número.',
            'estado_mesa_id.exists' => 'El estado indicado no existe.',
        ];
    }
}
