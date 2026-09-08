<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'disponible',
        'imagen_url',
        'disponible_desde',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
        'disponible_desde' => 'date',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('disponible', true);
    }

    public function scopeDeCategoria(Builder $query, string $nombreCategoria): Builder
    {
        return $query->whereHas('categoria', fn (Builder $q) => $q->where('nombre', $nombreCategoria));
    }
}
