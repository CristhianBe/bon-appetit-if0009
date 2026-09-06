<?php

namespace App\Console\Commands;

use App\Models\Comanda;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('demo:n1')]
#[Description('Compara la cantidad de consultas SQL con y sin eager loading (evidencia del problema N+1)')]
class DemoConsultasN1 extends Command
{
    public function handle(): void
    {
        $fase = 'sin_eager';
        $conteo = ['sin_eager' => 0, 'con_eager' => 0];

        DB::listen(function ($query) use (&$conteo, &$fase) {
            $conteo[$fase]++;
        });

        // --- SIN eager loading: cada acceso a ->detalles y ->producto dispara una consulta nueva ---
        $fase = 'sin_eager';
        $comandas = Comanda::take(10)->get();
        foreach ($comandas as $comanda) {
            foreach ($comanda->detalles as $detalle) {
                $nombreProducto = $detalle->producto->nombre;
            }
        }

        // --- CON eager loading: las relaciones se precargan en 2 consultas extra, sin importar cuántas comandas haya ---
        $fase = 'con_eager';
        $comandas = Comanda::with('detalles.producto')->take(10)->get();
        foreach ($comandas as $comanda) {
            foreach ($comanda->detalles as $detalle) {
                $nombreProducto = $detalle->producto->nombre;
            }
        }

        $this->info("Consultas SIN eager loading: {$conteo['sin_eager']}");
        $this->info("Consultas CON eager loading: {$conteo['con_eager']}");
    }
}