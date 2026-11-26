<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conexión del front-end con el backend:
 *  - mesas.zona: el salón se organiza por zonas (Salón, Terraza, Barra…).
 *  - comandas.metodo_pago_id: cómo pagó el cliente al cobrar (lo registra el cajero).
 *  - comandas.personas / notas: datos que el mesero captura al abrir la mesa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->string('zona', 40)->default('Salón')->after('capacidad');
        });

        Schema::table('comandas', function (Blueprint $table) {
            $table->foreignId('metodo_pago_id')->nullable()->after('estado_comanda_id')
                ->constrained('metodos_pago')->nullOnDelete();
            $table->unsignedSmallInteger('personas')->nullable()->after('metodo_pago_id');
            $table->string('notas', 255)->nullable()->after('personas');
        });
    }

    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('metodo_pago_id');
            $table->dropColumn(['personas', 'notas']);
        });

        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn('zona');
        });
    }
};
