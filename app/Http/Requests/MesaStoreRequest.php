<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MesaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'integer', 'min:1', 'unique:mesas,numero'],
            'capacidad' => ['sometimes', 'integer', 'min:1'],
            'estado_mesa_id' => ['required', 'integer', 'exists:estados_mesa,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'Debe indicar el número de mesa.',
            'numero.unique' => 'Ya existe una mesa con ese número.',
            'estado_mesa_id.required' => 'Debe indicar el estado inicial de la mesa.',
            'estado_mesa_id.exists' => 'El estado indicado no existe.',
        ];
    }
}
