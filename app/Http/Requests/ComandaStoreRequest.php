<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ComandaStoreRequest extends FormRequest
{
       public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mesa_id' => ['required', 'integer', 'exists:mesas,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mesa_id.required' => 'Debe indicar la mesa para abrir la comanda.',
            'mesa_id.integer' => 'La mesa indicada no es válida.',
            'mesa_id.exists' => 'La mesa indicada no existe.',
            'user_id.required' => 'Debe indicar el mesero responsable.',
            'user_id.integer' => 'El mesero indicado no es válido.',
            'user_id.exists' => 'El usuario indicado no existe.',
        ];
    }
}