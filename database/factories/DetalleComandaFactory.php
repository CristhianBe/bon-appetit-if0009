<?php

namespace Database\Factories;

use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleComanda>
 */
class DetalleComandaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comanda_id' => Comanda::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 5),
            'precio_unitario' => fake()->randomFloat(2, 500, 8000),
            'notas' => null,
        ];
    }
}
