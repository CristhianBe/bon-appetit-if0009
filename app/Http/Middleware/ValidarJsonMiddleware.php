<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
