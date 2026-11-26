<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    use HasFactory;

    public const ESTADOS = ['pendiente', 'confirmada', 'cumplida', 'cancelada'];

    public const ESTADOS_ACTIVOS = ['pendiente', 'confirmada'];

    protected $fillable = [
        'mesa_id', 'user_id', 'nombre', 'telefono', 'correo', 'personas',
        'zona', 'fecha', 'hora', 'estado', 'notas',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'personas' => 'integer',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function registradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
