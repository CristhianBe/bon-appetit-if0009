<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaStoreRequest;
use App\Http\Requests\MesaUpdateRequest;
use App\Http\Resources\MesaResource;
use App\Exceptions\ReglaNegocioException;
use App\Models\EstadoMesa;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MesaController extends Controller
{
    public function index(Request $request)
    {
        $porPagina = max(min((int) $request->query('por_pagina', 15), 100), 1);

        $mesas = Mesa::with(['estado', 'comandaActiva.mesero', 'comandaActiva.estadoComanda', 'comandaActiva.detalles'])
            ->orderBy('numero')
            ->paginate($porPagina);

        return MesaResource::collection($mesas);
    }

    public function store(MesaStoreRequest $request)
    {
        $datos = $request->validated();
        $datos['estado_mesa_id'] ??= EstadoMesa::where('codigo', 'libre')->value('id');

        $mesa = Mesa::create($datos);

        return MesaResource::make($mesa->load('estado'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('mesas.show', $mesa));
    }

    public function show(Mesa $mesa)
    {
        return MesaResource::make($mesa->load('estado'));
    }

    public function update(MesaUpdateRequest $request, Mesa $mesa)
    {
        $mesa->update($request->validated());

        return MesaResource::make($mesa->refresh()->load('estado'));
    }

    public function destroy(Mesa $mesa)
    {
        if ($mesa->comandas()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar una mesa que ya tiene comandas registradas.',
                'mesa_con_comandas',
                409,
            );
        }

        $mesa->delete();

        return response()->noContent();
    }
}
