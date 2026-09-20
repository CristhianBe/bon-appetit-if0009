<?php

use App\Models\Categoria;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lista, crea, muestra y actualiza categorias por la api', function () {
    $this->getJson('/api/categories')->assertOk();

    $creada = $this->postJson('/api/categories', ['nombre' => 'Postres'])
        ->assertCreated()
        ->json('data');

    $this->getJson("/api/categories/{$creada['id']}")
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Postres');

    $this->putJson("/api/categories/{$creada['id']}", ['nombre' => 'Postres y repostería'])
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Postres y repostería');
});

test('rechaza crear una categoria sin nombre', function () {
    $this->postJson('/api/categories', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('nombre');
});

test('no se puede eliminar una categoria con productos asociados', function () {
    $categoria = Categoria::create(['nombre' => 'Bebidas']);
    Producto::create([
        'nombre' => 'Café',
        'precio' => 1500,
        'categoria_id' => $categoria->id,
        'disponible' => true,
    ]);

    $this->deleteJson("/api/categories/{$categoria->id}")
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'categoria_con_productos');
});

test('crea, filtra y elimina productos por la api', function () {
    $categoria = Categoria::create(['nombre' => 'Entradas']);

    $creado = $this->postJson('/api/productos', [
        'nombre' => 'Ceviche',
        'precio' => 3200,
        'categoria_id' => $categoria->id,
    ])->assertCreated()->json('data');

    $this->getJson('/api/productos?q=Ceviche')
        ->assertOk()
        ->assertJsonPath('data.0.nombre', 'Ceviche');

    $this->putJson("/api/productos/{$creado['id']}", ['precio' => 3500])
        ->assertOk()
        ->assertJsonPath('data.precio', '3500.00');

    $this->deleteJson("/api/productos/{$creado['id']}")->assertNoContent();
});

test('no se puede eliminar un producto ya vendido en una comanda', function () {
    $categoria = Categoria::create(['nombre' => 'Platos fuertes']);
    $producto = Producto::create([
        'nombre' => 'Casado',
        'precio' => 4000,
        'categoria_id' => $categoria->id,
        'disponible' => true,
    ]);

    $estadoLibre = EstadoMesa::create(['codigo' => 'libre', 'nombre' => 'Libre']);
    $estadoAbierta = EstadoComanda::create(['codigo' => 'abierta', 'nombre' => 'Abierta']);
    $mesa = Mesa::create(['numero' => 1, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);
    $mesero = User::factory()->create();

    $comanda = Comanda::create([
        'mesa_id' => $mesa->id,
        'user_id' => $mesero->id,
        'estado_comanda_id' => $estadoAbierta->id,
    ]);

    DetalleComanda::create([
        'comanda_id' => $comanda->id,
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => $producto->precio,
    ]);

    $this->deleteJson("/api/productos/{$producto->id}")
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'producto_con_dependencias');
});
