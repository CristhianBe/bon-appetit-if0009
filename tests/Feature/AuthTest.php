<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registra una cuenta nueva y responde 201 sin token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Persona Nueva',
        'email' => 'nueva@bonappetit.test',
        'password' => 'ClaveSegura#2026',
        'password_confirmation' => 'ClaveSegura#2026',
    ]);

    // 201, no 200: se creó un recurso (la cuenta), no se inició sesión. Registrarse y
    // loguearse son dos pasos distintos a propósito (ver AuthController::register).
    $response->assertCreated()->assertJsonStructure(['user' => ['id', 'name', 'email']]);
    $response->assertJsonMissing(['token']);
    expect(User::where('email', 'nueva@bonappetit.test')->exists())->toBeTrue();
});

it('rechaza el registro con un correo ya usado: 422', function () {
    $existente = User::factory()->create();

    $response = $this->postJson('/api/register', [
        'name' => 'Otra Persona',
        'email' => $existente->email,
        'password' => 'ClaveSegura#2026',
        'password_confirmation' => 'ClaveSegura#2026',
    ]);

    $response->assertStatus(422);
});

it('rechaza el registro con una contraseña que no cumple la política: 422', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Persona Nueva',
        'email' => 'otra@bonappetit.test',
        'password' => 'corta123',
        'password_confirmation' => 'corta123',
    ]);

    $response->assertStatus(422);
});

it('inicia sesión y devuelve un token', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'Passw0rd!',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
});

it('rechaza credenciales incorrectas con 401', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'incorrecta',
    ]);

    $response->assertStatus(401);
});

it('rechaza datos inválidos con 422', function () {
    $response = $this->postJson('/api/login', ['email' => 'no-es-un-correo']);

    $response->assertStatus(422);
});

it('el token de login tiene fecha de expiración y capacidades asignadas', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);
    $user->assignRole(Role::firstOrCreate(['name' => 'mesero', 'guard_name' => 'web']));

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Passw0rd!']);

    $tokenGuardado = $user->tokens()->latest()->first();

    expect($tokenGuardado->expires_at)->not->toBeNull();
    expect($tokenGuardado->expires_at->isFuture())->toBeTrue();
    expect($tokenGuardado->abilities)->toContain('comandas:escribir'); // mesero puede escribir
});

it('un token expirado ya no sirve para autenticarse: 401', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api', ['*'], now()->subMinute());

    $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->getJson('/api/comandas')
        ->assertStatus(401);
});

it('logout revoca el token: usarlo después responde 401', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api', ['*'], now()->addMinutes(30));

    $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->postJson('/api/logout')
        ->assertNoContent();

    // El guard de autenticación cachea el usuario ya resuelto mientras dure la aplicación. En
    // una petición HTTP real esto no pasa (cada petición arranca una aplicación nueva), pero
    // dentro de UN mismo test, dos llamadas seguidas comparten la misma aplicación — sin este
    // forgetGuards(), la segunda llamada de abajo seguiría "recordando" al usuario de la
    // primera en vez de volver a validar el token (ya borrado) contra la base de datos.
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->getJson('/api/comandas')
        ->assertStatus(401);
});

it('limita a 5 intentos de login por minuto: el sexto responde 429', function () {
    $credenciales = ['email' => 'no-existe@bonappetit.test', 'password' => 'lo-que-sea'];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', $credenciales)->assertStatus(401);
    }

    $this->postJson('/api/login', $credenciales)->assertStatus(429);
});

it('cambia la contraseña cuando la actual es correcta y la nueva cumple la política', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/password', [
        'password_actual' => 'Passw0rd!',
        'password' => 'NuevaClave#2026',
        'password_confirmation' => 'NuevaClave#2026',
    ])->assertNoContent();

    expect(Hash::check('NuevaClave#2026', $user->fresh()->password))->toBeTrue();
});

it('rechaza el cambio de contraseña si la actual no coincide: 422', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/password', [
        'password_actual' => 'esta-no-es',
        'password' => 'NuevaClave#2026',
        'password_confirmation' => 'NuevaClave#2026',
    ])->assertStatus(422);
});

it('rechaza una contraseña nueva que no cumple la política de complejidad: 422', function () {
    $user = User::factory()->create(['password' => Hash::make('Passw0rd!')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/password', [
        'password_actual' => 'Passw0rd!',
        'password' => 'corta123',
        'password_confirmation' => 'corta123',
    ])->assertStatus(422);
});
