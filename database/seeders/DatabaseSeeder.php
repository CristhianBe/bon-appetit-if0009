<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comanda;
use App\Models\EstadoComanda;
use App\Models\MetodoPago;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
       $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            CatalogosSeeder::class,
            MesaSeeder::class,
        ]);

        $cerrada = EstadoComanda::where('codigo', 'cerrada')->value('id');
        $personal = User::role(['administrador', 'mesero'])->pluck('id');

        Comanda::factory()
            ->count(20)
            ->withDetalles()
            ->state(fn () => [
                'user_id' => $personal->random(),
                'estado_comanda_id' => $cerrada,
                'metodo_pago_id' => MetodoPago::inRandomOrder()->value('id'),
                'cerrada_en' => now()->subHours(fake()->numberBetween(1, 72)),
            ])
            ->create();
    }
}
