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
    public function index()
    {
        return UserResource::collection(User::with('rol')->orderBy('name')->paginate(15));
    }

    public function store(UserStoreRequest $request)
    {
        $datos = $request->validated();
        $datos['password'] = Hash::make($datos['password']);

        $user = User::create($datos);

        return UserResource::make($user->load('rol'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user)
    {
        return UserResource::make($user->load('rol'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $datos = $request->validated();

        if (isset($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $user->update($datos);

        return UserResource::make($user->refresh()->load('rol'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}