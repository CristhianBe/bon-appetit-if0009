<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservaStoreRequest;
use App\Http\Requests\ReservaUpdateRequest;
use App\Http\Resources\ReservaResource;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Reservaciones. Quién puede qué se decide en routes/api.php con el middleware "rol":
 * leer = cualquier usuario con sesión; crear/editar = administrador o mesero; borrar = administrador.
 */
class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}

    public function index(Request $request)
    {
        return ReservaResource::collection(
            $this->reservaService->listar($request->only(['fecha', 'desde', 'estado', 'mesa_id', 'por_pagina']))
        );
    }

    public function store(ReservaStoreRequest $request)
    {
        $reserva = $this->reservaService->crear($request->validated(), $request->user());

        return ReservaResource::make($reserva)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('reservas.show', $reserva));
    }

    public function show(Reserva $reserva)
    {
        return ReservaResource::make($reserva->load('mesa'));
    }

    public function update(ReservaUpdateRequest $request, Reserva $reserva)
    {
        return ReservaResource::make($this->reservaService->actualizar($reserva, $request->validated()));
    }

    public function destroy(Reserva $reserva)
    {
        $this->reservaService->eliminar($reserva);

        return response()->noContent();
    }
}
