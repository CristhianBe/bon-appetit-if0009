<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\DetalleComandaController;
use App\Http\Controllers\EstadoComandaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\ProductoController;
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

// A partir de acá, todas las rutas requieren "Authorization: Bearer <token>" válido.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'cambiarPassword']);

    Route::apiResource('categorias', CategoriaController::class)
        ->parameters(['categorias' => 'category']);

    Route::apiResource('comandas', ComandaController::class);

    Route::apiResource('comandas.detalles', DetalleComandaController::class)
        ->parameters(['detalles' => 'detalleComanda'])
        ->shallow();

    Route::apiResource('estados-comanda', EstadoComandaController::class)
        ->parameters(['estados-comanda' => 'estadoComanda']);

    Route::apiResource('mesas', MesaController::class);

    Route::apiResource('metodos-pago', MetodoPagoController::class)
        ->parameters(['metodos-pago' => 'metodoPago']);

    // 👇 CAMBIO 2: Le agregamos ->except() a la ruta que ya tenías
    Route::apiResource('productos', ProductoController::class)->except(['index', 'show']);

    Route::apiResource('roles', RoleController::class);

    Route::apiResource('unidades-medida', UnidadMedidaController::class)
        ->parameters(['unidades-medida' => 'unidadMedida']);

    Route::apiResource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user']);
});
