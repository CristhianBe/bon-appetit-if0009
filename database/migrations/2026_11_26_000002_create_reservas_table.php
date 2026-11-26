<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            // La mesa puede quedar sin asignar (el cliente solo pidió una zona); si se borra la mesa, la reserva se conserva.
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // quien la registró
            $table->string('nombre', 100);
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->unsignedSmallInteger('personas');
            $table->string('zona', 40)->nullable();
            $table->date('fecha');
            $table->string('hora', 5); // "HH:MM"
            $table->string('estado', 20)->default('pendiente'); // pendiente | confirmada | cumplida | cancelada
            $table->string('notas', 255)->nullable();
            $table->timestamps();

            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
