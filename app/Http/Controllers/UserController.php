<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Exceptions\ReglaNegocioException;
use App\Models\Comanda;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $porPagina = max(min((int) $request->query('por_pagina', 15), 100), 1);

        return UserResource::collection(User::with('roles')->orderBy('name')->paginate($porPagina));
    }

    public function store(UserStoreRequest $request)
    {
        $datos = $request->validated();
        $rol = $datos['rol'] ?? null;
        unset($datos['rol']);

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

    public function show(User $user)
    {
        return UserResource::make($user->load('roles'));
    }

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

        if ($rolProvisto) {
            $user->syncRoles($rol ? [$rol] : []);
        }

        return UserResource::make($user->refresh()->load('roles'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            throw new ReglaNegocioException('No puede eliminar su propia cuenta.', 'usuario_autoeliminacion', 409);
        }

        if (Comanda::where('user_id', $user->id)->exists() || Reserva::where('user_id', $user->id)->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar un usuario que ya registró comandas o reservas.',
                'usuario_con_actividad',
                409,
            );
        }

        $user->delete();

        return response()->noContent();
    }
}
