<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Laravel no lanza BadRequestHttpException cuando el body de una petición JSON viene mal
 * formado: simplemente lo interpreta como vacío, y la petición sigue hasta el FormRequest,
 * que termina fallando por campos "requeridos" faltantes -> 422 (reportado en Lab 5). Este
 * middleware corre antes que cualquier FormRequest y valida el JSON crudo, para que un body
 * mal formado responda 400 de verdad, con el "codigo": "solicitud_invalida" que ya arma
 * bootstrap/app.php para BadRequestHttpException.
 */
class ValidarJsonMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->traeCuerpoJson($request)) {
            json_decode($request->getContent());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException(
                    'El cuerpo de la solicitud no es JSON válido: '.json_last_error_msg().'.'
                );
            }
        }

        return $next($request);
    }

    private function traeCuerpoJson(Request $request): bool
    {
        return $request->isJson() && $request->getContent() !== '';
    }
}
