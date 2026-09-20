<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetodoPagoStoreRequest;
use App\Http\Requests\MetodoPagoUpdateRequest;
use App\Http\Resources\MetodoPagoResource;
use App\Models\MetodoPago;
use Illuminate\Http\Response;

class MetodoPagoController extends Controller
{
    public function index()
    {
        return MetodoPagoResource::collection(MetodoPago::orderBy('nombre')->paginate(15));
    }

    public function store(MetodoPagoStoreRequest $request)
    {
        $metodoPago = MetodoPago::create($request->validated());

        return MetodoPagoResource::make($metodoPago)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(MetodoPago $metodoPago)
    {
        return MetodoPagoResource::make($metodoPago);
    }

    public function update(MetodoPagoUpdateRequest $request, MetodoPago $metodoPago)
    {
        $metodoPago->update($request->validated());

        return MetodoPagoResource::make($metodoPago->refresh());
    }

    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        return response()->noContent();
    }
}