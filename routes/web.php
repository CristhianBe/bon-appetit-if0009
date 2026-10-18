<?php

use Illuminate\Support\Facades\Route;

// Redirige la raíz directamente al login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/menu', function () {
    return view('menu');
})->name('menu');

Route::get('/contacto', function () {
    return view('contact');
})->name('contact');

Route::get('/mesas', function () {
    return view('mesas');
})->name('mesas');

Route::get('/pedidos', function () {
    return view('pedidos');
})->name('pedidos');

Route::get('/reservas', function () {
    return view('reservas');
})->name('reservas');