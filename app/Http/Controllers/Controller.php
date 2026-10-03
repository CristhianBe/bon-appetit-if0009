<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Da acceso a $this->authorize('accion', $modelo) en los controladores: busca la Policy
    // del modelo por convención de nombres (App\Models\Comanda -> App\Policies\ComandaPolicy)
    // y, si el método de la policy devuelve false, lanza AuthorizationException -> Laravel la
    // traduce sola a HTTP 403.
    use AuthorizesRequests;
}
