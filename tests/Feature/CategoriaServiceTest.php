<?php

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\CategoriaService;

it('elimina una categoría sin productos asociados', function () {
    $categoria = Categoria::factory()->create();

    app(CategoriaService::class)->eliminar($categoria);

    expect(Categoria::find($categoria->id))->toBeNull();
});

it('no permite eliminar una categoría que tiene productos asociados', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->for($categoria)->create();

    expect(fn () => app(CategoriaService::class)->eliminar($categoria))
        ->toThrow(ReglaNegocioException::class);

    expect(Categoria::find($categoria->id))->not->toBeNull();
});
