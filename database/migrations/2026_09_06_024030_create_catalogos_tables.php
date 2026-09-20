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
    Schema::create('estados_mesa', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 20)->unique();
        $table->string('nombre', 50);
        $table->timestamps();
    });

    Schema::create('estados_comanda', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 20)->unique();
        $table->string('nombre', 50);
        $table->timestamps();
    });

    Schema::create('metodos_pago', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 20)->unique();
        $table->string('nombre', 50);
        $table->timestamps();
    });

    Schema::create('unidades_medida', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 10)->unique();
        $table->string('nombre', 30);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('unidades_medida');
    Schema::dropIfExists('metodos_pago');
    Schema::dropIfExists('estados_comanda');
    Schema::dropIfExists('estados_mesa');
}
};