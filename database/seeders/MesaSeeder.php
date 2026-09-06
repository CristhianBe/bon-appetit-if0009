<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EstadoMesa;
use App\Models\Mesa;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $libre = EstadoMesa::where('codigo', 'libre')->firstOrFail();

        for ($numero = 1; $numero <= 8; $numero++) {
            Mesa::create([
                'numero' => $numero,
                'capacidad' => $numero <= 4 ? 4 : 6,
                'estado_mesa_id' => $libre->id,
            ]);
        }
    }
}