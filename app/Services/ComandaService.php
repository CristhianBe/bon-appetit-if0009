<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Comanda;
use App\Models\EstadoComanda;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ComandaService
{
    // A partir de este monto (en colones) se aplica el descuento automático al cerrar la comanda.
    // Es la regla de "aplicar un descuento según un umbral" que pide el Laboratorio 4.
    private const UMBRAL_DESCUENTO = 20000;

    private const PORCENTAJE_DESCUENTO = 0.10;

    /**
     * Abre una comanda nueva junto con sus líneas de detalle, calculando el total a partir del
     * precio vigente de cada producto. Deja la mesa marcada como "ocupada".
     *
     * @param  array{mesa_id: int, user_id: int, detalles: array<int, array{producto_id: int, cantidad: int}>}  $datos
     */
    public function crear(array $datos): Comanda
    {
        $mesa = Mesa::findOrFail($datos['mesa_id']);

        // Regla de negocio propia del dominio: no se puede abrir una comanda nueva en una mesa
        // que ya está ocupada o reservada — cada mesa solo puede tener UNA comanda activa a la vez.
        if ($mesa->estado->codigo !== 'libre') {
            throw new ReglaNegocioException("La mesa {$mesa->numero} no está libre (estado actual: {$mesa->estado->nombre}).");
        }

        $estadoAbierta = EstadoComanda::where('codigo', 'abierta')->firstOrFail();
        $estadoOcupada = EstadoMesa::where('codigo', 'ocupada')->firstOrFail();

        // DB::transaction agrupa "crear la comanda + sus líneas + ocupar la mesa" como
        // todo-o-nada: si un producto no existe a mitad de camino, nada de esto queda guardado
        // (ni la comanda, ni las líneas ya creadas, ni el cambio de estado de la mesa).
        return DB::transaction(function () use ($datos, $mesa, $estadoAbierta, $estadoOcupada) {
            $comanda = Comanda::create([
                'mesa_id' => $mesa->id,
                'user_id' => $datos['user_id'],
                'estado_comanda_id' => $estadoAbierta->id,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($datos['detalles'] as $linea) {
                // Se toma el precio ACTUAL del producto (no uno que mande el cliente en el
                // request), para que nadie pueda mandar un precio inventado desde afuera.
                $precio = Producto::findOrFail($linea['producto_id'])->precio;
                $comanda->detalles()->create([...$linea, 'precio_unitario' => $precio]);
                $total += $precio * $linea['cantidad'];
            }

            $mesa->update(['estado_mesa_id' => $estadoOcupada->id]);

            return tap($comanda)->update(['total' => $total]);
        });
    }

    /**
     * Actualiza mesa y/o mesero responsable de una comanda existente (no toca las líneas).
     *
     * @param  array{mesa_id?: int, user_id?: int}  $datos
     */
    public function actualizar(Comanda $comanda, array $datos): Comanda
    {
        $comanda->update($datos);

        return $comanda->fresh(['mesa', 'mesero', 'estado', 'detalles']);
    }

    /**
     * Cierra una comanda: aplica el descuento por umbral si corresponde, libera la mesa y
     * marca la hora de cierre. Escritura múltiple (comandas + mesas) dentro de una transacción.
     */
    public function cerrar(Comanda $comanda): Comanda
    {
        // Regla: no se puede cerrar (facturar) una comanda sin ninguna línea de detalle.
        if ($comanda->detalles()->doesntExist()) {
            throw new ReglaNegocioException('No se puede cerrar una comanda sin detalle.');
        }

        // Regla: una comanda cerrada es un estado terminal — no se puede volver a cerrar
        // (evita, por ejemplo, aplicar el descuento dos veces sobre el mismo total).
        if ($comanda->estado->codigo === 'cerrada') {
            throw new ReglaNegocioException('La comanda ya está cerrada.');
        }

        return DB::transaction(function () use ($comanda) {
            $total = (float) $comanda->total;

            // Regla de "descuento según un umbral": si el consumo supera el monto definido,
            // se aplica el descuento antes de dejar el total en firme.
            if ($total > self::UMBRAL_DESCUENTO) {
                $total -= $total * self::PORCENTAJE_DESCUENTO;
            }

            $estadoCerrada = EstadoComanda::where('codigo', 'cerrada')->firstOrFail();
            $estadoLibre = EstadoMesa::where('codigo', 'libre')->firstOrFail();

            $comanda->update([
                'estado_comanda_id' => $estadoCerrada->id,
                'total' => $total,
                'cerrada_en' => now(),
            ]);

            // Al cerrar la comanda, la mesa vuelve a estar disponible para el siguiente cliente.
            $comanda->mesa->update(['estado_mesa_id' => $estadoLibre->id]);

            return $comanda->fresh(['mesa', 'mesero', 'estado', 'detalles']);
        });
    }

    /**
     * Elimina una comanda, siempre que no esté cerrada (ya forma parte del historial de ventas).
     */
    public function eliminar(Comanda $comanda): void
    {
        if ($comanda->estado->codigo === 'cerrada') {
            throw new ReglaNegocioException('No se puede eliminar una comanda cerrada; forma parte del historial de ventas.');
        }

        $comanda->delete();
    }
}
