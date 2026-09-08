<?php

use App\Exceptions\ReglaNegocioException;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use App\Services\ComandaService;
use Database\Seeders\CatalogosSeeder;

// Una prueba por cada regla de negocio implementada en ComandaService (ver docs/lab04-negocio.md).
// ComandaService no recibe ninguna dependencia por constructor (a diferencia de PedidoService en
// el otro proyecto, que sí recibe InventarioService), así que acá no hace falta ningún doble:
// se prueba directo contra la base de datos en memoria (ver tests/Pest.php: RefreshDatabase).

// El servicio asume que los catálogos (estados_mesa, estados_comanda) YA existen — como pasaría
// en producción, donde CatalogosSeeder corre una sola vez al desplegar — y usa firstOrFail() a
// propósito: si un código de estado no existe, es un bug real y debe fallar, no crear una fila
// nueva en silencio. Por eso cada prueba siembra los catálogos igual que lo haría producción.
beforeEach(fn () => $this->seed(CatalogosSeeder::class));

it('abre una comanda, calcula el total y ocupa la mesa (camino feliz)', function () {
    $mesa = Mesa::factory()->create(); // nace en estado "libre"
    $producto = Producto::factory()->create(['precio' => 1000]);
    $user = User::factory()->create();

    $comanda = app(ComandaService::class)->crear([
        'mesa_id' => $mesa->id,
        'user_id' => $user->id,
        'detalles' => [['producto_id' => $producto->id, 'cantidad' => 3]],
    ]);

    expect((float) $comanda->total)->toBe(3000.0);
    expect($mesa->fresh()->estado->codigo)->toBe('ocupada');
});

it('no permite abrir una comanda en una mesa que no está libre', function () {
    $mesa = Mesa::factory()->ocupada()->create();
    $producto = Producto::factory()->create();
    $user = User::factory()->create();

    expect(fn () => app(ComandaService::class)->crear([
        'mesa_id' => $mesa->id,
        'user_id' => $user->id,
        'detalles' => [['producto_id' => $producto->id, 'cantidad' => 1]],
    ]))->toThrow(ReglaNegocioException::class);
});

it('no permite cerrar una comanda sin detalle', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('abierta')->id]);

    expect(fn () => app(ComandaService::class)->cerrar($comanda))
        ->toThrow(ReglaNegocioException::class);
});

it('no permite cerrar una comanda que ya está cerrada', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('cerrada')->id]);

    expect(fn () => app(ComandaService::class)->cerrar($comanda))
        ->toThrow(ReglaNegocioException::class);
});

it('cierra una comanda sin descuento cuando el total no supera el umbral, y libera la mesa', function () {
    $mesa = Mesa::factory()->ocupada()->create();
    $comanda = Comanda::factory()->create([
        'mesa_id' => $mesa->id,
        'estado_comanda_id' => estadoComanda('abierta')->id,
        'total' => 15000,
    ]);
    $comanda->detalles()->create([
        'producto_id' => Producto::factory()->create()->id,
        'cantidad' => 1,
        'precio_unitario' => 15000,
    ]);

    $cerrada = app(ComandaService::class)->cerrar($comanda);

    expect((float) $cerrada->total)->toBe(15000.0); // sin descuento: no pasa el umbral de 20 000
    expect($cerrada->estado->codigo)->toBe('cerrada');
    expect($cerrada->cerrada_en)->not->toBeNull();
    expect($mesa->fresh()->estado->codigo)->toBe('libre');
});

it('aplica el 10% de descuento al cerrar una comanda que supera el umbral', function () {
    $comanda = Comanda::factory()->create([
        'estado_comanda_id' => estadoComanda('abierta')->id,
        'total' => 30000,
    ]);
    $comanda->detalles()->create([
        'producto_id' => Producto::factory()->create()->id,
        'cantidad' => 1,
        'precio_unitario' => 30000,
    ]);

    $cerrada = app(ComandaService::class)->cerrar($comanda);

    expect((float) $cerrada->total)->toBe(27000.0); // 30 000 - 10% = 27 000
});

it('no permite eliminar una comanda ya cerrada', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('cerrada')->id]);

    expect(fn () => app(ComandaService::class)->eliminar($comanda))
        ->toThrow(ReglaNegocioException::class);

    expect(Comanda::find($comanda->id))->not->toBeNull();
});

it('elimina una comanda que no está cerrada', function () {
    $comanda = Comanda::factory()->create(['estado_comanda_id' => estadoComanda('abierta')->id]);

    app(ComandaService::class)->eliminar($comanda);

    expect(Comanda::find($comanda->id))->toBeNull();
});
