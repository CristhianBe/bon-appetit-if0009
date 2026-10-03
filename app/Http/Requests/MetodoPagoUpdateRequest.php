<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MetodoPagoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registro = $this->route('metodoPago');

        return [
            'codigo' => [
                'sometimes', 'required', 'string', 'max:20',
                Rule::unique('metodos_pago', 'codigo')->ignore($registro?->id),
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
