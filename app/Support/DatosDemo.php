<?php

namespace App\Support;

/**
 * Datos de demostración para las vistas web (maquetas del front-end).
 *
 * Las vistas de mesas, comandas y reservaciones todavía no consumen la API: usan estos
 * datos fijos. Viven en un solo lugar para que las tres pantallas cuenten la misma
 * historia (la mesa 2 ocupada tiene la comanda C-0142, con el mismo total, etc.).
 * Los nombres y precios de los platillos coinciden con los del ProductoSeeder.
 */
class DatosDemo
{
    public const IVA = 0.13;

    public const SERVICIO = 0.10;

    /** Minutos a partir de los cuales una comanda no servida se considera con retraso. */
    public const MINUTOS_RETRASO = 45;

    public static function comandas(): array
    {
        $comandas = [
            ['codigo' => 'C-0142', 'mesa' => 2, 'personas' => 4, 'mesero' => 'Ana M.', 'minutos' => 42, 'estado' => 'en_preparacion',
                'alergia' => 'Nueces', 'items' => [
                    ['cant' => 2, 'nombre' => 'Casado con pollo', 'precio' => 4500, 'nota' => 'Sin cebolla'],
                    ['cant' => 1, 'nombre' => 'Arroz con camarones', 'precio' => 5200],
                    ['cant' => 3, 'nombre' => 'Refresco natural', 'precio' => 1200],
                    ['cant' => 1, 'nombre' => 'Cheesecake', 'precio' => 2500],
                ]],
            ['codigo' => 'C-0143', 'mesa' => 3, 'personas' => 6, 'mesero' => 'Luis M.', 'minutos' => 65, 'estado' => 'en_preparacion',
                'items' => [
                    ['cant' => 3, 'nombre' => 'Arroz con camarones', 'precio' => 5200, 'nota' => 'Uno sin picante'],
                    ['cant' => 2, 'nombre' => 'Nachos con queso', 'precio' => 3200],
                    ['cant' => 4, 'nombre' => 'Refresco natural', 'precio' => 1200],
                ]],
            ['codigo' => 'C-0146', 'mesa' => 6, 'personas' => 2, 'mesero' => 'Ana M.', 'minutos' => 18, 'estado' => 'abierta',
                'items' => [
                    ['cant' => 1, 'nombre' => 'Ensalada César', 'precio' => 3000],
                    ['cant' => 1, 'nombre' => 'Casado con pollo', 'precio' => 4500],
                    ['cant' => 2, 'nombre' => 'Refresco natural', 'precio' => 1200],
                ]],
            ['codigo' => 'C-0147', 'mesa' => 7, 'personas' => 4, 'mesero' => 'Luis M.', 'minutos' => 33, 'estado' => 'servida',
                'items' => [
                    ['cant' => 2, 'nombre' => 'Nachos con queso', 'precio' => 3200],
                    ['cant' => 2, 'nombre' => 'Casado con pollo', 'precio' => 4500],
                    ['cant' => 2, 'nombre' => 'Refresco natural', 'precio' => 1200],
                ]],
            ['codigo' => 'C-0149', 'mesa' => 9, 'personas' => 3, 'mesero' => 'Carla R.', 'minutos' => 27, 'estado' => 'servida',
                'items' => [
                    ['cant' => 2, 'nombre' => 'Ensalada César', 'precio' => 3000],
                    ['cant' => 1, 'nombre' => 'Arroz con camarones', 'precio' => 5200],
                    ['cant' => 3, 'nombre' => 'Refresco natural', 'precio' => 1200],
                ]],
            ['codigo' => 'C-0152', 'mesa' => 12, 'personas' => 2, 'mesero' => 'Carla R.', 'minutos' => 55, 'estado' => 'en_preparacion',
                'items' => [
                    ['cant' => 1, 'nombre' => 'Arroz con camarones', 'precio' => 5200],
                    ['cant' => 1, 'nombre' => 'Casado con pollo', 'precio' => 4500],
                    ['cant' => 2, 'nombre' => 'Refresco natural', 'precio' => 1200],
                ]],
        ];

        $comandas = array_map(function (array $c) {
            $subtotal = array_sum(array_map(fn ($i) => $i['cant'] * $i['precio'], $c['items']));
            $c['subtotal'] = $subtotal;
            $c['iva'] = (int) round($subtotal * self::IVA);
            $c['servicio'] = (int) round($subtotal * self::SERVICIO);
            $c['total'] = $subtotal + $c['iva'] + $c['servicio'];
            $c['cantidad_items'] = array_sum(array_column($c['items'], 'cant'));
            $c['retraso'] = $c['estado'] !== 'servida' && $c['minutos'] > self::MINUTOS_RETRASO;

            return $c;
        }, $comandas);

        // Primero las atrasadas, después por tiempo en mesa (la más antigua arriba).
        usort($comandas, fn ($a, $b) => [$b['retraso'], $b['minutos']] <=> [$a['retraso'], $a['minutos']]);

        return $comandas;
    }

