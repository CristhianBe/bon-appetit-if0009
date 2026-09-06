<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $roles = [
        ['nombre' => 'administrador', 'descripcion' => 'Acceso total al sistema'],
        ['nombre' => 'mesero', 'descripcion' => 'Crea y gestiona sus propias comandas'],
        ['nombre' => 'cajero', 'descripcion' => 'Cierra caja y consulta reportes de pagos'],
    ];

    foreach ($roles as $rol) {
        Role::create($rol);
    }
    }
}
