<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a uno o varios roles: ->middleware('rol:administrador,mesero').
 *
 * Es la "Capa 0" de autorización: corta antes de llegar al controlador. Las reglas finas
 * por recurso (ej. "el mesero solo toca SUS comandas") siguen viviendo en las Policies.
 * Un usuario sin el rol recibe 403 con el mismo formato JSON que el resto de la API.
 */
class RequiereRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->hasAnyRole($roles)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }
}
