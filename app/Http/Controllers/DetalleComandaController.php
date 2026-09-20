<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetalleComandaStoreRequest;
use App\Http\Requests\DetalleComandaUpdateRequest;
use App\Http\Resources\DetalleComandaResource;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Services\ComandaService;
use Illuminate\Http\Response;

class DetalleComandaController extends Controller
{
    public function __construct(private ComandaService $comandaService) {}

    /**
     * Display a listing of the resource.
     *
     * Anidado bajo /comandas/{comanda}/detalles.
     */
    public function index(Comanda $comanda)
    {
        $detalles = $comanda->detalles()->with('producto')->latest()->paginate(15);

        return DetalleComandaResource::collection($detalles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Anidado bajo /comandas/{comanda}/detalles: la comanda ya viene resuelta
     * por el model binding de la ruta, no hace falta repetirla en el body.
     */
    public function store(DetalleComandaStoreRequest $request, Comanda $comanda)
    {
        $detalle = $this->comandaService->agregarDetalle($comanda, $request->validated());

        return DetalleComandaResource::make($detalle->load('producto'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('detalles.show', $detalle));
    }

    /**
     * Display the specified resource.
     *
     * Ruta "shallow": /detalles/{detalleComanda}, sin repetir la comanda.
     */
    public function show(DetalleComanda $detalleComanda)
    {
        return DetalleComandaResource::make($detalleComanda->load('producto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetalleComandaUpdateRequest $request, DetalleComanda $detalleComanda)
    {
        $detalle = $this->comandaService->actualizarDetalle($detalleComanda, $request->validated());

        return DetalleComandaResource::make($detalle->load('producto'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleComanda $detalleComanda)
    {
        $this->comandaService->quitarDetalle($detalleComanda->comanda, $detalleComanda);

        return response()->noContent();
    }
}
