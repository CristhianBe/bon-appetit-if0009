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
    Schema::create('comandas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
        $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // mesero responsable
        $table->foreignId('estado_comanda_id')->constrained('estados_comanda')->restrictOnDelete();
        $table->decimal('total', 10, 2)->nullable();
        $table->timestamp('cerrada_en')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comandas');
    }
};
