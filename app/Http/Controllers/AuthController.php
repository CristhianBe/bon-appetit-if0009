<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarPasswordRequest;
use App\Http\Requests\IniciarSesionRequest;
use App\Http\Requests\RegistrarUsuarioRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegistrarUsuarioRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function login(IniciarSesionRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $abilities = $user->hasRole('administrador') || $user->hasRole('mesero')
            ? ['comandas:leer', 'comandas:escribir']
            : ['comandas:leer'];

        $token = $user->createToken(
            name: 'api',
            abilities: $abilities,
            expiresAt: now()->addMinutes(30),
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->roles->first()?->name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function cambiarPassword(CambiarPasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->password_actual, $user->password)) {
            return response()->json(['message' => 'La contraseña actual no coincide.'], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->noContent();
    }
}
