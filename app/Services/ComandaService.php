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

    public function listar(array $filtros, ?User $actor = null): LengthAwarePaginator
    {
        $query = Comanda::with(['mesa.estado', 'mesero', 'estadoComanda', 'metodoPago', 'detalles.producto']);

        if ($actor && $actor->hasRole('mesero') && ! $actor->hasAnyRole(['administrador', 'cajero'])) {
            $query->where('user_id', $actor->id);
        }

        if (! empty($filtros['mesa_id'])) {
            $query->where('mesa_id', $filtros['mesa_id']);
        }

        if (! empty($filtros['estado_comanda_id'])) {
            $query->where('estado_comanda_id', $filtros['estado_comanda_id']);
        }

        if (! empty($filtros['activas'])) {
            $query->whereHas('estadoComanda', fn ($q) => $q->where('codigo', '!=', 'cerrada'));
        }

        $porPagina = min((int) ($filtros['por_pagina'] ?? 15), self::TAMANO_PAGINA_MAXIMO);
        $porPagina = max($porPagina, 1);

        return $query->latest('id')->paginate($porPagina);
    }

    public function abrir(array $datos, User $actor): Comanda
    {
        Gate::forUser($actor)->authorize('create', Comanda::class);

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
                'personas' => $datos['personas'] ?? null,
                'notas' => $datos['notas'] ?? null,
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

    public function actualizar(Comanda $comanda, array $datos, User $actor): Comanda
    {
        Gate::forUser($actor)->authorize('update', $comanda);

        if (isset($datos['estado_comanda_id'])) {
            $estado = EstadoComanda::find($datos['estado_comanda_id']);

            if ($estado && $estado->codigo === 'cerrada') {
                return $this->cerrar($comanda, $actor);
            }
        }

        $comanda->update($datos);

        return $comanda->refresh();
    }

    public function cerrar(Comanda $comanda, User $actor): Comanda
    {
        Gate::forUser($actor)->authorize('update', $comanda);

        return $this->finalizar($comanda, null);
    }

    public function cobrar(Comanda $comanda, int $metodoPagoId, User $actor): Comanda
    {
        Gate::forUser($actor)->authorize('cobrar', $comanda);

        if ($comanda->estadoComanda->codigo === 'cerrada') {
            throw new ReglaNegocioException(
                'Esta comanda ya fue cobrada y cerrada.',
                'comanda_ya_cerrada',
                409,
            );
        }

        return $this->finalizar($comanda, $metodoPagoId);
    }

    public static function calcularTotales(iterable $detalles): array
    {
        $subtotal = 0.0;
        foreach ($detalles as $detalle) {
            $subtotal += $detalle->cantidad * (float) $detalle->precio_unitario;
        }

        $subtotal = round($subtotal, 2);
        $descuento = $subtotal >= self::UMBRAL_DESCUENTO ? round($subtotal * self::PORCENTAJE_DESCUENTO, 2) : 0.0;

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => round($subtotal - $descuento, 2),
        ];
    }

    private function finalizar(Comanda $comanda, ?int $metodoPagoId): Comanda
    {
        if ($comanda->detalles()->count() === 0) {
            throw new ReglaNegocioException(
                'No se puede cerrar una comanda sin al menos un producto.',
                'comanda_sin_detalle',
                422,
            );
        }

        return DB::transaction(function () use ($comanda, $metodoPagoId) {
            $total = self::calcularTotales($comanda->detalles()->get())['total'];

            $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->firstOrFail();
            $estadoLibre = EstadoMesa::where('codigo', 'libre')->firstOrFail();

            $comanda->update([
                'total' => $total,
                'estado_comanda_id' => $estadoCerrada->id,
                'metodo_pago_id' => $metodoPagoId ?? $comanda->metodo_pago_id,
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

        DB::transaction(function () use ($comanda) {
            if ($comanda->estadoComanda->codigo !== 'cerrada') {
                $estadoLibre = EstadoMesa::where('codigo', 'libre')->firstOrFail();
                $comanda->mesa->update(['estado_mesa_id' => $estadoLibre->id]);
            }

            $comanda->delete();
        });
    }
}
