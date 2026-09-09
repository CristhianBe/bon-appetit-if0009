<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaStoreRequest;
use App\Http\Requests\CategoriaUpdateRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $categoriaService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categorias = $this->categoriaService->listar($request->only(['q', 'por_pagina']));

        return CategoriaResource::collection($categorias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriaStoreRequest $request)
    {
        $categoria = $this->categoriaService->crear($request->validated());

        return CategoriaResource::make($categoria)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $category)
    {
        return CategoriaResource::make($category->load('productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriaUpdateRequest $request, Categoria $category)
    {
        $categoria = $this->categoriaService->actualizar($category, $request->validated());

        return CategoriaResource::make($categoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $category)
    {
        $this->categoriaService->eliminar($category);

        return response()->noContent();
    }
}
