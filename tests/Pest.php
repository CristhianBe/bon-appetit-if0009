<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
}

function usuarioConRol(string $nombre): User
{
    $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

    $usuario = User::factory()->create();
    $usuario->assignRole($rol);

    return $usuario;
}
