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
    // POST /api/register — crea una cuenta nueva. A propósito NO devuelve un token:
    // registrarse y loguearse son dos acciones separadas (así lo pide el Laboratorio 6).
    public function register(RegistrarUsuarioRequest $request)
    {
        // password_confirmation no llega acá (solo se usó para comparar); el cast
        // 'password' => 'hashed' del modelo User encripta la contraseña sola al crear el
        // registro.
        $user = User::create($request->validated());

        // A propósito no se asigna ningún rol acá: un usuario recién registrado no puede
        // hacer nada todavía (ComandaPolicy rechaza a cualquiera sin rol) hasta que un
        // administrador le asigne uno — igual que cuando se contrata a alguien nuevo.
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    // POST /api/login — Laravel ya validó email/password (ver IniciarSesionRequest) antes de
    // llegar acá. Si la validación falla, este método nunca se ejecuta: Laravel responde 422.
    public function login(IniciarSesionRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Si el usuario no existe (null) O la contraseña no coincide, es lo mismo desde
        // afuera: "credenciales incorrectas" con 401. No se distingue cuál de las dos falló,
        // para no permitir enumerar qué correos existen probando uno por uno.
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Las "abilities" viajan pegadas al TOKEN, aparte de los roles del usuario: limitan
        // qué puede hacer ese token en concreto. Si se filtra (ej. quedó en un log), quien lo
        // use solo puede hacer lo que el token permite.
        $abilities = $user->hasRole('administrador') || $user->hasRole('mesero')
            ? ['comandas:leer', 'comandas:escribir']
            : ['comandas:leer'];

        $token = $user->createToken(
            name: 'api',
            abilities: $abilities,
            // Token con vida corta (30 minutos): si alguien lo roba, la ventana en la que
            // sirve para algo es limitada.
            expiresAt: now()->addMinutes(30),
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    // POST /api/logout — revoca (borra) el token con el que se hizo ESTA MISMA petición.
    public function logout(Request $request)
    {
        // currentAccessToken() es el token que Sanctum usó para autenticar esta petición.
        // Borrarlo lo revoca de inmediato: cualquier petición futura con ese mismo token
        // recibe 401, aunque no se haya cumplido el tiempo de expiración.
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    // PUT /api/password — cambia la contraseña del usuario autenticado (nunca la de otro:
    // siempre es $request->user(), no un id que venga en la URL o el body).
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
