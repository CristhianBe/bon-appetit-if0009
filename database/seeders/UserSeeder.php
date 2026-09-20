<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('nombre', 'administrador')->firstOrFail();
        $mesero = Role::where('nombre', 'mesero')->firstOrFail();
        $cajero = Role::where('nombre', 'cajero')->firstOrFail();

        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@bonappetit.test',
            'password' => 'Password123!',
            'rol_id' => $admin->id,
        ]);

        User::create([
            'name' => 'Mesero Demo',
            'email' => 'mesero@bonappetit.test',
            'password' => 'Password123!',
            'rol_id' => $mesero->id,
        ]);

        User::create([
            'name' => 'Cajero Demo',
            'email' => 'cajero@bonappetit.test',
            'password' => 'Password123!',
            'rol_id' => $cajero->id,
        ]);
    }
}
