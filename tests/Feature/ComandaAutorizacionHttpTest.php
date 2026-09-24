<?php

use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\User;
use App\Services\ComandaService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// A diferencia de ComandaAutorizacionTest.php (que prueba el servicio directo), este archivo
// prueba específicamente el "acceso permitido/denegado por rol" pegándole a las rutas reales
// (routes/api.php), para comprobar que la Capa 1 (ComandaController) también hace su parte.

beforeEach(fn () => $this->seed(CatalogosSeeder::class));

function crearMesaLibreHttp(int $numero): Mesa
{
    $estado = EstadoMesa::where('codigo', 'libre')->first();

    return Mesa::create(['numero' => $numero, 'capacidad' => 4, 'estado_mesa_id' => $estado->id]);
}

it('un mesero ve su propia comanda con 200', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => crearMesaLibreHttp(101)->id, 'user_id' => $mesero->id], $mesero);

    Sanctum::actingAs($mesero);

    $this->getJson("/api/comandas/{$comanda->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $comanda->id);
});

it('un mesero NO ve la comanda de otro mesero: 403', function () {
    $dueño = usuarioConRol('mesero');
    $otroMesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => crearMesaLibreHttp(102)->id, 'user_id' => $dueño->id], $dueño);

    Sanctum::actingAs($otroMesero);

    $this->getJson("/api/comandas/{$comanda->id}")->assertStatus(403);
});

it('cajero ve cualquier comanda (rol de solo lectura) con 200', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => crearMesaLibreHttp(103)->id, 'user_id' => $mesero->id], $mesero);

    Sanctum::actingAs(usuarioConRol('cajero'));

    $this->getJson("/api/comandas/{$comanda->id}")->assertOk();
});

it('cajero NO puede abrir una comanda: 403', function () {
    $cajero = usuarioConRol('cajero');
    Sanctum::actingAs($cajero);

    $this->postJson('/api/comandas', [
        'mesa_id' => crearMesaLibreHttp(104)->id,
        'user_id' => $cajero->id,
    ])->assertStatus(403);
});

it('administrador puede eliminar la comanda de CUALQUIER mesero con 204', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => crearMesaLibreHttp(105)->id, 'user_id' => $mesero->id], $mesero);

    Sanctum::actingAs(usuarioConRol('administrador'));

    $this->deleteJson("/api/comandas/{$comanda->id}")->assertNoContent();
});

it('un usuario autenticado sin ningún rol recibe 403 al ver una comanda', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => crearMesaLibreHttp(106)->id, 'user_id' => $mesero->id], $mesero);

    // Sin usuarioConRol(): usuario válido (token real), pero sin fila en model_has_roles.
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/comandas/{$comanda->id}")->assertStatus(403);
});
