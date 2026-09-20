<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetalleComandaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comanda_id' => ['required', 'integer', 'exists:comandas,id'],
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'comanda_id.required' => 'Debe indicar la comanda.',
            'comanda_id.exists' => 'La comanda indicada no existe.',
            'producto_id.required' => 'Debe indicar el producto.',
            'producto_id.exists' => 'El producto indicado no existe.',
            'cantidad.required' => 'Debe indicar la cantidad.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
