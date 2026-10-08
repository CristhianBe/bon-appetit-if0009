<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/menu', function () {
    return view('menu');
})->name('menu');

Route::get('/contacto', function () {
    return view('contact');
})->name('contact');

Route::get('/login', function () {
    return view('login');
});

Route::get('/mesas', function () {
    return view('mesas');
});

Route::get('/pedidos', function () {
    return view('pedidos');
});

Route::get('/reservas', function () {
    return view('reservas');
});