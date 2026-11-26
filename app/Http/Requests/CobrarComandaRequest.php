<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CobrarComandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_pago_id' => ['required', 'integer', 'exists:metodos_pago,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'metodo_pago_id.required' => 'Debe indicar el método de pago.',
            'metodo_pago_id.integer' => 'El método de pago indicado no es válido.',
            'metodo_pago_id.exists' => 'El método de pago indicado no existe.',
        ];
    }
}
