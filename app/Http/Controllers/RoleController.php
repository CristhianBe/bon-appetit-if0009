<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return RoleResource::collection(Role::orderBy('name')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreRequest $request)
    {
        $datos = $request->validated();

        // El request valida "nombre" (contrato externo); el modelo lo guarda como "name"
        // (columna real de spatie/laravel-permission). "descripcion" sí se llama igual en los
        // dos lados, porque es un campo propio de este proyecto, no del paquete.
        //
        // "guard_name" se fija explícito (no se deja en manos del default): Sanctum::actingAs()
        // en las pruebas (y potencialmente otros flujos autenticados) cambian
        // config('auth.defaults.guard') a "sanctum" mientras dura la petición, y Spatie usa
        // justo ese valor por defecto al crear un rol si no se le da uno — sin esto, un rol
        // creado durante una sesión autenticada terminaría con guard_name="sanctum" en vez de
        // "web", y After Role::users() fallaría más tarde (guard "sanctum" no tiene un
        // "provider" configurado en auth.guards).
        $role = Role::create([
            'name' => $datos['nombre'],
            'guard_name' => 'web',
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return RoleResource::make($role)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('roles.show', $role));
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return RoleResource::make($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleUpdateRequest $request, Role $role)
    {
        $datos = $request->validated();

        if (array_key_exists('nombre', $datos)) {
            $datos['name'] = $datos['nombre'];
            unset($datos['nombre']);
        }

        $role->update($datos);

        return RoleResource::make($role->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }
}
