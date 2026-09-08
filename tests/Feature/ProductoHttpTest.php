<?php

use App\Models\Categoria;
use App\Models\Producto;

it('lista productos paginados', function () {
    Producto::factory()->count(3)->create();

    $this->getJson('/api/productos')->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
});

it('devuelve 404 al ver un producto inexistente', function () {
    $this->getJson('/api/productos/999999')->assertStatus(404);
});

it('crea un producto y responde 201 con Location', function () {
    $categoria = Categoria::factory()->create();

    $response = $this->postJson('/api/productos', [
        'nombre' => 'Batido de fresa',
        'categoria_id' => $categoria->id,
        'precio' => 2500,
    ]);

    $response->assertCreated()->assertHeader('Location');
    expect(Producto::where('nombre', 'Batido de fresa')->exists())->toBeTrue();
});

it('rechaza crear un producto sin categoría con 422', function () {
    $this->postJson('/api/productos', ['nombre' => 'Sin categoría', 'precio' => 1000])
        ->assertStatus(422);
});

it('rechaza un precio negativo o en cero con 422', function () {
    $categoria = Categoria::factory()->create();

    $this->postJson('/api/productos', [
        'nombre' => 'Precio inválido',
        'categoria_id' => $categoria->id,
        'precio' => 0,
    ])->assertStatus(422);
});

it('rechaza un nombre de producto repetido con 422', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->create(['nombre' => 'Nachos con queso']);

    $this->postJson('/api/productos', [
        'nombre' => 'Nachos con queso',
        'categoria_id' => $categoria->id,
        'precio' => 3200,
    ])->assertStatus(422);
});

it('acepta una fecha de disponibilidad válida', function () {
    $categoria = Categoria::factory()->create();

    $response = $this->postJson('/api/productos', [
        'nombre' => 'Especial de temporada',
        'categoria_id' => $categoria->id,
        'precio' => 4000,
        'disponible_desde' => '2026-12-01',
    ]);

    $response->assertCreated()->assertJsonPath('data.disponible_desde', '2026-12-01');
});

it('rechaza una fecha de disponibilidad con formato inválido con 422', function () {
    $categoria = Categoria::factory()->create();

    $this->postJson('/api/productos', [
        'nombre' => 'Fecha rota',
        'categoria_id' => $categoria->id,
        'precio' => 1500,
        'disponible_desde' => '31 de febrero del 2026',
    ])->assertStatus(422);
});

it('actualiza un producto', function () {
    $producto = Producto::factory()->create();

    $response = $this->putJson("/api/productos/{$producto->id}", [
        'nombre' => 'Nombre editado',
        'categoria_id' => $producto->categoria_id,
        'precio' => $producto->precio,
    ]);

    $response->assertOk()->assertJsonPath('data.nombre', 'Nombre editado');
});

it('elimina un producto', function () {
    $producto = Producto::factory()->create();

    $this->deleteJson("/api/productos/{$producto->id}")->assertNoContent();
    expect(Producto::find($producto->id))->toBeNull();
});
