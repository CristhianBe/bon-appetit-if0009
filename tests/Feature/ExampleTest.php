<?php

// La raíz ya no es una página propia: desde el front-end web (labs 7-9) redirige al login.
test('la raíz redirige al login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('la página de login responde correctamente', function () {
    $this->get('/login')->assertOk();
});

// Lab 14: endpoint de verificación de estado que usa Railway (healthcheckPath).
test('el endpoint de verificación de estado responde 200', function () {
    $this->get('/up')->assertOk();
});
