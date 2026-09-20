<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstadoComandaStoreRequest;
use App\Http\Requests\EstadoComandaUpdateRequest;
use App\Http\Resources\EstadoComandaResource;
use App\Models\EstadoComanda;
use Illuminate\Http\Response;

class EstadoComandaController extends Controller
{
    public function index()
    {
        return EstadoComandaResource::collection(EstadoComanda::orderBy('nombre')->paginate(15));
    }

    public function store(EstadoComandaStoreRequest $request)
    {
        $estadoComanda = EstadoComanda::create($request->validated());

        return EstadoComandaResource::make($estadoComanda)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(EstadoComanda $estadoComanda)
    {
        return EstadoComandaResource::make($estadoComanda);
    }

    public function update(EstadoComandaUpdateRequest $request, EstadoComanda $estadoComanda)
    {
        $estadoComanda->update($request->validated());

        return EstadoComandaResource::make($estadoComanda->refresh());
    }

    public function destroy(EstadoComanda $estadoComanda)
    {
        $estadoComanda->delete();

        return response()->noContent();
    }
}