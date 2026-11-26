<?php

use Illuminate\Support\Facades\Route;

// Redirige la raíz directamente al login
Route::get('/', function () {
    return redirect()->route('login');
});

// El login no envía nada al servidor web: el formulario llama por JavaScript a
// POST /api/login (Sanctum) y guarda el token (y el rol) en el navegador.
Route::get('/login', function () {
    return view('login');
})->name('login');

// Las pantallas del panel son "cascarones": el HTML no trae datos. Cada una carga lo suyo desde la
// API con el token de la sesión (resources/js/pages/*.js) y muestra solo lo que el rol permite.
// Quién puede qué lo decide siempre la API; ocultar un botón es solo comodidad visual.
Route::view('/menu', 'menu')->name('menu');
Route::view('/mesas', 'mesas')->name('mesas');
Route::view('/pedidos', 'pedidos')->name('pedidos');
Route::view('/reservas', 'reservas')->name('reservas');
Route::view('/usuarios', 'usuarios')->name('usuarios');

Route::get('/api-demo', function () {
    return view('api-demo');
});
