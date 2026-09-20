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
        return RoleResource::collection(Role::orderBy('nombre')->paginate(15));
    }

    public function store(RoleStoreRequest $request)
    {
        $role = Role::create($request->validated());

        return RoleResource::make($role)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Role $role)
    {
        return RoleResource::make($role);
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        $role->update($request->validated());

        return RoleResource::make($role->refresh());
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }
}