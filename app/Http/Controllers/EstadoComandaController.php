<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstadoComandaStoreRequest;
use App\Http\Requests\EstadoComandaUpdateRequest;
use App\Http\Resources\EstadoComandaResource;
use App\Models\EstadoComanda;
use Illuminate\Http\Response;

class EstadoComandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return EstadoComandaResource::collection(EstadoComanda::orderBy('nombre')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EstadoComandaStoreRequest $request)
    {
        $estadoComanda = EstadoComanda::create($request->validated());

        return EstadoComandaResource::make($estadoComanda)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(EstadoComanda $estadoComanda)
    {
        return EstadoComandaResource::make($estadoComanda);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EstadoComandaUpdateRequest $request, EstadoComanda $estadoComanda)
    {
        $estadoComanda->update($request->validated());

        return EstadoComandaResource::make($estadoComanda->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EstadoComanda $estadoComanda)
    {
        $estadoComanda->delete();

        return response()->noContent();
    }
}
