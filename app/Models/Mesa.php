<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $fillable = ['numero', 'capacidad', 'estado_mesa_id'];

public function estado(): BelongsTo
{
    return $this->belongsTo(EstadoMesa::class, 'estado_mesa_id');
}


public function comandas(): HasMany
{
    return $this->hasMany(Comanda::class);
}
}
