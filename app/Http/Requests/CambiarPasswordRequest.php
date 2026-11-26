<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_actual' => ['required', 'string'],
            'password' => [
                'required', 'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password_actual.required' => 'Debe indicar la contraseña actual.',
            'password.required' => 'Debe indicar la contraseña nueva.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
