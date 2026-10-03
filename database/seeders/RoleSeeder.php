<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'administrador', 'guard_name' => 'web', 'descripcion' => 'Acceso total al sistema'],
            ['name' => 'mesero', 'guard_name' => 'web', 'descripcion' => 'Crea y gestiona sus propias comandas'],
            ['name' => 'cajero', 'guard_name' => 'web', 'descripcion' => 'Cierra caja y consulta reportes de pagos'],
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
    }
}
