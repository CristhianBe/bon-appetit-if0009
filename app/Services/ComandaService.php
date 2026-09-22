<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ComandaService
{
    private const UMBRAL_DESCUENTO = 15000;

    private const PORCENTAJE_DESCUENTO = 0.10;

    private const TAMANO_PAGINA_MAXIMO = 50;

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Comanda::with(['mesa.estado', 'mesero', 'estadoComanda', 'detalles.producto']);

        if (! empty($filtros['mesa_id'])) {
            $query->where('mesa_id', $filtros['mesa_id']);
        }

        if (! empty($filtros['estado_comanda_id'])) {
            $query->where('estado_comanda_id', $filtros['estado_comanda_id']);
        }

        $porPagina = min((int) ($filtros['por_pagina'] ?? 15), self::TAMANO_PAGINA_MAXIMO);
        $porPagina = max($porPagina, 1);

        return $query->latest()->paginate($porPagina);
    }

    /**
     * $actor es el usuario autenticado que hace la petición. El controlador YA revisó el
     * permiso una vez (Capa 1, con $this->authorize()); acá se vuelve a revisar (Capa 2) para
     * que la regla se cumpla también si algo más —una consola, un job en cola, otro
     * controlador— llama a este servicio directamente sin pasar por la ruta protegida. Es el
     * mismo principio de "defensa en profundidad" que exige el Laboratorio 6.
     */
    public function abrir(array $datos, User $actor): Comanda
    {
        // Gate::forUser($actor)->authorize(...) ejecuta ComandaPolicy::create($actor) por
        // debajo. Si devuelve false, lanza AuthorizationException (Laravel la convierte sola
        // en HTTP 403).
        Gate::forUser($actor)->authorize('create', Comanda::class);

        // Un mesero solo puede abrir comandas a su propio nombre: sin esto, cualquier mesero
        // autenticado podría mandar el user_id de OTRO mesero en el body y "regalarle" (o
        // robarle) comandas. El administrador sí puede abrir a nombre de cualquiera (ej. para
        // corregir un dato mal cargado).
        if (! $actor->hasRole('administrador')) {
            $datos['user_id'] = $actor->id;
        }

        $mesa = Mesa::with('estado')->findOrFail($datos['mesa_id']);

        if ($mesa->estado->codigo !== 'libre') {
            throw new ReglaNegocioException(
                'No se puede abrir una comanda en una mesa que no está libre.',
                'mesa_no_disponible',
                409,
            );
        }

        return DB::transaction(function () use ($datos, $mesa) {
            $estadoAbierta = EstadoComanda::where('codigo', 'abierta')->firstOrFail();
            $estadoOcupada = EstadoMesa::where('codigo', 'ocupada')->firstOrFail();

            $comanda = Comanda::create([
                'mesa_id' => $mesa->id,
                'user_id' => $datos['user_id'],
                'estado_comanda_id' => $estadoAbierta->id,
            ]);

            $mesa->update(['estado_mesa_id' => $estadoOcupada->id]);

            return $comanda;
        });
    }

    public function agregarDetalle(Comanda $comanda, array $datos, User $actor): DetalleComanda
    {
        Gate::forUser($actor)->authorize('update', $comanda);

        if ($comanda->estadoComanda->codigo === 'cerrada') {
            throw new ReglaNegocioException(
                'No se puede modificar el detalle de una comanda ya cerrada.',
                'comanda_cerrada_no_editable',
                409,
            );
        }

        $producto = Producto::findOrFail($datos['producto_id']);

        if (! $producto->disponible) {
            throw new ReglaNegocioException(
                'No se puede agregar un producto que no está disponible.',
                'producto_no_disponible',
                422,
            );
        }

        return DetalleComanda::create([
            'comanda_id' => $comanda->id,
            'producto_id' => $producto->id,
            'cantidad' => $datos['cantidad'],
            'precio_unitario' => $producto->precio,
            'notas' => $datos['notas'] ?? null,
        ]);
    }

    public function actualizarDetalle(DetalleComanda $detalle, array $datos, User $actor): DetalleComanda
    {
        Gate::forUser($actor)->authorize('update', $detalle->comanda);

        if ($detalle->comanda->estadoComanda->codigo === 'cerrada') {
            throw new ReglaNegocioException(
                'No se puede modificar el detalle de una comanda ya cerrada.',
                'comanda_cerrada_no_editable',
                409,
            );
        }

        $detalle->update($datos);

        return $detalle->refresh();
    }

    public function cerrar(Comanda $comanda, User $actor): Comanda
    {
        // Cerrar es una forma de "editar" la comanda (cambia su estado), así que usa el mismo
        // permiso que update(): administrador, o el mesero dueño.
        Gate::forUser($actor)->authorize('update', $comanda);

        if ($comanda->detalles()->count() === 0) {
            throw new ReglaNegocioException(
                'No se puede cerrar una comanda sin al menos un producto.',
                'comanda_sin_detalle',
                422,
            );
        }

        return DB::transaction(function () use ($comanda) {
            $subtotal = $comanda->detalles()
                ->selectRaw('SUM(cantidad * precio_unitario) as total')
                ->value('total');

            $total = $subtotal >= self::UMBRAL_DESCUENTO
                ? $subtotal * (1 - self::PORCENTAJE_DESCUENTO)
                : $subtotal;

            $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->firstOrFail();
            $estadoLibre = EstadoMesa::where('codigo', 'libre')->firstOrFail();

            $comanda->update([
                'total' => $total,
                'estado_comanda_id' => $estadoCerrada->id,
                'cerrada_en' => now(),
            ]);

            $comanda->mesa->update(['estado_mesa_id' => $estadoLibre->id]);

            return $comanda->refresh();
        });
    }

    public function quitarDetalle(Comanda $comanda, DetalleComanda $detalle, User $actor): void
    {
        Gate::forUser($actor)->authorize('update', $comanda);

        if ($comanda->estadoComanda->codigo === 'cerrada') {
            throw new ReglaNegocioException(
                'No se puede modificar el detalle de una comanda ya cerrada.',
                'comanda_cerrada_no_editable',
                409,
            );
        }

        $detalle->delete();
    }

    public function eliminar(Comanda $comanda, User $actor): void
    {
        Gate::forUser($actor)->authorize('delete', $comanda);

        $comanda->delete();
    }
}
