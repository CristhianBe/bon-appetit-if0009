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

    /**
     * Seed the application's database.
     */
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

        // Historial de ventas: comandas ya cobradas (cerradas, con método de pago), atendidas por
        // el mesero o el administrador. Antes se creaban con estados al azar y sin ocupar la mesa,
        // lo que en el panel real se veía como mesas libres con comandas abiertas.
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