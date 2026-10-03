<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// Valida el body de PUT /api/password (cambio de la propia contraseña).
class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // true: cualquier usuario YA AUTENTICADO (la ruta lleva auth:sanctum) puede intentar
        // cambiar SU PROPIA contraseña. No hay chequeo de "dueño" que hacer acá porque
        // siempre se cambia la del usuario del token, nunca la de otro.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Se pide la contraseña actual para evitar que alguien con un token robado (pero
            // sin conocer la contraseña real) pueda secuestrar la cuenta cambiándola.
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
