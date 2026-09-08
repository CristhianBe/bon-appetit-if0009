<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarProductoRequest extends FormRequest
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
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                // unique()->ignore(...): el nombre no se puede repetir con OTRO producto del
                // menú, pero editar el mismo producto no debe fallar por chocar consigo mismo.
                Rule::unique('productos', 'nombre')->ignore($this->route('producto')),
            ],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            // 'numeric' en vez de 'integer': los precios de un menú suelen tener centavos.
            'precio' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            // 'boolean' acepta true/false/1/0/"1"/"0" — típico de un checkbox en un formulario.
            'disponible' => ['sometimes', 'boolean'],
            'imagen_url' => ['nullable', 'url', 'max:255'],
            // Validación de fecha: si se manda, debe ser una fecha real (rechaza "31 de febrero").
            // Es nullable y sin límite hacia el futuro a propósito: un producto de temporada
            // puede cargarse hoy para que aparezca en el menú recién dentro de unas semanas.
            'disponible_desde' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe un producto con ese nombre.',
            'categoria_id.required' => 'Debe indicar la categoría del producto.',
            'categoria_id.exists' => 'La categoría indicada no existe.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a cero.',
            'imagen_url.url' => 'La URL de la imagen no tiene un formato válido.',
            'disponible_desde.date' => 'La fecha de disponibilidad no es una fecha válida.',
        ];
    }
}
