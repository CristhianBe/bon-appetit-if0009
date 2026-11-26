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

        foreach (range(1, 12) as $numero) {
            Mesa::create([
                'numero' => $numero,
                'capacidad' => $numero <= 4 ? 4 : ($numero <= 8 ? 6 : 2),
                'zona' => $numero <= 6 ? 'Salón' : ($numero <= 10 ? 'Terraza' : 'Barra'),
                'estado_mesa_id' => $libre->id,
            ]);
        }
    }
}
