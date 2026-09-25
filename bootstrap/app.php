<?php

use App\Exceptions\ReglaNegocioException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Corre antes que cualquier FormRequest, para que un JSON mal formado en el body
        // responda 400 en vez de terminar fallando la validación normal con 422.
        $middleware->api(prepend: [
            \App\Http\Middleware\ValidarJsonMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Punto único de traducción excepción -> código HTTP (Lab 5). Cada
        // familia de excepción se traduce en un solo lugar, así ningún
        // controlador necesita un try/catch propio ni deja pasar una traza
        // de pila en la respuesta.

        // Regla de negocio violada (Lab 4) -> 409, con el código propio de la regla.
        $exceptions->render(function (ReglaNegocioException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'codigo' => $e->codigo(),
                ], $e->statusSugerido());
            }
        });

        // Recurso inexistente (model binding o findOrFail) -> 404 limpio,
        // sin el mensaje interno de Eloquent ni la traza.
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'El recurso solicitado no existe.',
                    'codigo' => 'recurso_no_encontrado',
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'El recurso solicitado no existe.',
                    'codigo' => 'recurso_no_encontrado',
                ], 404);
            }
        });

        // Sin autenticar -> 401. No indica si la cuenta existe o no, para no
        // permitir enumerar usuarios.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado.',
                    'codigo' => 'no_autenticado',
                ], 401);
            }
        });

        // Autenticado pero sin permiso sobre el recurso -> 403.
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'No tiene permisos para realizar esta acción.',
                    'codigo' => 'no_autorizado',
                ], 403);
            }
        });

        // Validación fallida -> 422 con el detalle por campo (Laravel ya lo
        // arma así; se deja explícito para que el formato quede documentado
        // y no dependa del comportamiento implícito del framework).
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Los datos enviados no son válidos.',
                    'codigo' => 'validacion_fallida',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Solicitud mal formada (ej. JSON inválido en el body) -> 400.
        $exceptions->render(function (BadRequestHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'La solicitud contiene una sintaxis inválida.',
                    'codigo' => 'solicitud_invalida',
                ], 400);
            }
        });
    })->create();
