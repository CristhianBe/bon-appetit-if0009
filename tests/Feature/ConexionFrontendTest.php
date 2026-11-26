<?php

use App\Models\Categoria;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\MetodoPago;
use App\Models\Producto;
use App\Models\User;
use App\Services\ComandaService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CatalogosSeeder::class);
    $this->libre = EstadoMesa::where('codigo', 'libre')->first();
    $this->categoria = Categoria::create(['nombre' => 'Platos fuertes']);
    $this->producto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Casado',
        'precio' => 5000,
        'disponible' => true,
    ]);
});

function mesaLibreFront(int $numero, int $capacidad = 4): Mesa
{
    return Mesa::create([
        'numero' => $numero,
        'capacidad' => $capacidad,
        'estado_mesa_id' => EstadoMesa::where('codigo', 'libre')->value('id'),
    ]);
}

function comandaConProductoFront(User $mesero, Mesa $mesa, int $cantidad = 2)
{
    $servicio = app(ComandaService::class);
    $comanda = $servicio->abrir(['mesa_id' => $mesa->id, 'user_id' => $mesero->id], $mesero);
    $servicio->agregarDetalle($comanda, ['producto_id' => Producto::first()->id, 'cantidad' => $cantidad], $mesero);

    return $comanda->refresh();
}

it('solo el administrador gestiona usuarios y roles', function (string $rol, int $esperado) {
    Sanctum::actingAs(usuarioConRol($rol));

    $this->getJson('/api/usuarios')->assertStatus($esperado);
    $this->getJson('/api/roles')->assertStatus($esperado);
})->with([
    'administrador' => ['administrador', 200],
    'mesero' => ['mesero', 403],
    'cajero' => ['cajero', 403],
]);

it('solo el administrador crea platillos y categorías; los demás roles solo leen', function () {
    Sanctum::actingAs(usuarioConRol('mesero'));

    $this->getJson('/api/categorias')->assertOk();
    $this->postJson('/api/categorias', ['nombre' => 'Postres'])->assertForbidden();
    $this->postJson('/api/productos', [
        'nombre' => 'Flan', 'precio' => 2500, 'categoria_id' => $this->categoria->id,
    ])->assertForbidden();

    Sanctum::actingAs(usuarioConRol('administrador'));

    $this->postJson('/api/categorias', ['nombre' => 'Postres'])->assertCreated();
    $this->postJson('/api/productos', [
        'nombre' => 'Flan', 'precio' => 2500, 'categoria_id' => $this->categoria->id,
    ])->assertCreated();
});

it('el login y /api/user devuelven el rol del usuario', function () {
    $cajero = usuarioConRol('cajero');
    $cajero->update(['password' => 'Password123!']);

    $token = $this->postJson('/api/login', ['email' => $cajero->email, 'password' => 'Password123!'])
        ->assertOk()
        ->assertJsonPath('user.rol', 'cajero')
        ->json('token');

    $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('rol', 'cajero');
});

it('el cajero cobra una comanda: la cierra, registra el pago y libera la mesa', function () {
    $mesero = usuarioConRol('mesero');
    $mesa = mesaLibreFront(1);
    $comanda = comandaConProductoFront($mesero, $mesa);
    $efectivo = MetodoPago::where('codigo', 'efectivo')->first();

    Sanctum::actingAs(usuarioConRol('cajero'));

    $respuesta = $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => $efectivo->id])
        ->assertOk()
        ->assertJsonPath('data.estado.codigo', 'cerrada')
        ->assertJsonPath('data.metodo_pago.codigo', 'efectivo');

    expect((float) $respuesta->json('data.total'))->toBe(10000.0);
    expect($mesa->refresh()->estado->codigo)->toBe('libre');
});

it('al cobrar se aplica el descuento del 10 % cuando el subtotal llega al umbral', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = comandaConProductoFront($mesero, mesaLibreFront(2), cantidad: 4);
    $tarjeta = MetodoPago::where('codigo', 'tarjeta')->first();

    Sanctum::actingAs(usuarioConRol('cajero'));

    $respuesta = $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => $tarjeta->id])->assertOk();

    expect((float) $respuesta->json('data.total'))->toBe(18000.0);
});

it('un mesero no puede cobrar, y cobrar dos veces la misma comanda da 409', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = comandaConProductoFront($mesero, mesaLibreFront(3));
    $efectivo = MetodoPago::where('codigo', 'efectivo')->first();

    Sanctum::actingAs($mesero);
    $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => $efectivo->id])->assertForbidden();

    Sanctum::actingAs(usuarioConRol('cajero'));
    $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => $efectivo->id])->assertOk();
    $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => $efectivo->id])
        ->assertStatus(409)
        ->assertJsonPath('codigo', 'comanda_ya_cerrada');
});

it('cobrar exige método de pago válido', function () {
    $comanda = comandaConProductoFront(usuarioConRol('mesero'), mesaLibreFront(4));

    Sanctum::actingAs(usuarioConRol('cajero'));

    $this->postJson("/api/comandas/{$comanda->id}/cobrar", [])->assertStatus(422);
    $this->postJson("/api/comandas/{$comanda->id}/cobrar", ['metodo_pago_id' => 9999])->assertStatus(422);
});

it('el listado de comandas de un mesero solo incluye las suyas', function () {
    $ana = usuarioConRol('mesero');
    $beto = usuarioConRol('mesero');
    comandaConProductoFront($ana, mesaLibreFront(5));
    comandaConProductoFront($beto, mesaLibreFront(6));

    Sanctum::actingAs($ana);
    $this->getJson('/api/comandas')->assertOk()->assertJsonCount(1, 'data');

    Sanctum::actingAs(usuarioConRol('cajero'));
    $this->getJson('/api/comandas')->assertOk()->assertJsonCount(2, 'data');
});

it('las mesas muestran la comanda activa con mesero y total', function () {
    $mesero = usuarioConRol('mesero');
    $mesa = mesaLibreFront(7);
    mesaLibreFront(8);
    comandaConProductoFront($mesero, $mesa);

    Sanctum::actingAs(usuarioConRol('cajero'));

    $mesas = $this->getJson('/api/mesas?por_pagina=100')->assertOk()->json('data');

    expect($mesas[0]['estado']['codigo'])->toBe('ocupada')
        ->and($mesas[0]['comanda_activa']['mesero'])->toBe($mesero->name)
        ->and((float) $mesas[0]['comanda_activa']['total'])->toBe(10000.0)
        ->and($mesas[0]['zona'])->toBe('Salón')
        ->and($mesas[1]['comanda_activa'])->toBeNull();
});

it('una mesa con comandas registradas no se puede eliminar', function () {
    $mesa = mesaLibreFront(9);
    comandaConProductoFront(usuarioConRol('mesero'), $mesa);

    Sanctum::actingAs(usuarioConRol('administrador'));

    $this->deleteJson("/api/mesas/{$mesa->id}")->assertStatus(409)->assertJsonPath('codigo', 'mesa_con_comandas');
});
