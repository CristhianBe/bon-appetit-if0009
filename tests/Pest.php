<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

// Helper compartido por las pruebas del Laboratorio 6: crea un usuario nuevo y le asigna un
// rol (administrador, mesero o cajero), creando el rol si todavía no existe en esta base de
// datos de prueba (firstOrCreate evita el error de "unique" si dos pruebas piden el mismo rol).
function usuarioConRol(string $nombre): User
{
    // guard_name explícito (no se deja en el default): Sanctum::actingAs(), cuando ya se
    // llamó antes en el mismo test, deja config('auth.defaults.guard') en "sanctum" — sin
    // esto, un rol creado en ese momento terminaría con el guard equivocado (ver
    // RoleController::store() para el detalle completo del porqué).
    $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

    $usuario = User::factory()->create();
    $usuario->assignRole($rol);

    return $usuario;
}
