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

test('las vistas del panel responden correctamente', function (string $ruta, string $texto) {
    $this->get($ruta)->assertOk()->assertSee($texto);
})->with([
    'mesas' => ['/mesas', 'Control de mesas'],
    'comandas' => ['/pedidos', 'Comandas activas'],
    'menú' => ['/menu', 'Menú y catálogo'],
    'reservaciones' => ['/reservas', 'Próximas reservas'],
]);

test('la comanda de una mesa ocupada muestra el mismo total en mesas y comandas', function () {
    $comanda = collect(\App\Support\DatosDemo::comandas())->firstWhere('mesa', 2);
    $total = \App\Support\DatosDemo::colones($comanda['total']);

    $this->get('/mesas')->assertSee($total);
    $this->get('/pedidos')->assertSee($total);
});

// Lab 14: endpoint de verificación de estado que usa Railway (healthcheckPath).
test('el endpoint de verificación de estado responde 200', function () {
    $this->get('/up')->assertOk();
});