    public static function mesas(): array
    {
        $porMesa = [];
        foreach (self::comandas() as $c) {
            $porMesa[$c['mesa']] = $c;
        }

        $base = [
            [1, 'Interior', 4], [2, 'Interior', 4], [3, 'Interior', 6], [4, 'Interior', 4],
            [5, 'Interior', 2], [6, 'Interior', 2], [7, 'Terraza', 4], [8, 'Terraza', 6],
            [9, 'Terraza', 4], [10, 'Terraza', 8], [11, 'Barra', 2], [12, 'Barra', 2],
        ];

        $reservas = collect(self::reservas())->where('estado', 'confirmada')->keyBy('mesa');

        return array_map(function ($m) use ($porMesa, $reservas) {
            [$numero, $zona, $capacidad] = $m;
            $mesa = ['numero' => $numero, 'zona' => $zona, 'capacidad' => $capacidad, 'estado' => 'libre',
                'comanda' => null, 'reserva' => null];

            if (isset($porMesa[$numero])) {
                $mesa['estado'] = 'ocupada';
                $mesa['comanda'] = $porMesa[$numero];
            } elseif ($reservas->has($numero)) {
                $mesa['estado'] = 'reservada';
                $mesa['reserva'] = $reservas->get($numero);
            }

            return $mesa;
        }, $base);
    }

    public static function reservas(): array
    {
        return [
            ['nombre' => 'Fam. Rodríguez', 'personas' => 4, 'fecha' => 'Hoy', 'hora' => '20:30', 'mesa' => 4, 'zona' => 'Interior', 'estado' => 'confirmada'],
            ['nombre' => 'Grupo Vargas', 'personas' => 8, 'fecha' => 'Hoy', 'hora' => '21:00', 'mesa' => 10, 'zona' => 'Terraza', 'estado' => 'confirmada'],
            ['nombre' => 'Mariana Solís', 'personas' => 2, 'fecha' => 'Mañana', 'hora' => '19:00', 'mesa' => null, 'zona' => 'Barra', 'estado' => 'pendiente'],
            ['nombre' => 'Andrés Quesada', 'personas' => 5, 'fecha' => 'Mañana', 'hora' => '20:00', 'mesa' => null, 'zona' => 'Terraza', 'estado' => 'pendiente'],
        ];
    }

    /** Formato de colones de Costa Rica: ₡38.500 */
    public static function colones(int|float $monto): string
    {
        return '₡'.number_format($monto, 0, ',', '.');
    }

    public static function estadoComanda(string $estado): array
    {
        return match ($estado) {
            'abierta' => ['texto' => 'Abierta', 'tono' => 'info'],
            'en_preparacion' => ['texto' => 'En preparación', 'tono' => 'warning'],
            'servida' => ['texto' => 'Servida', 'tono' => 'success'],
            default => ['texto' => ucfirst($estado), 'tono' => 'neutral'],
        };
    }
}
