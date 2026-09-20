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
            'mesa_id' => Mesa::inRandomOrder()->value('id'),
            'user_id' => User::inRandomOrder()->value('id'),
            'estado_comanda_id' => EstadoComanda::inRandomOrder()->value('id'),
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