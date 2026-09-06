<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Nachos con queso', 'categoria' => 'Entradas', 'precio' => 3200, 'disponible' => true],
            ['nombre' => 'Casado con pollo', 'categoria' => 'Platos fuertes', 'precio' => 4500, 'disponible' => true],
            ['nombre' => 'Refresco natural', 'categoria' => 'Bebidas', 'precio' => 1200, 'disponible' => true],
            ['nombre' => 'Cheesecake', 'categoria' => 'Postres', 'precio' => 2500, 'disponible' => false],
            ['nombre' => 'Ensalada César', 'categoria' => 'Entradas', 'precio' => 3000, 'disponible' => true],
            ['nombre' => 'Arroz con camarones', 'categoria' => 'Platos fuertes', 'precio' => 5200, 'disponible' => true],
        ];

        foreach ($productos as $p) {
            $categoria = Categoria::where('nombre', $p['categoria'])->firstOrFail();

            Producto::create([
                'categoria_id' => $categoria->id,
                'nombre' => $p['nombre'],
                'precio' => $p['precio'],
                'disponible' => $p['disponible'],
            ]);
        }
    }
}