<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\ProductoController;
use App\Models\Comanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// NOTA para el equipo: este archivo también se está tocando en feature/ControladoresVacios y
// feature/RoutasMelvin (rutas de Categoria en construcción ahí). Este bloque se escribió en una
// rama aparte (feature/lab4-capa-negocio) a propósito, para no pisar ese trabajo — falta
// reconciliar/fusionar cuando el equipo lo coordine. No hay autenticación todavía (eso es del
// Laboratorio 6): todas las rutas de acá son públicas por ahora.

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// apiResource crea automáticamente 5 rutas (index/store/show/update/destroy) por cada una.
Route::apiResource('comandas', ComandaController::class);
Route::apiResource('productos', ProductoController::class);
Route::apiResource('categorias', CategoriaController::class);

// "Cerrar" no es parte del CRUD estándar, por eso se declara a mano. Laravel inyecta
// automáticamente la Comanda (buscada por {comanda} en la URL) y el ComandaService.
Route::post('/comandas/{comanda}/cerrar', function (Comanda $comanda, ComandaService $service) {
    return $service->cerrar($comanda);
});
