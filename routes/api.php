<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\DetalleComandaController;
use App\Http\Controllers\EstadoComandaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas PÚBLICAS: no llevan "auth:sanctum"
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// 👇 CAMBIO 1: Agregamos esta línea para permitir que cualquiera vea el menú
Route::apiResource('productos', ProductoController::class)->only(['index', 'show']);

// Quién puede qué (resumen):
//   - Leer catálogos, mesas, comandas y reservas → cualquier usuario con sesión.
//   - Menú, categorías, mesas y catálogos (escritura), usuarios y roles → solo administrador.
//   - Tomar pedidos (comandas) → administrador y mesero (reglas finas en ComandaPolicy).
//   - Cobrar → administrador y cajero (ComandaPolicy::cobrar).
//   - Reservas (crear/editar) → administrador y mesero.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $usuario = $request->user();

        return [
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'rol' => $usuario->roles->first()?->name,
        ];
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'cambiarPassword']);

    // ---- Lectura: cualquier usuario autenticado ----
    Route::apiResource('categorias', CategoriaController::class)
        ->parameters(['categorias' => 'category'])
        ->only(['index', 'show']);
    Route::apiResource('estados-comanda', EstadoComandaController::class)
        ->parameters(['estados-comanda' => 'estadoComanda'])
        ->only(['index', 'show']);
    Route::apiResource('metodos-pago', MetodoPagoController::class)
        ->parameters(['metodos-pago' => 'metodoPago'])
        ->only(['index', 'show']);
    Route::apiResource('unidades-medida', UnidadMedidaController::class)
        ->parameters(['unidades-medida' => 'unidadMedida'])
        ->only(['index', 'show']);
    Route::apiResource('mesas', MesaController::class)->only(['index', 'show']);
    Route::apiResource('reservas', ReservaController::class)->only(['index', 'show']);

    // ---- Comandas: las reglas por rol y por dueño viven en ComandaPolicy ----
    Route::apiResource('comandas', ComandaController::class);
    Route::post('comandas/{comanda}/cobrar', [ComandaController::class, 'cobrar'])->name('comandas.cobrar');

    Route::apiResource('comandas.detalles', DetalleComandaController::class)
        ->parameters(['detalles' => 'detalleComanda'])
        ->shallow();

    // ---- Reservas: las gestiona el personal de sala ----
    Route::middleware('rol:administrador,mesero')->group(function () {
        Route::apiResource('reservas', ReservaController::class)->only(['store', 'update']);
    });

    // ---- Administración: solo el rol administrador ----
    Route::middleware('rol:administrador')->group(function () {
        Route::apiResource('categorias', CategoriaController::class)
            ->parameters(['categorias' => 'category'])
            ->only(['store', 'update', 'destroy']);
        Route::apiResource('estados-comanda', EstadoComandaController::class)
            ->parameters(['estados-comanda' => 'estadoComanda'])
            ->only(['store', 'update', 'destroy']);
        Route::apiResource('metodos-pago', MetodoPagoController::class)
            ->parameters(['metodos-pago' => 'metodoPago'])
            ->only(['store', 'update', 'destroy']);
        Route::apiResource('unidades-medida', UnidadMedidaController::class)
            ->parameters(['unidades-medida' => 'unidadMedida'])
            ->only(['store', 'update', 'destroy']);
        Route::apiResource('mesas', MesaController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('reservas', ReservaController::class)->only(['destroy']);
        Route::apiResource('productos', ProductoController::class)->except(['index', 'show']);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'user']);
    });
});
