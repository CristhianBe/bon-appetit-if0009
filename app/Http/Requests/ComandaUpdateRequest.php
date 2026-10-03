<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ComandaUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mesa_id' => ['sometimes', 'required', 'integer', 'exists:mesas,id'],
            'estado_comanda_id' => ['sometimes', 'required', 'integer', 'exists:estados_comanda,id'],
            'cerrada_en' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'mesa_id.required' => 'Debe indicar la mesa de la comanda.',
            'mesa_id.integer' => 'La mesa indicada no es válida.',
            'mesa_id.exists' => 'La mesa indicada no existe.',
            'estado_comanda_id.required' => 'Debe indicar el estado de la comanda.',
            'estado_comanda_id.integer' => 'El estado indicado no es válido.',
            'estado_comanda_id.exists' => 'El estado indicado no existe.',
            'cerrada_en.date' => 'La fecha de cierre no tiene un formato válido.',
            'cerrada_en.before_or_equal' => 'La fecha de cierre no puede ser en el futuro.',
        ];
    }
}