<?php

beforeEach(fn () => $this->withoutVite());

test('la raíz redirige al login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('la página de login responde correctamente', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('method="post"', false)
        ->assertDontSee('method="GET"', false);
});

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
    $this->get('/mesas')->assertDontSee('C-0142')->assertDontSee('demostración');
    $this->get('/pedidos')->assertDontSee('C-0142')->assertDontSee('demostración');
    $this->get('/reservas')->assertDontSee('demostración');
});

test('el menú lateral marca los enlaces de administración solo para el rol administrador', function () {
    $this->get('/mesas')
        ->assertSee('data-roles="administrador"', false)
        ->assertSee(route('usuarios'), false);
});

test('el endpoint de verificación de estado responde 200', function () {
    $this->get('/up')->assertOk();
});
