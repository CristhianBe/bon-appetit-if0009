<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = app()->environment(['local', 'testing'])
            ? 'Password123!'
            : (config('app.seed_demo_password') ?: Str::random(32));

        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@bonappetit.test',
            'password' => $password,
        ]);
        $admin->assignRole('administrador');

        $mesero = User::create([
            'name' => 'Mesero Demo',
            'email' => 'mesero@bonappetit.test',
            'password' => $password,
        ]);
        $mesero->assignRole('mesero');

        $cajero = User::create([
            'name' => 'Cajero Demo',
            'email' => 'cajero@bonappetit.test',
            'password' => $password,
        ]);
        $cajero->assignRole('cajero');
    }
}
