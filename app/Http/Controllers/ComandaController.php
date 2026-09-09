<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComandaStoreRequest;
use App\Http\Requests\ComandaUpdateRequest;
use App\Http\Resources\ComandaResource;
use App\Models\Comanda;
use App\Models\EstadoComanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComandaController extends Controller
{
    private const RELACIONES = ['mesa.estado', 'mesero', 'estadoComanda', 'detalles.producto'];

    public function __construct(private ComandaService $comandaService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $comandas = $this->comandaService->listar($request->only(['mesa_id', 'estado_comanda_id', 'por_pagina']));

        return ComandaResource::collection($comandas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComandaStoreRequest $request)
    {
        $comanda = $this->comandaService->abrir($request->validated());

        return ComandaResource::make($comanda->load(self::RELACIONES))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comanda $comanda)
    {
        return ComandaResource::make($comanda->load(self::RELACIONES));
    }

    /**
     * Update the specified resource in storage.
     *
     * Si el cambio de estado corresponde al estado "cerrada", se delega en
     * ComandaService::cerrar() para aplicar la regla de negocio (no cerrar sin
     * detalle, cálculo del descuento y liberación de la mesa). Cualquier otro
     * cambio se aplica directamente.
     */
    public function update(ComandaUpdateRequest $request, Comanda $comanda)
    {
        $datos = $request->validated();

        if (isset($datos['estado_comanda_id'])) {
            $estado = EstadoComanda::find($datos['estado_comanda_id']);

            if ($estado && $estado->codigo === 'cerrada') {
                $comanda = $this->comandaService->cerrar($comanda);

                return ComandaResource::make($comanda->load(self::RELACIONES));
            }
        }

        $comanda->update($datos);

        return ComandaResource::make($comanda->refresh()->load(self::RELACIONES));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comanda $comanda)
    {
        $comanda->delete();

        return response()->noContent();
    }
}
