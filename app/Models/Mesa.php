<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mesa extends Model
{
      use HasFactory;
    protected $fillable = ['numero', 'capacidad', 'zona', 'estado_mesa_id'];

public function estado(): BelongsTo
{
    return $this->belongsTo(EstadoMesa::class, 'estado_mesa_id');
}


public function comandas(): HasMany
{
    return $this->hasMany(Comanda::class);
}

/**
 * La comanda que hoy está "viva" en la mesa (cualquiera que no esté cerrada).
 * Sirve para que el salón muestre quién atiende y cuánto lleva consumido.
 */
public function comandaActiva(): HasOne
{
    return $this->hasOne(Comanda::class)
        ->whereHas('estadoComanda', fn ($q) => $q->where('codigo', '!=', 'cerrada'))
        ->latest('id');
}

public function reservas(): HasMany
{
    return $this->hasMany(Reserva::class);
}
}
