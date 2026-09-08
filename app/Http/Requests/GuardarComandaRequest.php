<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarComandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Esta MISMA clase se usa para abrir (POST) y editar (PUT) una comanda, así que
        // "detalles" cambia según el método: obligatorio al abrir, opcional al editar
        // (por ejemplo, para solo cambiar de mesa una comanda que ya tiene líneas).
        $creando = $this->isMethod('post');

        return [
            'mesa_id' => ['required', 'integer', 'exists:mesas,id'],

            // "user_id" es el mesero responsable de la comanda. No hay autenticación todavía
            // (eso es del Laboratorio 6), así que por ahora se manda explícito en el body y se
            // valida que exista de verdad en la tabla users.
            'user_id' => ['required', 'integer', 'exists:users,id'],

            'detalles' => [$creando ? 'required' : 'sometimes', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required_with:detalles', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required_with:detalles', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'mesa_id.required' => 'Debe indicar la mesa de la comanda.',
            'mesa_id.exists' => 'La mesa indicada no existe.',
            'user_id.required' => 'Debe indicar el mesero responsable de la comanda.',
            'user_id.exists' => 'El usuario indicado no existe.',
            'detalles.required' => 'La comanda debe incluir al menos una línea de detalle.',
            'detalles.min' => 'La comanda debe incluir al menos una línea de detalle.',
            'detalles.*.producto_id.exists' => 'Uno de los productos indicados no existe.',
            'detalles.*.cantidad.min' => 'La cantidad de cada línea debe ser al menos 1.',
            'detalles.*.cantidad.max' => 'La cantidad de cada línea no puede superar 50.',
        ];
    }
}
