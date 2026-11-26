<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::view('/menu', 'menu')->name('menu');
Route::view('/mesas', 'mesas')->name('mesas');
Route::view('/pedidos', 'pedidos')->name('pedidos');
Route::view('/reservas', 'reservas')->name('reservas');
Route::view('/usuarios', 'usuarios')->name('usuarios');

Route::get('/api-demo', function () {
    return view('api-demo');
});
