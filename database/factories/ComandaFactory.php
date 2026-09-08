<?php

namespace Database\Factories;

use App\Models\EstadoComanda;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComandaFactory extends Factory
{
    public function definition(): array
    {
        return [
            // "?? Modelo::factory()": reutiliza una fila que ya exista (rápido, típico al sembrar
            // datos de ejemplo con muchas mesas/usuarios ya creados) o crea una nueva sobre la
            // marcha si la prueba arranca de una base de datos vacía (ver RefreshDatabase en
            // tests/Pest.php) — sin esto, una comanda de prueba sin mesa/usuario previos fallaría
            // por violar la restricción NOT NULL de la columna.
            'mesa_id' => Mesa::inRandomOrder()->value('id') ?? Mesa::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'estado_comanda_id' => EstadoComanda::inRandomOrder()->value('id')
                ?? EstadoComanda::firstOrCreate(['codigo' => 'abierta'], ['nombre' => 'Abierta'])->id,
            'total' => null,
        ];
    }

    public function withDetalles(int $min = 1, int $max = 4): static
    {
        return $this->afterCreating(function ($comanda) use ($min, $max) {
            $productos = Producto::inRandomOrder()->limit(fake()->numberBetween($min, $max))->get();

            $total = 0;
            foreach ($productos as $producto) {
                $cantidad = fake()->numberBetween(1, 3);
                $comanda->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                ]);
                $total += $cantidad * $producto->precio;
            }

            $comanda->update(['total' => $total]);
        });
    }
}
