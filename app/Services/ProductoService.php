<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductoService
{
    private const CAMPOS_ORDENABLES = ['nombre', 'precio', 'created_at'];
    private const TAMANO_PAGINA_MAXIMO = 50;

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Producto::query();

        if (! empty($filtros['categoria'])) {
            $query->deCategoria($filtros['categoria']);
        }

        if (! empty($filtros['solo_disponibles'])) {
            $query->disponibles();
        }

        if (! empty($filtros['q'])) {
            $query->where('nombre', 'like', '%'.$filtros['q'].'%');
        }

        $ordenarPor = in_array($filtros['ordenar_por'] ?? null, self::CAMPOS_ORDENABLES, true)
            ? $filtros['ordenar_por']
            : 'nombre';

        $direccion = strtolower($filtros['direccion'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $porPagina = min((int) ($filtros['por_pagina'] ?? 15), self::TAMANO_PAGINA_MAXIMO);
        $porPagina = max($porPagina, 1);

        return $query->orderBy($ordenarPor, $direccion)->paginate($porPagina);
    }

    public function crear(array $datos): Producto
    {
        return Producto::create($datos);
    }

    public function actualizar(Producto $producto, array $datos): Producto
    {
        $producto->update($datos);

        return $producto->refresh();
    }

    public function eliminar(Producto $producto): void
    {
        if ($producto->detalleComandas()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar un producto que ya fue vendido en alguna comanda.',
                'producto_con_dependencias',
                409,
            );
        }

        $producto->delete();
    }
}