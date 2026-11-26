<?php

// Las vistas usan @vite; en las pruebas no hace falta el build de los assets.
beforeEach(fn () => $this->withoutVite());

// La raíz ya no es una página propia: desde el front-end web (labs 7-9) redirige al login.
test('la raíz redirige al login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('la página de login responde correctamente', function () {
    $this->get('/login')
        ->assertOk()
        // El formulario se envía por POST desde JavaScript: la contraseña nunca va en la URL.
        ->assertSee('method="post"', false)
        ->assertDontSee('method="GET"', false);
});

// Las pantallas del panel son cascarones: el HTML no trae datos, los carga desde la API por JavaScript.
test('las vistas del panel responden correctamente', function (string $ruta, string $texto) {
    $this->get($ruta)->assertOk()->assertSee($texto);
})->with([
    'mesas' => ['/mesas', 'Control de mesas'],
    'comandas' => ['/pedidos', 'Comandas activas'],
    'menú' => ['/menu', 'Menú y catálogo'],
    'reservaciones' => ['/reservas', 'Próximas reservas'],
    'usuarios' => ['/usuarios', 'Usuarios y roles'],
]);

test('el panel no trae datos de demostración incrustados en el HTML', function () {
    // Antes mesas/comandas/reservas eran maquetas con datos fijos; ahora todo viene de la API.
    $this->get('/mesas')->assertDontSee('C-0142')->assertDontSee('demostración');
    $this->get('/pedidos')->assertDontSee('C-0142')->assertDontSee('demostración');
    $this->get('/reservas')->assertDontSee('demostración');
});

test('el menú lateral marca los enlaces de administración solo para el rol administrador', function () {
    $this->get('/mesas')
        ->assertSee('data-roles="administrador"', false)
        ->assertSee(route('usuarios'), false);
});

// Lab 14: endpoint de verificación de estado que usa Railway (healthcheckPath).
test('el endpoint de verificación de estado responde 200', function () {
    $this->get('/up')->assertOk();
});
