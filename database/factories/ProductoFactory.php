<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Categoria::factory() crea una categoría nueva si el test no pasa una con ->for(...).
            'categoria_id' => Categoria::factory(),
            'nombre' => fake()->unique()->words(3, true),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomFloat(2, 500, 8000),
            'disponible' => true,
            'imagen_url' => null,
            'disponible_desde' => null,
        ];
    }
}
