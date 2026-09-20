<?php

use App\Models\EstadoMesa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('crud completo de mesas por la api', function () {
    $estado = EstadoMesa::create(['codigo' => 'libre', 'nombre' => 'Libre']);

    $this->getJson('/api/mesas')->assertOk();

    $mesa = $this->postJson('/api/mesas', [
        'numero' => 20,
        'capacidad' => 6,
        'estado_mesa_id' => $estado->id,
    ])->assertCreated()->json('data');

    $this->getJson("/api/mesas/{$mesa['id']}")->assertOk();
    $this->putJson("/api/mesas/{$mesa['id']}", ['capacidad' => 8])
        ->assertOk()->assertJsonPath('data.capacidad', 8);
    $this->deleteJson("/api/mesas/{$mesa['id']}")->assertNoContent();
});

test('crud completo de estados de comanda por la api', function () {
    $this->getJson('/api/EstadoComandas')->assertOk();

    $estado = $this->postJson('/api/EstadoComandas', ['codigo' => 'lista', 'nombre' => 'Lista'])
        ->assertCreated()->json('data');

    $this->putJson("/api/EstadoComandas/{$estado['id']}", ['nombre' => 'Lista para servir'])
        ->assertOk()->assertJsonPath('data.nombre', 'Lista para servir');

    $this->deleteJson("/api/EstadoComandas/{$estado['id']}")->assertNoContent();
});

test('crud completo de metodos de pago por la api', function () {
    $metodo = $this->postJson('/api/metodos-pago', ['codigo' => 'efectivo', 'nombre' => 'Efectivo'])
        ->assertCreated()->json('data');

    $this->getJson('/api/metodos-pago')->assertOk();
    $this->deleteJson("/api/metodos-pago/{$metodo['id']}")->assertNoContent();
});

test('crud completo de unidades de medida por la api', function () {
    $unidad = $this->postJson('/api/unidad-medida', ['codigo' => 'kg', 'nombre' => 'Kilogramo'])
        ->assertCreated()->json('data');

    $this->getJson('/api/unidad-medida')->assertOk();
    $this->deleteJson("/api/unidad-medida/{$unidad['id']}")->assertNoContent();
});

test('crud completo de roles por la api', function () {
    $role = $this->postJson('/api/roles', ['nombre' => 'Mesero'])
        ->assertCreated()->json('data');

    $this->getJson('/api/roles')->assertOk();
    $this->putJson("/api/roles/{$role['id']}", ['descripcion' => 'Atiende mesas'])
        ->assertOk()->assertJsonPath('data.descripcion', 'Atiende mesas');
    $this->deleteJson("/api/roles/{$role['id']}")->assertNoContent();
});

test('crud completo de usuarios por la api sin exponer la contraseña', function () {
    $user = $this->postJson('/api/users', [
        'name' => 'Ana Mesera',
        'email' => 'ana@bonappetit.test',
        'password' => 'password123',
    ])->assertCreated()->json('data');

    expect($user)->not->toHaveKey('password');

    $this->getJson('/api/users')->assertOk();
    $this->putJson("/api/users/{$user['id']}", ['name' => 'Ana M.'])
        ->assertOk()->assertJsonPath('data.name', 'Ana M.');
    $this->deleteJson("/api/users/{$user['id']}")->assertNoContent();
});
