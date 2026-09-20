<?php

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use App\Services\ComandaService;
use App\Services\ProductoService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CatalogosSeeder::class);

    $this->categoria = Categoria::create(['nombre' => 'Platos fuertes']);
    $this->mesero = User::factory()->create();

    $this->comandaService = app(ComandaService::class);
    $this->productoService = app(ProductoService::class);
});

test('no se puede cerrar una comanda sin detalle', function () {
    $estadoLibre = EstadoMesa::where('codigo', 'libre')->first();
    $mesa = Mesa::create(['numero' => 1, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);

    $comanda = $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);

    $this->comandaService->cerrar($comanda);
})->throws(ReglaNegocioException::class, 'No se puede cerrar una comanda sin al menos un producto.');

test('no se puede eliminar un producto que ya fue vendido', function () {
    $estadoLibre = EstadoMesa::where('codigo', 'libre')->first();
    $mesa = Mesa::create(['numero' => 2, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);

    $producto = Producto::create([
        'nombre' => 'Casado con pollo',
        'precio' => 4500,
        'categoria_id' => $this->categoria->id,
        'disponible' => true,
    ]);

    $comanda = $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);

    $this->comandaService->agregarDetalle($comanda, [
        'producto_id' => $producto->id,
        'cantidad' => 1,
    ]);

    $this->productoService->eliminar($producto);
})->throws(ReglaNegocioException::class, 'No se puede eliminar un producto que ya fue vendido en alguna comanda.');

test('se aplica un descuento del 10% cuando el total supera el umbral', function () {
    $estadoLibre = EstadoMesa::where('codigo', 'libre')->first();
    $mesa = Mesa::create(['numero' => 3, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);

    $producto = Producto::create([
        'nombre' => 'Arroz con camarones',
        'precio' => 5200,
        'categoria_id' => $this->categoria->id,
        'disponible' => true,
    ]);

    $comanda = $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);

    // 4 unidades x 5200 = 20800, supera el umbral de 15000
    $this->comandaService->agregarDetalle($comanda, [
        'producto_id' => $producto->id,
        'cantidad' => 4,
    ]);

    $comandaCerrada = $this->comandaService->cerrar($comanda);

    expect((float) $comandaCerrada->total)->toBe(20800 * 0.9);
});

test('no se puede abrir una comanda en una mesa que no esta libre', function () {
    $estadoOcupada = EstadoMesa::where('codigo', 'ocupada')->first();
    $mesa = Mesa::create(['numero' => 4, 'capacidad' => 4, 'estado_mesa_id' => $estadoOcupada->id]);

    $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);
})->throws(ReglaNegocioException::class, 'No se puede abrir una comanda en una mesa que no está libre.');

test('no se puede agregar a la comanda un producto no disponible', function () {
    $estadoLibre = EstadoMesa::where('codigo', 'libre')->first();
    $mesa = Mesa::create(['numero' => 5, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);

    $producto = Producto::create([
        'nombre' => 'Cheesecake',
        'precio' => 2500,
        'categoria_id' => $this->categoria->id,
        'disponible' => false,
    ]);

    $comanda = $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);

    $this->comandaService->agregarDetalle($comanda, [
        'producto_id' => $producto->id,
        'cantidad' => 1,
    ]);
})->throws(ReglaNegocioException::class, 'No se puede agregar un producto que no está disponible.');

test('no se puede modificar el detalle de una comanda ya cerrada', function () {
    $estadoLibre = EstadoMesa::where('codigo', 'libre')->first();
    $mesa = Mesa::create(['numero' => 6, 'capacidad' => 4, 'estado_mesa_id' => $estadoLibre->id]);

    $producto = Producto::create([
        'nombre' => 'Refresco natural',
        'precio' => 1200,
        'categoria_id' => $this->categoria->id,
        'disponible' => true,
    ]);

    $comanda = $this->comandaService->abrir([
        'mesa_id' => $mesa->id,
        'user_id' => $this->mesero->id,
    ]);

    $detalle = $this->comandaService->agregarDetalle($comanda, [
        'producto_id' => $producto->id,
        'cantidad' => 1,
    ]);

    $comandaCerrada = $this->comandaService->cerrar($comanda);

    $this->comandaService->quitarDetalle($comandaCerrada, $detalle);
})->throws(ReglaNegocioException::class, 'No se puede modificar el detalle de una comanda ya cerrada.');