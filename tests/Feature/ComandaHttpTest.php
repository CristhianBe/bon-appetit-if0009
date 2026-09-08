<?php

use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Database\Seeders\CatalogosSeeder;

// Estos tests pegan a las rutas reales (routes/api.php) en vez de llamar directo a una clase PHP,
// para verificar que ruta + validación + controlador + servicio responden como se espera:
// códigos 200/201/204/404/409/422 y la forma del JSON. No hay auth:sanctum todavía en estas
// rutas (eso es del Laboratorio 6), así que no hace falta ningún token acá.

beforeEach(fn () => $this->seed(CatalogosSeeder::class));

it('lista comandas paginadas', function () {
    Comanda::factory()->count(3)->create();

    $this->getJson('/api/comandas')->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
});

it('respeta el tope de 100 en per_page', function () {
    $this->getJson('/api/comandas?per_page=500')->assertOk()->assertJsonPath('meta.per_page', 100);
});

it('devuelve 404 al ver una comanda inexistente', function () {
    $this->getJson('/api/comandas/999999')->assertStatus(404);
});

it('abre una comanda y responde 201 con Location', function () {
    $mesa = Mesa::factory()->create();
    $producto = Producto::factory()->create();
    $user = User::factory()->create();

    $response = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $user->id,
        'detalles' => [['producto_id' => $producto->id, 'cantidad' => 2]],
    ]);

    $response->assertCreated()->assertHeader('Location');
    expect(Comanda::where('mesa_id', $mesa->id)->exists())->toBeTrue();
});

it('rechaza abrir una comanda sin detalles con 422', function () {
    $mesa = Mesa::factory()->create();
    $user = User::factory()->create();

    $this->postJson('/api/comandas', ['mesa_id' => $mesa->id, 'user_id' => $user->id])
        ->assertStatus(422);
});

it('rechaza abrir una comanda en una mesa ocupada con 409', function () {
    $mesa = Mesa::factory()->ocupada()->create();
    $producto = Producto::factory()->create();
    $user = User::factory()->create();

    $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $user->id,
        'detalles' => [['producto_id' => $producto->id, 'cantidad' => 1]],
    ])->assertStatus(409);
});

it('actualiza la mesa de una comanda', function () {
    // PUT reemplaza el recurso completo (a diferencia de PATCH): igual que en GuardarPedidoRequest
    // del otro proyecto, mesa_id y user_id son obligatorios en cada PUT, no parciales.
    $comanda = Comanda::factory()->create();
    $otraMesa = Mesa::factory()->create();

    $response = $this->putJson("/api/comandas/{$comanda->id}", [
        'mesa_id' => $otraMesa->id,
        'user_id' => $comanda->user_id,
    ]);

    $response->assertOk()->assertJsonPath('data.mesa.id', $otraMesa->id);
});

it('elimina una comanda abierta con 204', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('abierta')->id]);

    $this->deleteJson("/api/comandas/{$comanda->id}")->assertNoContent();
    expect(Comanda::find($comanda->id))->toBeNull();
});

it('rechaza eliminar una comanda cerrada con 409', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('cerrada')->id]);

    $this->deleteJson("/api/comandas/{$comanda->id}")->assertStatus(409);
    expect(Comanda::find($comanda->id))->not->toBeNull();
});

it('cierra una comanda con detalle y libera la mesa', function () {
    $mesa = Mesa::factory()->ocupada()->create();
    $comanda = Comanda::factory()->create([
        'mesa_id' => $mesa->id,
        'estado_comanda_id' => estadoComanda('abierta')->id,
        'total' => 5000,
    ]);
    $comanda->detalles()->create([
        'producto_id' => Producto::factory()->create()->id,
        'cantidad' => 1,
        'precio_unitario' => 5000,
    ]);

    $response = $this->postJson("/api/comandas/{$comanda->id}/cerrar");

    $response->assertOk()->assertJsonPath('estado.codigo', 'cerrada');
    expect($mesa->fresh()->estado->codigo)->toBe('libre');
});
