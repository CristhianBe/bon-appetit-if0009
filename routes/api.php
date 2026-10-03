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

// Rutas PÚBLICAS: no llevan "auth:sanctum" porque todavía no hay token (es acá donde se
// consigue, o se crea la cuenta que después inicia sesión). "throttle" frena abuso por IP:
// 5/min en login (fuerza bruta de contraseñas), 10/min en registro (creación masiva de cuentas).
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// A partir de acá, todas las rutas requieren "Authorization: Bearer <token>" válido. Si no
// viene el token (o es inválido, o ya expiró), Laravel corta la petición y responde 401
// automáticamente, sin que ningún controlador se llegue a ejecutar (ver bootstrap/app.php).
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'cambiarPassword']);

    // Rutas orientadas a recursos (Lab 5): sustantivos en plural, sin verbos.
    // El nombre de cada parámetro se conserva igual al de los controladores
    // (category, user, etc.) para no tener que retocar el binding existente.

    Route::apiResource('categorias', CategoriaController::class)
        ->parameters(['categorias' => 'category']);

    Route::apiResource('comandas', ComandaController::class);

    // DetalleComanda es una relación de Comanda: se anida bajo /comandas/{comanda}/detalles
    // para el listado y la creación, y queda "shallow" (/detalles/{detalle}) para
    // mostrar, actualizar y eliminar un detalle puntual — así no hace falta repetir
    // el id de la comanda en cada operación sobre un detalle ya existente.
    Route::apiResource('comandas.detalles', DetalleComandaController::class)
        ->parameters(['detalles' => 'detalleComanda'])
        ->shallow();

    Route::apiResource('estados-comanda', EstadoComandaController::class)
        ->parameters(['estados-comanda' => 'estadoComanda']);

    Route::apiResource('mesas', MesaController::class);

    Route::apiResource('metodos-pago', MetodoPagoController::class)
        ->parameters(['metodos-pago' => 'metodoPago']);

    Route::apiResource('productos', ProductoController::class);

    Route::apiResource('roles', RoleController::class);

    Route::apiResource('unidades-medida', UnidadMedidaController::class)
        ->parameters(['unidades-medida' => 'unidadMedida']);

    Route::apiResource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user']);
});
