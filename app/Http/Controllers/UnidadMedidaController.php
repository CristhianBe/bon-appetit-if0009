<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnidadMedidaStoreRequest;
use App\Http\Requests\UnidadMedidaUpdateRequest;
use App\Http\Resources\UnidadMedidaResource;
use App\Models\UnidadMedida;
use Illuminate\Http\Response;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        return UnidadMedidaResource::collection(UnidadMedida::orderBy('nombre')->paginate(15));
    }

    public function store(UnidadMedidaStoreRequest $request)
    {
        $unidadMedida = UnidadMedida::create($request->validated());

        return UnidadMedidaResource::make($unidadMedida)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(UnidadMedida $unidadMedida)
    {
        return UnidadMedidaResource::make($unidadMedida);
    }

    public function update(UnidadMedidaUpdateRequest $request, UnidadMedida $unidadMedida)
    {
        $unidadMedida->update($request->validated());

        return UnidadMedidaResource::make($unidadMedida->refresh());
    }

    public function destroy(UnidadMedida $unidadMedida)
    {
        $unidadMedida->delete();

        return response()->noContent();
    }
}