<?php

namespace App\Http\Controllers;

use App\Models\DetalleComanda;
use App\Http\Resources\DetalleComandaResource;
use Illuminate\Http\Request;

class DetalleComandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Obtener los detalles de una comanda específica (Ruta Anidada - Lab 5)
     */
    public function indexByComanda($comandaId)
    {
        $detalles = DetalleComanda::where('comanda_id', $comandaId)
            ->with('producto')
            ->get();

        return DetalleComandaResource::collection($detalles);
    }

    /**
     * Agregar un detalle a una comanda específica (Ruta Anidada - Lab 5)
     */
    public function storeByComanda(Request $request, $comandaId)
    {
        $data = $request->validate([
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'precio_unitario'=> 'required|numeric',
            'notas'          => 'nullable|string',
        ]);

        $data['comanda_id'] = $comandaId;
        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];

        $detalle = DetalleComanda::create($data);

        return (new DetalleComandaResource($detalle))
            ->response()
            ->setStatusCode(201);
    }
}