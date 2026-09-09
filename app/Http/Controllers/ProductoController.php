<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productoService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productos = $this->productoService->listar(
            $request->only(['categoria', 'solo_disponibles', 'q', 'ordenar_por', 'direccion', 'por_pagina'])
        );

        return ProductoResource::collection($productos->load('categoria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductoStoreRequest $request)
    {
        $producto = $this->productoService->crear($request->validated());

        return ProductoResource::make($producto->load('categoria'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        return ProductoResource::make($producto->load('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductoUpdateRequest $request, Producto $producto)
    {
        $producto = $this->productoService->actualizar($producto, $request->validated());

        return ProductoResource::make($producto->load('categoria'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $this->productoService->eliminar($producto);

        return response()->noContent();
    }
}
