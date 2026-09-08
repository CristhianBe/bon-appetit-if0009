<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarComandaRequest;
use App\Http\Resources\ComandaResource;
use App\Models\Comanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;

class ComandaController extends Controller
{
    // Laravel inyecta ComandaService automáticamente (inyección de dependencias): no hace falta
    // escribir "new ComandaService()" en ningún lado.
    public function __construct(private ComandaService $comandas) {}

    // GET /api/comandas — lista paginada, con filtro por estado y por mesa.
    public function index(Request $request)
    {
        // Tope de página: sin importar lo que pida el cliente, nunca se sirven más de 100
        // registros por página (protege contra ?per_page=999999).
        $porPagina = min((int) $request->input('per_page', 15), 100);

        $comandas = Comanda::query()
            ->with(['mesa', 'mesero', 'estado'])
            ->when($request->mesa_id, fn ($q, $m) => $q->where('mesa_id', $m))
            ->when($request->estado, fn ($q, $e) => $q->whereHas('estado', fn ($q2) => $q2->where('codigo', $e)))
            ->orderBy($request->input('sort', 'created_at'), $request->input('dir', 'desc'))
            ->paginate($porPagina);

        return ComandaResource::collection($comandas);
    }

    // POST /api/comandas — abre una comanda nueva (la lógica real vive en ComandaService::crear).
    public function store(GuardarComandaRequest $request)
    {
        $comanda = $this->comandas->crear($request->validated());

        return (new ComandaResource($comanda->load(['mesa', 'mesero', 'estado', 'detalles'])))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('comandas.show', $comanda));
    }

    // GET /api/comandas/{id} — Laravel ya buscó la Comanda por su id (route model binding);
    // si no existe, responde 404 automáticamente sin que este método se llegue a ejecutar.
    public function show(Comanda $comanda)
    {
        return new ComandaResource($comanda->load(['mesa', 'mesero', 'estado', 'detalles']));
    }

    // PUT /api/comandas/{id} — solo permite editar mesa y mesero (no las líneas acá).
    public function update(GuardarComandaRequest $request, Comanda $comanda)
    {
        $comanda = $this->comandas->actualizar($comanda, $request->only(['mesa_id', 'user_id']));

        return new ComandaResource($comanda);
    }

    // DELETE /api/comandas/{id} — la regla de "se puede o no borrar" vive en
    // ComandaService::eliminar (lanza ReglaNegocioException → 409 si ya está cerrada).
    public function destroy(Comanda $comanda)
    {
        $this->comandas->eliminar($comanda);

        return response()->noContent(); // 204: borrada, sin contenido en la respuesta
    }
}
