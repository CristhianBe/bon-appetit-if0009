<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetalleComandaStoreRequest;
use App\Http\Requests\DetalleComandaUpdateRequest;
use App\Http\Resources\DetalleComandaResource;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetalleComandaController extends Controller
{
    public function __construct(private ComandaService $comandaService) {}

    public function index()
    {
        $detalles = DetalleComanda::with('producto')->latest()->paginate(15);

        return DetalleComandaResource::collection($detalles);
    }


    public function store(DetalleComandaStoreRequest $request)
    {
        $datos = $request->validated();
        $comanda = Comanda::findOrFail($datos['comanda_id']);

        $detalle = $this->comandaService->agregarDetalle($comanda, $datos);

        return DetalleComandaResource::make($detalle->load('producto'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    public function show(DetalleComanda $detalleComanda)
    {
        return DetalleComandaResource::make($detalleComanda->load('producto'));
    }


    public function update(DetalleComandaUpdateRequest $request, DetalleComanda $detalleComanda)
    {
        $detalle = $this->comandaService->actualizarDetalle($detalleComanda, $request->validated());

        return DetalleComandaResource::make($detalle->load('producto'));
    }


    public function destroy(DetalleComanda $detalleComanda)
    {
        $this->comandaService->quitarDetalle($detalleComanda->comanda, $detalleComanda);

        return response()->noContent();
    }


    public function indexByComanda($comandaId)
    {
        $detalles = DetalleComanda::where('comanda_id', $comandaId)
            ->with('producto')
            ->paginate(10);

        return DetalleComandaResource::collection($detalles);
    }

    public function storeByComanda(Request $request, $comandaId)
    {
        $data = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'cantidad'        => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric',
            'notas'           => 'nullable|string',
        ]);

        $data['comanda_id'] = $comandaId;
        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];

        $detalle = DetalleComanda::create($data);

        return (new DetalleComandaResource($detalle))
            ->response()
            ->setStatusCode(201);
    }
}