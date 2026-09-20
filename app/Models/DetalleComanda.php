<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleComanda extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleComandaFactory> */
    use HasFactory;

    protected $fillable = ['comanda_id', 'producto_id', 'cantidad', 'precio_unitario', 'notas'];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
