<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaStoreRequest;
use App\Http\Requests\MesaUpdateRequest;
use App\Http\Resources\MesaResource;
use App\Models\Mesa;
use Illuminate\Http\Response;

class MesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MesaResource::collection(Mesa::with('estado')->orderBy('numero')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MesaStoreRequest $request)
    {
        $mesa = Mesa::create($request->validated());

        return MesaResource::make($mesa->load('estado'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Mesa $mesa)
    {
        return MesaResource::make($mesa->load('estado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MesaUpdateRequest $request, Mesa $mesa)
    {
        $mesa->update($request->validated());

        return MesaResource::make($mesa->refresh()->load('estado'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mesa $mesa)
    {
        $mesa->delete();

        return response()->noContent();
    }
}
