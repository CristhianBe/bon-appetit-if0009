<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Un FormRequest es una clase que Laravel ejecuta automáticamente ANTES de entrar al
// controlador. Si algo no pasa las reglas de rules(), corta la petición y responde 422 con
// los mensajes de error, sin que el controlador tenga que hacer ese chequeo a mano.
class IniciarSesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // true: cualquiera puede intentar iniciar sesión (no hace falta estar logueado
        // para loguearse).
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
