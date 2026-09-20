<?php

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('categories', CategoriaController::class);
Route::apiResource('productos', ProductoController::class);
Route::apiResource('mesas', MesaController::class);
Route::apiResource('comandas', ComandaController::class);
Route::apiResource('comanda-detalle', DetalleComandaController::class);
Route::apiResource('users', UserController::class);

Route::apiResource('roles', RoleController::class);
Route::apiResource('estado-comandas', EstadoComandaController::class);
Route::apiResource('estados-comanda', EstadoComandaController::class);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('unidad-medida', UnidadMedidaController::class);
Route::apiResource('unidades-medida', UnidadMedidaController::class);

Route::get('categorias/{categoria}/productos', [CategoriaController::class, 'productos'])
    ->name('categorias.productos.index');

Route::get('mesas/{mesa}/comandas', [MesaController::class, 'comandas'])
    ->name('mesas.comandas.index');

Route::get('comandas/{comanda}/detalles', [DetalleComandaController::class, 'indexByComanda'])
    ->name('comandas.detalles.index');

Route::post('comandas/{comanda}/detalles', [DetalleComandaController::class, 'storeByComanda'])
    ->name('comandas.detalles.store');