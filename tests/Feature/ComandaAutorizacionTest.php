<?php

use App\Models\Comanda;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\User;
use App\Services\ComandaService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

// Estas pruebas ejercitan ComandaService DIRECTAMENTE (sin pasar por una ruta HTTP ni por
// ComandaController), a propósito: el Laboratorio 6 exige demostrar que la Capa 2 de
// autorización (la que vive DENTRO del servicio, ver Gate::forUser($actor)->authorize(...) en
// app/Services/ComandaService.php) rechaza igual aunque nadie pase por la Capa 1 del
// controlador. Si solo hubiéramos protegido la ruta, cualquier otro código del proyecto (una
// consola, un job en cola) podría llamar al servicio y saltarse la regla — acá comprobamos
// que NO puede.

beforeEach(fn () => $this->seed(CatalogosSeeder::class));

function mesaLibre(int $numero): Mesa
{
    $estado = EstadoMesa::where('codigo', 'libre')->first();

    return Mesa::create(['numero' => $numero, 'capacidad' => 4, 'estado_mesa_id' => $estado->id]);
}

it('el administrador puede ver cualquier comanda, sin importar quién la abrió', function () {
    $admin = usuarioConRol('administrador');
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(1)->id, 'user_id' => $mesero->id], $mesero);

    $encontrada = Comanda::findOrFail($comanda->id);
    Gate::forUser($admin)->authorize('view', $encontrada);

    expect($encontrada->id)->toBe($comanda->id);
});

it('un mesero puede ver su propia comanda', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(2)->id, 'user_id' => $mesero->id], $mesero);

    Gate::forUser($mesero)->authorize('view', $comanda);

    expect(true)->toBeTrue(); // no lanzó AuthorizationException
});

it('un mesero NO puede ver la comanda de otro mesero', function () {
    $dueño = usuarioConRol('mesero');
    $otroMesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(3)->id, 'user_id' => $dueño->id], $dueño);

    expect(fn () => Gate::forUser($otroMesero)->authorize('view', $comanda))
        ->toThrow(AuthorizationException::class);
});

it('cajero puede ver cualquier comanda (solo lectura), aunque no la haya abierto ningún cajero', function () {
    $cajero = usuarioConRol('cajero');
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(4)->id, 'user_id' => $mesero->id], $mesero);

    Gate::forUser($cajero)->authorize('view', $comanda);

    expect(true)->toBeTrue();
});

it('cajero NO puede abrir una comanda (rol de solo lectura)', function () {
    $cajero = usuarioConRol('cajero');

    expect(fn () => app(ComandaService::class)->abrir([
        'mesa_id' => mesaLibre(5)->id,
        'user_id' => $cajero->id,
    ], $cajero))->toThrow(AuthorizationException::class);
});

it('un mesero NO puede eliminar la comanda de otro mesero', function () {
    $dueño = usuarioConRol('mesero');
    $otroMesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(6)->id, 'user_id' => $dueño->id], $dueño);

    expect(fn () => app(ComandaService::class)->eliminar($comanda, $otroMesero))
        ->toThrow(AuthorizationException::class);

    expect(Comanda::find($comanda->id))->not->toBeNull();
});

it('un mesero puede eliminar su propia comanda', function () {
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(7)->id, 'user_id' => $mesero->id], $mesero);

    app(ComandaService::class)->eliminar($comanda, $mesero);

    expect(Comanda::find($comanda->id))->toBeNull();
});

it('abrir() asigna como dueño al mesero autenticado, ignorando el user_id que venga en el body', function () {
    $mesero = usuarioConRol('mesero');
    $otroMesero = usuarioConRol('mesero');

    // A propósito se manda el id de OTRO mesero en el body: ComandaService::abrir() debe
    // ignorarlo y usar siempre al actor autenticado, para que un mesero no pueda abrir
    // comandas a nombre de otro.
    $comanda = app(ComandaService::class)->abrir([
        'mesa_id' => mesaLibre(8)->id,
        'user_id' => $otroMesero->id,
    ], $mesero);

    expect($comanda->user_id)->toBe($mesero->id);
});

it('un usuario sin ningún rol asignado no puede ver ninguna comanda (caso límite: cero roles)', function () {
    // usuarioConRol() no se usa acá a propósito: este usuario no tiene NINGÚN rol en
    // model_has_roles, que es distinto de "tener un rol sin permiso" — es el caso límite de
    // la cuenta recién registrada a la que todavía no se le asignó ningún rol.
    $sinRol = User::factory()->create();
    $mesero = usuarioConRol('mesero');
    $comanda = app(ComandaService::class)->abrir(['mesa_id' => mesaLibre(9)->id, 'user_id' => $mesero->id], $mesero);

    expect(fn () => Gate::forUser($sinRol)->authorize('view', $comanda))
        ->toThrow(AuthorizationException::class);
});
