<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ReservaService
{
    public const DURACION_MINUTOS = 120;

    private const TAMANO_PAGINA_MAXIMO = 100;

    public const ZONA_HORARIA = 'America/Costa_Rica';

    public static function hoy(): string
    {
        return Carbon::now(self::ZONA_HORARIA)->toDateString();
    }

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Reserva::with('mesa');

        if (! empty($filtros['fecha'])) {
            $query->whereDate('fecha', $filtros['fecha']);
        }

        if (! empty($filtros['desde'])) {
            $query->whereDate('fecha', '>=', $filtros['desde']);
        }

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['mesa_id'])) {
            $query->where('mesa_id', $filtros['mesa_id']);
        }

        $porPagina = max(min((int) ($filtros['por_pagina'] ?? 15), self::TAMANO_PAGINA_MAXIMO), 1);

        return $query->orderBy('fecha')->orderBy('hora')->paginate($porPagina);
    }

    public function crear(array $datos, User $actor): Reserva
    {
        $datos['user_id'] = $actor->id;
        $datos['estado'] = $datos['estado'] ?? 'pendiente';

        $this->validarMesa($datos['mesa_id'] ?? null, (int) $datos['personas'], $datos['fecha'], $datos['hora'], $datos['estado'], null);

        return Reserva::create($datos)->load('mesa');
    }

    public function actualizar(Reserva $reserva, array $datos): Reserva
    {
        if (! in_array($reserva->estado, Reserva::ESTADOS_ACTIVOS, true)) {
            throw new ReglaNegocioException(
                'La reserva ya está '.($reserva->estado === 'cancelada' ? 'cancelada' : 'cumplida').' y no se puede modificar.',
                'reserva_cerrada',
                409,
            );
        }

        $final = array_merge($reserva->only(['mesa_id', 'personas', 'hora', 'estado']), [
            'fecha' => $reserva->fecha->toDateString(),
        ], $datos);

        $this->validarMesa($final['mesa_id'], (int) $final['personas'], $final['fecha'], $final['hora'], $final['estado'], $reserva->id);

        $reserva->update($datos);

        return $reserva->refresh()->load('mesa');
    }

    public function eliminar(Reserva $reserva): void
    {
        $reserva->delete();
    }

    private function validarMesa(?int $mesaId, int $personas, string $fecha, string $hora, string $estado, ?int $ignorarId): void
    {
        if (! $mesaId || ! in_array($estado, Reserva::ESTADOS_ACTIVOS, true)) {
            return;
        }

        $mesa = Mesa::findOrFail($mesaId);

        if ($personas > $mesa->capacidad) {
            throw new ReglaNegocioException(
                "La mesa {$mesa->numero} tiene capacidad para {$mesa->capacidad} personas.",
                'capacidad_insuficiente',
                422,
            );
        }

        $minutos = $this->aMinutos($hora);

        $choque = Reserva::where('mesa_id', $mesaId)
            ->whereDate('fecha', $fecha)
            ->whereIn('estado', Reserva::ESTADOS_ACTIVOS)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->get()
            ->first(fn (Reserva $otra) => abs($this->aMinutos($otra->hora) - $minutos) < self::DURACION_MINUTOS);

        if ($choque) {
            throw new ReglaNegocioException(
                "La mesa {$mesa->numero} ya está reservada a las {$choque->hora} (a nombre de {$choque->nombre}).",
                'reserva_solapada',
                409,
            );
        }
    }

    private function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return $h * 60 + $m;
    }
}
