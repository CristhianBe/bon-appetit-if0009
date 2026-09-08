<?php

namespace Database\Factories;

use App\Models\EstadoMesa;
use App\Models\Mesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mesa>
 */
class MesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => fake()->unique()->numberBetween(100, 999),
            'capacidad' => fake()->randomElement([2, 4, 6, 8]),
            // EstadoMesa es un catálogo (no tiene factory propia): firstOrCreate() reutiliza la
            // fila "libre" si ya existe (ej. sembrada por CatalogosSeeder) o la crea si no.
            'estado_mesa_id' => EstadoMesa::firstOrCreate(['codigo' => 'libre'], ['nombre' => 'Libre'])->id,
        ];
    }

    /**
     * Estado alternativo: mesa ocupada (para probar que no se puede abrir otra comanda ahí).
     */
    public function ocupada(): static
    {
        return $this->state(fn () => [
            'estado_mesa_id' => EstadoMesa::firstOrCreate(['codigo' => 'ocupada'], ['nombre' => 'Ocupada'])->id,
        ]);
    }
}
