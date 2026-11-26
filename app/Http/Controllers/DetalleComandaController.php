<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetalleComandaStoreRequest;
use App\Http\Requests\DetalleComandaUpdateRequest;
use App\Http\Resources\DetalleComandaResource;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetalleComandaController extends Controller
{
    public function __construct(private ComandaService $comandaService) {}

    public function index(Comanda $comanda)
    {
        $this->authorize('view', $comanda);

        $detalles = $comanda->detalles()->with('producto')->latest()->paginate(15);

        return DetalleComandaResource::collection($detalles);
    }

    public function store(DetalleComandaStoreRequest $request, Comanda $comanda)
    {
        $this->authorize('update', $comanda);

        $detalle = $this->comandaService->agregarDetalle($comanda, $request->validated(), $request->user());

        return DetalleComandaResource::make($detalle->load('producto'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('detalles.show', $detalle));
    }

    public function show(DetalleComanda $detalleComanda)
    {
        $this->authorize('view', $detalleComanda->comanda);

        return DetalleComandaResource::make($detalleComanda->load('producto'));
    }

    public function update(DetalleComandaUpdateRequest $request, DetalleComanda $detalleComanda)
    {
        $this->authorize('update', $detalleComanda->comanda);

        $detalle = $this->comandaService->actualizarDetalle($detalleComanda, $request->validated(), $request->user());

        return DetalleComandaResource::make($detalle->load('producto'));
    }

    public function destroy(DetalleComanda $detalleComanda, Request $request)
    {
        $this->authorize('update', $detalleComanda->comanda);

        $this->comandaService->quitarDetalle($detalleComanda->comanda, $detalleComanda, $request->user());

        return response()->noContent();
    }
}
