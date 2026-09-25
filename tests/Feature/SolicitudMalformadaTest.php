<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('responde 400 cuando el body no es JSON valido', function () {
    Sanctum::actingAs(usuarioConRol('administrador'));

    $respuesta = $this->call(
        'POST',
        '/api/categorias',
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
        content: '{"nombre": "Bebidas"' // llave sin cerrar: JSON invalido a proposito
    );

    $respuesta->assertStatus(400)->assertJsonPath('codigo', 'solicitud_invalida');
});

test('un body JSON valido pero vacio sigue respondiendo la validacion normal (422)', function () {
    Sanctum::actingAs(usuarioConRol('administrador'));

    $this->postJson('/api/categorias', [])
        ->assertStatus(422)
        ->assertJsonPath('codigo', 'validacion_fallida');
});
