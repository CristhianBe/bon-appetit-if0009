<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $categorias) {}

    public function index(Request $request)
    {
        $porPagina = min((int) $request->input('per_page', 15), 100);

        $categorias = Categoria::query()
            ->when($request->q, fn ($q, $t) => $q->where('nombre', 'like', "%{$t}%"))
            ->orderBy($request->input('sort', 'nombre'), $request->input('dir', 'asc'))
            ->paginate($porPagina);

        return CategoriaResource::collection($categorias);
    }

    public function store(GuardarCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return (new CategoriaResource($categoria))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('categorias.show', $categoria));
    }

    public function show(Categoria $categoria)
    {
        return new CategoriaResource($categoria);
    }

    public function update(GuardarCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return new CategoriaResource($categoria->fresh());
    }

    // La regla real ("no borrar si tiene productos asociados") vive en CategoriaService::eliminar.
    public function destroy(Categoria $categoria)
    {
        $this->categorias->eliminar($categoria);

        return response()->noContent();
    }
}
