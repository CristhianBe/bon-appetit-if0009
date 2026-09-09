<?php

use App\Models\Categoria;
use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->estadoLibre = EstadoMesa::create(['codigo' => 'libre', 'nombre' => 'Libre']);
    $this->estadoOcupada = EstadoMesa::create(['codigo' => 'ocupada', 'nombre' => 'Ocupada']);
    EstadoComanda::create(['codigo' => 'abierta', 'nombre' => 'Abierta']);
    EstadoComanda::create(['codigo' => 'cerrada', 'nombre' => 'Cerrada']);

    $this->categoria = Categoria::create(['nombre' => 'Platos fuertes']);
    $this->mesero = User::factory()->create();
});

test('abre una comanda por la api y ocupa la mesa', function () {
    $mesa = Mesa::create(['numero' => 10, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoLibre->id]);

    $comanda = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->assertCreated()->json('data');

    expect($comanda['mesa']['estado']['codigo'])->toBe('ocupada');

    $mesa->refresh();
    expect($mesa->estado->codigo)->toBe('ocupada');
});

test('no se puede abrir una comanda en una mesa ocupada', function () {
    $mesa = Mesa::create(['numero' => 11, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoOcupada->id]);

    $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->assertStatus(409)->assertJsonPath('codigo', 'mesa_no_disponible');
});

test('agrega detalle, cierra la comanda con descuento y libera la mesa', function () {
    $mesa = Mesa::create(['numero' => 12, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoLibre->id]);
    $producto = Producto::create([
        'nombre' => 'Arroz con camarones',
        'precio' => 5200,
        'categoria_id' => $this->categoria->id,
        'disponible' => true,
    ]);

    $comanda = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->json('data');

    // 4 unidades x 5200 = 20800, supera el umbral de 15000 -> 10% de descuento
    $this->postJson('/api/DetalleComandas', [
        'comanda_id' => $comanda['id'],
        'producto_id' => $producto->id,
        'cantidad' => 4,
    ])->assertCreated();

    $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->first();

    $cerrada = $this->putJson("/api/comandas/{$comanda['id']}", [
        'estado_comanda_id' => $estadoCerrada->id,
    ])->assertOk()->json('data');

    expect((float) $cerrada['total'])->toBe(20800 * 0.9);
    expect($cerrada['mesa']['estado']['codigo'])->toBe('libre');
});

test('no se puede cerrar una comanda sin detalle', function () {
    $mesa = Mesa::create(['numero' => 13, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoLibre->id]);

    $comanda = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->json('data');

    $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->first();

    $this->putJson("/api/comandas/{$comanda['id']}", [
        'estado_comanda_id' => $estadoCerrada->id,
    ])->assertStatus(422)->assertJsonPath('codigo', 'comanda_sin_detalle');
});

test('no se puede modificar el detalle de una comanda ya cerrada', function () {
    $mesa = Mesa::create(['numero' => 14, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoLibre->id]);
    $producto = Producto::create([
        'nombre' => 'Refresco natural',
        'precio' => 1200,
        'categoria_id' => $this->categoria->id,
        'disponible' => true,
    ]);

    $comanda = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->json('data');

    $detalle = $this->postJson('/api/DetalleComandas', [
        'comanda_id' => $comanda['id'],
        'producto_id' => $producto->id,
        'cantidad' => 1,
    ])->json('data');

    $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->first();
    $this->putJson("/api/comandas/{$comanda['id']}", ['estado_comanda_id' => $estadoCerrada->id])->assertOk();

    $this->putJson("/api/DetalleComandas/{$detalle['id']}", ['cantidad' => 2])
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'comanda_cerrada_no_editable');

    $this->deleteJson("/api/DetalleComandas/{$detalle['id']}")
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'comanda_cerrada_no_editable');
});

test('no se puede agregar un producto no disponible a una comanda', function () {
    $mesa = Mesa::create(['numero' => 15, 'capacidad' => 4, 'estado_mesa_id' => $this->estadoLibre->id]);
    $producto = Producto::create([
        'nombre' => 'Cheesecake',
        'precio' => 2500,
        'categoria_id' => $this->categoria->id,
        'disponible' => false,
    ]);

    $comanda = $this->postJson('/api/comandas', [
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ])->json('data');

    $this->postJson('/api/DetalleComandas', [
        'comanda_id' => $comanda['id'],
        'producto_id' => $producto->id,
        'cantidad' => 1,
    ])->assertStatus(422)->assertJsonPath('codigo', 'producto_no_disponible');
});
