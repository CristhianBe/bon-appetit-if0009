<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comanda extends Model
{
    use HasFactory;
    protected $fillable = ['mesa_id', 'user_id', 'estado_comanda_id', 'total', 'cerrada_en'];

protected $casts = [
    'total' => 'decimal:2',
    'cerrada_en' => 'datetime',
];

public function mesa(): BelongsTo
{
    return $this->belongsTo(Mesa::class);
}

public function mesero(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

public function estado(): BelongsTo
{
    return $this->belongsTo(EstadoComanda::class, 'estado_comanda_id');
}

public function detalles(): HasMany
{
    return $this->hasMany(DetalleComanda::class);
}
}
