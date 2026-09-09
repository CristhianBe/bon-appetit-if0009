<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetodoPagoStoreRequest;
use App\Http\Requests\MetodoPagoUpdateRequest;
use App\Http\Resources\MetodoPagoResource;
use App\Models\MetodoPago;
use Illuminate\Http\Response;

class MetodoPagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MetodoPagoResource::collection(MetodoPago::orderBy('nombre')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetodoPagoStoreRequest $request)
    {
        $metodoPago = MetodoPago::create($request->validated());

        return MetodoPagoResource::make($metodoPago)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoPago $metodoPago)
    {
        return MetodoPagoResource::make($metodoPago);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetodoPagoUpdateRequest $request, MetodoPago $metodoPago)
    {
        $metodoPago->update($request->validated());

        return MetodoPagoResource::make($metodoPago->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        return response()->noContent();
    }
}
