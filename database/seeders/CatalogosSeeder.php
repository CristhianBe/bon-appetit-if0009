<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
use App\Models\MetodoPago;
use App\Models\UnidadMedida;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['codigo' => 'libre', 'nombre' => 'Libre'],
            ['codigo' => 'ocupada', 'nombre' => 'Ocupada'],
            ['codigo' => 'reservada', 'nombre' => 'Reservada'],
        ] as $estado) {
            EstadoMesa::create($estado);
        }

        foreach ([
            ['codigo' => 'abierta', 'nombre' => 'Abierta'],
            ['codigo' => 'en_preparacion', 'nombre' => 'En preparación'],
            ['codigo' => 'servida', 'nombre' => 'Servida'],
            ['codigo' => 'cerrada', 'nombre' => 'Cerrada'],
        ] as $estado) {
            EstadoComanda::create($estado);
        }

        foreach ([
            ['codigo' => 'efectivo', 'nombre' => 'Efectivo'],
            ['codigo' => 'tarjeta', 'nombre' => 'Tarjeta'],
            ['codigo' => 'sinpe', 'nombre' => 'SINPE Móvil'],
        ] as $metodo) {
            MetodoPago::create($metodo);
        }

        foreach ([
            ['codigo' => 'kg', 'nombre' => 'Kilogramo'],
            ['codigo' => 'l', 'nombre' => 'Litro'],
            ['codigo' => 'unidad', 'nombre' => 'Unidad'],
        ] as $unidad) {
            UnidadMedida::create($unidad);
        }
    }
}