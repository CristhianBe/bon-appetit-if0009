<?php

// Lab 14 — "compuerta" del flujo de CI/CD.
// En condiciones normales siempre pasa. Solo falla cuando el workflow se dispara a mano
// con simular_fallo=true, para dejar evidencia de que una prueba fallida detiene el despliegue
// sin tener que subir código roto a la rama principal.
test('compuerta de CI: no se pidió simular un fallo', function () {
    expect(getenv('SIMULAR_FALLO_CI'))->not->toBe('1');
});
