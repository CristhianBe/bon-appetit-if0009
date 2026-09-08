<?php

use App\Models\Categoria;
use App\Models\Producto;

it('lista categorías paginadas', function () {
    Categoria::factory()->count(3)->create();

    $this->getJson('/api/categorias')->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
});

it('crea una categoría y responde 201 con Location', function () {
    $response = $this->postJson('/api/categorias', ['nombre' => 'Cafetería']);

    $response->assertCreated()->assertHeader('Location');
    expect(Categoria::where('nombre', 'Cafetería')->exists())->toBeTrue();
});

it('rechaza una categoría sin nombre con 422', function () {
    $this->postJson('/api/categorias', [])->assertStatus(422);
});

it('elimina una categoría sin productos con 204', function () {
    $categoria = Categoria::factory()->create();

    $this->deleteJson("/api/categorias/{$categoria->id}")->assertNoContent();
    expect(Categoria::find($categoria->id))->toBeNull();
});

it('rechaza eliminar una categoría con productos asociados con 409', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->for($categoria)->create();

    $this->deleteJson("/api/categorias/{$categoria->id}")->assertStatus(409);
    expect(Categoria::find($categoria->id))->not->toBeNull();
});
