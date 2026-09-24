<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::with('roles')->orderBy('name')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $datos = $request->validated();
        $rol = $datos['rol'] ?? null;
        unset($datos['rol']); // "rol" no es una columna de users: se asigna aparte, vía Spatie.

        $datos['password'] = Hash::make($datos['password']);

        $user = User::create($datos);

        if ($rol) {
            $user->assignRole($rol);
        }

        return UserResource::make($user->load('roles'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('usuarios.show', $user));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return UserResource::make($user->load('roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $datos = $request->validated();
        $rolProvisto = array_key_exists('rol', $datos);
        $rol = $datos['rol'] ?? null;
        unset($datos['rol']);

        if (isset($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $user->update($datos);

        // syncRoles() reemplaza el rol actual por el nuevo (o lo quita, si mandaron rol: null) —
        // coherente con el negocio de "un solo rol por usuario". Solo se toca si el campo vino
        // en la petición; si no vino, el rol actual queda intacto.
        if ($rolProvisto) {
            $user->syncRoles($rol ? [$rol] : []);
        }

        return UserResource::make($user->refresh()->load('roles'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}
