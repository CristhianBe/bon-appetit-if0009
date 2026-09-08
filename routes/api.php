<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Categoria rutas 
Route::get('/categories', [CategoriaController::class, 'index']);
Route::post('/categories', [CategoriaController::class, 'store']);
Route::get('/categories/{category}', [CategoriaController::class, 'show']);
Route::put('/categories/{category}', [CategoriaController::class, 'update']);
Route::delete('/categories/{category}', [CategoriaController::class, 'destroy']);
