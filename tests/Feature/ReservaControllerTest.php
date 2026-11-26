<?php

use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\ReservaService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CatalogosSeeder::class);
    $this->mesa = Mesa::create([
        'numero' => 1,
        'capacidad' => 4,
        'estado_mesa_id' => EstadoMesa::where('codigo', 'libre')->value('id'),
    ]);
    $this->manana = date('Y-m-d', strtotime(ReservaService::hoy().' +1 day'));
    $this->datos = [
        'nombre' => 'Familia Rojas',
        'personas' => 4,
        'fecha' => $this->manana,
        'hora' => '19:00',
        'mesa_id' => $this->mesa->id,
    ];
});

it('el mesero crea una reserva pendiente', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $this->postJson('/api/reservas', $this->datos)
        ->assertCreated()
        ->assertJsonPath('data.estado', 'pendiente')
        ->assertJsonPath('data.mesa.numero', 1)
        ->assertJsonPath('data.fecha', $this->manana);
});

it('el cajero puede ver las reservas pero no crearlas ni cambiarlas', function () {
    $reserva = Reserva::create($this->datos + ['user_id' => usuarioConRol('mesero')->id, 'estado' => 'pendiente']);

    Sanctum::actingAs(usuarioConRol('cajero'));

    $this->getJson('/api/reservas')->assertOk()->assertJsonCount(1, 'data');
    $this->postJson('/api/reservas', $this->datos)->assertForbidden();
    $this->putJson("/api/reservas/{$reserva->id}", ['estado' => 'confirmada'])->assertForbidden();
});

it('solo el administrador elimina reservas', function () {
    $reserva = Reserva::create($this->datos + ['user_id' => usuarioConRol('mesero')->id, 'estado' => 'pendiente']);

    Sanctum::actingAs(usuarioConRol('mesero'));
    $this->deleteJson("/api/reservas/{$reserva->id}")->assertForbidden();

    Sanctum::actingAs(usuarioConRol('administrador'));
    $this->deleteJson("/api/reservas/{$reserva->id}")->assertNoContent();
});

it('rechaza una reserva que choca con otra en la misma mesa (409)', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $this->postJson('/api/reservas', $this->datos)->assertCreated();

    $this->postJson('/api/reservas', array_merge($this->datos, ['nombre' => 'Otro', 'hora' => '20:30']))
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'reserva_solapada');

    // Dos horas después de distancia ya no choca.
    $this->postJson('/api/reservas', array_merge($this->datos, ['nombre' => 'Otro', 'hora' => '21:00']))
        ->assertCreated();
});

it('una reserva cancelada libera el horario', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $id = $this->postJson('/api/reservas', $this->datos)->json('data.id');
    $this->putJson("/api/reservas/{$id}", ['estado' => 'cancelada'])->assertOk();

    $this->postJson('/api/reservas', array_merge($this->datos, ['nombre' => 'Otro']))->assertCreated();
});

it('valida capacidad de la mesa, fecha pasada y campos obligatorios', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $this->postJson('/api/reservas', array_merge($this->datos, ['personas' => 9]))
        ->assertStatus(422)
        ->assertJsonPath('codigo', 'capacidad_insuficiente');

    $this->postJson('/api/reservas', array_merge($this->datos, ['fecha' => '2020-01-01']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('fecha');

    $this->postJson('/api/reservas', ['nombre' => ''])->assertStatus(422);
});

it('una reserva cumplida o cancelada ya no se puede modificar (409)', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $id = $this->postJson('/api/reservas', $this->datos)->json('data.id');
    $this->putJson("/api/reservas/{$id}", ['estado' => 'cumplida'])->assertOk();

    $this->putJson("/api/reservas/{$id}", ['nombre' => 'Cambio'])
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'reserva_cerrada');
});

it('filtra las reservas por fecha', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $this->postJson('/api/reservas', $this->datos)->assertCreated();
    $this->postJson('/api/reservas', array_merge($this->datos, [
        'fecha' => date('Y-m-d', strtotime($this->manana.' +1 day')),
    ]))->assertCreated();

    $this->getJson('/api/reservas?fecha='.$this->manana)->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/reservas')->assertOk()->assertJsonCount(2, 'data');
});
