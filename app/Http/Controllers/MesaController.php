<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaStoreRequest;
use App\Http\Requests\MesaUpdateRequest;
use App\Http\Resources\ComandaResource;
use App\Http\Resources\MesaResource;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MesaController extends Controller
{
    public function index()
    {
        return MesaResource::collection(Mesa::with('estado')->orderBy('numero')->paginate(15));
    }

    public function store(MesaStoreRequest $request)
    {
        $mesa = Mesa::create($request->validated());

        return MesaResource::make($mesa->load('estado'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
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
        $mesa->delete();

        return response()->noContent();
    }

    public function comandas($id)
    {
        $mesa = Mesa::findOrFail($id);
        $comandas = $mesa->comandas()->paginate(10);

        return ComandaResource::collection($comandas);
    }
}