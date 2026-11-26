<?php

test('compuerta de CI: no se pidió simular un fallo', function () {
    expect(getenv('SIMULAR_FALLO_CI'))->not->toBe('1');
});
