<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Entradas', 'Platos fuertes', 'Bebidas', 'Postres'] as $nombre) {
            Categoria::create(['nombre' => $nombre]);
        }
    }
}
