<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Fecha a partir de la cual un producto entra al menú (ej: un platillo de temporada
            // que se agrega hoy pero no debe verse en el menú hasta la semana que viene).
            // Nullable: la mayoría de los productos actuales están disponibles desde siempre,
            // no tiene sentido obligar a capturar esta fecha en cada producto.
            $table->date('disponible_desde')->nullable()->after('disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('disponible_desde');
        });
    }
};
