<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoriaService
{
    private const TAMANO_PAGINA_MAXIMO = 50;

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Categoria::query();

        if (! empty($filtros['q'])) {
            $query->where('nombre', 'like', '%'.$filtros['q'].'%');
        }

        $porPagina = min((int) ($filtros['por_pagina'] ?? 15), self::TAMANO_PAGINA_MAXIMO);
        $porPagina = max($porPagina, 1);

        return $query->orderBy('nombre')->paginate($porPagina);
    }

    public function crear(array $datos): Categoria
    {
        return Categoria::create($datos);
    }

    public function actualizar(Categoria $categoria, array $datos): Categoria
    {
        $categoria->update($datos);

        return $categoria->refresh();
    }

    public function eliminar(Categoria $categoria): void
    {
        if ($categoria->productos()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar una categoría que tiene productos asociados.',
                'categoria_con_productos',
                409,
            );
        }

        $categoria->delete();
    }
}
