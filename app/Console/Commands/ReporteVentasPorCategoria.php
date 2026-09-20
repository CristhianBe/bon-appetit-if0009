<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('reporte:ventas-por-categoria')]
#[Description('Muestra unidades vendidas y total facturado, agrupado por categoría')]
class ReporteVentasPorCategoria extends Command
{
    public function handle(): void
    {
        $filas = DB::table('detalle_comandas')
            ->join('productos', 'detalle_comandas.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->groupBy('categorias.id', 'categorias.nombre')
            ->selectRaw('categorias.nombre as categoria')
            ->selectRaw('SUM(detalle_comandas.cantidad) as unidades_vendidas')
            ->selectRaw('SUM(detalle_comandas.cantidad * detalle_comandas.precio_unitario) as total_facturado')
            ->orderByDesc('total_facturado')
            ->get();

        $this->table(
            ['Categoría', 'Unidades vendidas', 'Total facturado'],
            $filas->map(fn ($fila) => [
                $fila->categoria,
                $fila->unidades_vendidas,
                number_format($fila->total_facturado, 2),
            ])->toArray()
        );
    }
}