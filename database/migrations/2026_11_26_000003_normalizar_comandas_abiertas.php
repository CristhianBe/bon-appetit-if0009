<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cerrada = DB::table('estados_comanda')->where('codigo', 'cerrada')->value('id');
        $ocupada = DB::table('estados_mesa')->where('codigo', 'ocupada')->value('id');

        if (! $cerrada) {
            return;
        }

        $activas = DB::table('comandas')
            ->where('estado_comanda_id', '!=', $cerrada)
            ->orderByDesc('id')
            ->get(['id', 'mesa_id']);

        $conservadas = [];
        foreach ($activas as $comanda) {
            $mesaOcupada = (int) DB::table('mesas')->where('id', $comanda->mesa_id)->value('estado_mesa_id') === (int) $ocupada;

            if ($mesaOcupada && ! isset($conservadas[$comanda->mesa_id])) {
                $conservadas[$comanda->mesa_id] = true;
                continue;
            }

            $subtotal = (float) DB::table('detalle_comandas')
                ->where('comanda_id', $comanda->id)
                ->sum(DB::raw('cantidad * precio_unitario'));

            DB::table('comandas')->where('id', $comanda->id)->update([
                'estado_comanda_id' => $cerrada,
                'total' => $subtotal >= 15000 ? round($subtotal * 0.9, 2) : $subtotal,
                'cerrada_en' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
    }
};
