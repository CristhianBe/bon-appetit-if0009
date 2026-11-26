<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function index()
    {
        return RoleResource::collection(Role::orderBy('name')->paginate(15));
    }

    public function store(RoleStoreRequest $request)
    {
        $datos = $request->validated();

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

    public function show(Role $role)
    {
        return RoleResource::make($role);
    }

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

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }
}
