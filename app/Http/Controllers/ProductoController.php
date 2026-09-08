<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\Request;

// Producto no tiene reglas de negocio propias (solo validación), así que el controlador habla
// directo con el modelo — igual que ClienteController en el proyecto de Pedidos. Compárese con
// ComandaController, que sí delega en ComandaService por las reglas de mesa/estado/descuento.
class ProductoController extends Controller
{
    // GET /api/productos — lista paginada, con búsqueda por nombre y filtro por categoría/disponibilidad.
    public function index(Request $request)
    {
        $porPagina = min((int) $request->input('per_page', 15), 100);

        $productos = Producto::query()
            ->with('categoria')
            ->when($request->q, fn ($q, $t) => $q->where('nombre', 'like', "%{$t}%"))
            ->when($request->categoria_id, fn ($q, $c) => $q->where('categoria_id', $c))
            ->when($request->has('disponible'), fn ($q) => $q->where('disponible', $request->boolean('disponible')))
            ->orderBy($request->input('sort', 'nombre'), $request->input('dir', 'asc'))
            ->paginate($porPagina);

        return ProductoResource::collection($productos);
    }

    // POST /api/productos
    public function store(GuardarProductoRequest $request)
    {
        $producto = Producto::create($request->validated());

        return (new ProductoResource($producto))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('productos.show', $producto));
    }

    // GET /api/productos/{id}
    public function show(Producto $producto)
    {
        return new ProductoResource($producto);
    }

    // PUT /api/productos/{id}
    public function update(GuardarProductoRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

        return new ProductoResource($producto->fresh());
    }

    // DELETE /api/productos/{id} — no tiene una regla de negocio propia todavía: si el producto
    // ya aparece en el detalle de alguna comanda, la base de datos lo rechaza sola
    // (restrictOnDelete en detalle_comandas.producto_id) y ese error ya llega como 500/409
    // genérico. Si el equipo lo pide, se puede repetir acá el mismo patrón de
    // CategoriaService::eliminar() para responder un 409 con mensaje en español.
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->noContent();
    }
}
