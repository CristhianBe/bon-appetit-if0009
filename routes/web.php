<?php

use App\Support\DatosDemo;
use Illuminate\Support\Facades\Route;

// Redirige la raíz directamente al login
Route::get('/', function () {
    return redirect()->route('login');
});

// El login no envía nada al servidor web: el formulario llama por JavaScript a
// POST /api/login (Sanctum) y guarda el token en el navegador.
Route::get('/login', function () {
    return view('login');
})->name('login');

// El menú se carga desde la API pública GET /api/productos.
Route::get('/menu', function () {
    return view('menu');
})->name('menu');

// Mesas, comandas y reservaciones son maquetas con datos de demostración
// compartidos (App\Support\DatosDemo), todavía sin conexión a la API.
Route::get('/mesas', function () {
    return view('mesas', ['mesas' => DatosDemo::mesas()]);
})->name('mesas');

Route::get('/pedidos', function () {
    return view('pedidos', ['comandas' => DatosDemo::comandas()]);
})->name('pedidos');

Route::get('/reservas', function () {
    return view('reservas', ['reservas' => DatosDemo::reservas()]);
})->name('reservas');

Route::get('/api-demo', function () {
    return view('api-demo');
});
