<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComandaController;
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
