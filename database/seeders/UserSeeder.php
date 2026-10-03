<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@bonappetit.test',
            'password' => 'Password123!',
        ]);
        $admin->assignRole('administrador');

        $mesero = User::create([
            'name' => 'Mesero Demo',
            'email' => 'mesero@bonappetit.test',
            'password' => 'Password123!',
        ]);
        $mesero->assignRole('mesero');

        $cajero = User::create([
            'name' => 'Cajero Demo',
            'email' => 'cajero@bonappetit.test',
            'password' => 'Password123!',
        ]);
        $cajero->assignRole('cajero');
    }
}
