<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoMesa extends Model
{
    protected $table = 'estados_mesa';
    protected $fillable = ['codigo', 'nombre'];
}
