<?php

use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
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

// RefreshDatabase: cada prueba corre las migraciones sobre la base de datos en memoria
// (ver phpunit.xml: DB_DATABASE=:memory:) y la limpia entre pruebas. Sin esto, las pruebas de
// Feature no tendrían ni tablas ni datos aislados de una prueba a otra.
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
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

// Helpers compartidos por las pruebas de Comanda: los estados son catálogos (App\Models\EstadoMesa
// / EstadoComanda), no tienen factory propia ni datos por defecto — firstOrCreate() reutiliza la
// fila si ya existe en esta base de datos de prueba, o la crea si hace falta.
function estadoMesa(string $codigo): EstadoMesa
{
    return EstadoMesa::firstOrCreate(['codigo' => $codigo], ['nombre' => ucfirst($codigo)]);
}

function estadoComanda(string $codigo): EstadoComanda
{
    return EstadoComanda::firstOrCreate(
        ['codigo' => $codigo],
        ['nombre' => ucfirst(str_replace('_', ' ', $codigo))]
    );
}
