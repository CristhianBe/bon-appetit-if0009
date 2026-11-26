<?php

namespace App\Http\Controllers;

use App\Http\Requests\CobrarComandaRequest;
use App\Http\Requests\ComandaStoreRequest;
use App\Http\Requests\ComandaUpdateRequest;
use App\Http\Resources\ComandaResource;
use App\Models\Comanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComandaController extends Controller
{
    private const RELACIONES = ['mesa.estado', 'mesero', 'estadoComanda', 'metodoPago', 'detalles.producto'];

    public function __construct(private ComandaService $comandaService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Comanda::class);

        $comandas = $this->comandaService->listar(
            $request->only(['mesa_id', 'estado_comanda_id', 'activas', 'por_pagina']),
            $request->user(),
        );

        return ComandaResource::collection($comandas);
    }

    public function store(ComandaStoreRequest $request)
    {
        $this->authorize('create', Comanda::class);

        $comanda = $this->comandaService->abrir($request->validated(), $request->user());

        return ComandaResource::make($comanda->load(self::RELACIONES))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('comandas.show', $comanda));
    }

    public function show(Comanda $comanda, Request $request)
    {
        $this->authorize('view', $comanda);

        return ComandaResource::make($comanda->load(self::RELACIONES));
    }

    public function update(ComandaUpdateRequest $request, Comanda $comanda)
    {
        $this->authorize('update', $comanda);

        $comanda = $this->comandaService->actualizar($comanda, $request->validated(), $request->user());

        return ComandaResource::make($comanda->load(self::RELACIONES));
    }

    public function destroy(Comanda $comanda, Request $request)
    {
        $this->authorize('delete', $comanda);

        $this->comandaService->eliminar($comanda, $request->user());

        return response()->noContent();
    }

    public function cobrar(CobrarComandaRequest $request, Comanda $comanda)
    {
        $this->authorize('cobrar', $comanda);

        $comanda = $this->comandaService->cobrar($comanda, (int) $request->validated('metodo_pago_id'), $request->user());

        return ComandaResource::make($comanda->load(self::RELACIONES));
    }
}
