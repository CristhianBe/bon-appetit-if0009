<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Categoria rutas 
Route::apiResource('categories', CategoriaController::class);

//Comanda rutas
Route::apiResource('comandas', ComandaController::class);

//Detalle Comanda rutas
Route::apiResource('comanda-detalle', DetalleComandaController::class);

//Estado Comanda rutas
Route::apiResource('estado-comandas', EstadoComandaController::class);

//Mesa rutas
Route::apiResource('mesas', MesaController::class);

//Metodos de pago
Route::apiResource('metodos-pago', MetodoPagoController::class);

//Producto
Route::apiResource('productos', ProductoController::class);

//Role
Route::apiResource('roles', RoleController::class);

//Unidad de medida
Route::apiResource('unidad-medida', UnidadMedidaController::class);

//User
Route::apiResource('users', UserController::class);
