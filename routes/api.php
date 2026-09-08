<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\DetalleComandaController;
use App\Http\Controllers\EstadoComandaController;
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
