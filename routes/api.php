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
Route::get('/categories', [CategoriaController::class, 'index']);
Route::post('/categories', [CategoriaController::class, 'store']);
Route::get('/categories/{category}', [CategoriaController::class, 'show']);
Route::put('/categories/{category}', [CategoriaController::class, 'update']);
Route::delete('/categories/{category}', [CategoriaController::class, 'destroy']);

//Comanda rutas
Route::get('/comandas', [ComandaController::class, 'index']);
Route::post('/comandas', [ComandaController::class, 'store']);
Route::get('/comandas/{comanda}', [ComandaController::class, 'show']);
Route::put('/comandas/{comanda}', [ComandaController::class, 'update']);
Route::delete('/comandas/{comanda}', [ComandaController::class, 'destroy']);

//Detalle Comanda rutas
Route::get('/DetalleComandas', [DetalleComandaController::class, 'index']);
Route::post('/DetalleComandas', [DetalleComandaController::class, 'store']);
Route::get('/DetalleComandas/{detalleComanda}', [DetalleComandaController::class, 'show']);
Route::put('/DetalleComandas/{detalleComanda}', [DetalleComandaController::class, 'update']);
Route::delete('/DetalleComandas/{detalleComanda}', [DetalleComandaController::class, 'destroy']);

//Estado Comanda rutas
Route::get('/EstadoComandas', [EstadoComandaController::class, 'index']);
Route::post('/EstadoComandas', [EstadoComandaController::class, 'store']);
Route::get('/EstadoComandas/{estadoComanda}', [EstadoComandaController::class, 'show']);
Route::put('/EstadoComandas/{estadoComanda}', [EstadoComandaController::class, 'update']);
Route::delete('/EstadoComandas/{estadoComanda}', [EstadoComandaController::class, 'destroy']);

//Mesa rutas
Route::get('/mesas', [MesaController::class, 'index']);
Route::post('/mesas', [MesaController::class, 'store']);
Route::get('/mesas/{mesa}', [MesaController::class, 'show']);
Route::put('/mesas/{mesa}', [MesaController::class, 'update']);
Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy']);

//Metodos de pago
Route::get('/metodos-pago', [MetodoPagoController::class, 'index']);
Route::post('/metodos-pago', [MetodoPagoController::class, 'store']);
Route::get('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'show']);
Route::put('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'update']);
Route::delete('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'destroy']);

//Producto
Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/productos', [ProductoController::class, 'store']);
Route::get('/productos/{producto}', [ProductoController::class, 'show']);
Route::put('/productos/{producto}', [ProductoController::class, 'update']);
Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);

//Role
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::get('/roles/{role}', [RoleController::class, 'show']);
Route::put('/roles/{role}', [RoleController::class, 'update']);
Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

//Unidad de medida
Route::get('/unidad-medida', [UnidadMedidaController::class, 'index']);
Route::post('/unidad-medida', [UnidadMedidaController::class, 'store']);
Route::get('/unidad-medida/{unidadMedida}', [UnidadMedidaController::class, 'show']);
Route::put('/unidad-medida/{unidadMedida}', [UnidadMedidaController::class, 'update']);
Route::delete('/unidad-medida/{unidadMedida}', [UnidadMedidaController::class, 'destroy']);

//User
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);