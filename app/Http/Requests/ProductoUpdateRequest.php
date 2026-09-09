<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $producto = $this->route('producto');

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('productos', 'nombre')
                    ->where(fn ($query) => $query->where(
                        'categoria_id',
                        $this->categoria_id ?? $producto?->categoria_id
                    ))
                    ->ignore($producto?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:1', 'max:999999.99'],
            'categoria_id' => ['sometimes', 'required', 'integer', 'exists:categorias,id'],
            'disponible' => ['sometimes', 'boolean'],
            'imagen_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe un producto con ese nombre en esta categoría.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'precio.max' => 'El precio ingresado es demasiado alto.',
            'categoria_id.required' => 'Debe indicar la categoría del producto.',
            'categoria_id.integer' => 'La categoría indicada no es válida.',
            'categoria_id.exists' => 'La categoría indicada no existe.',
            'disponible.boolean' => 'El campo disponible debe ser verdadero o falso.',
            'imagen_url.url' => 'La URL de la imagen no tiene un formato válido.',
            'imagen_url.max' => 'La URL de la imagen es demasiado larga.',
        ];
    }
}
