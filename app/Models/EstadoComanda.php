<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoComanda extends Model
{
    protected $table = 'estados_comanda';
    protected $fillable = ['codigo', 'nombre'];
}
