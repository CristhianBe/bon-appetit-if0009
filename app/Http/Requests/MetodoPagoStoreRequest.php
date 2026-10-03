<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MetodoPagoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:20', 'unique:metodos_pago,codigo'],
            'nombre' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe un registro con ese código.',
            'nombre.required' => 'El nombre es obligatorio.',
        ];
    }
}
