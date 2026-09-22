# Laboratorio 6 — Autenticación con tokens, autorización por políticas y pruebas automatizadas

Rama de trabajo: `feature/lab6-seguridad` (creada desde `main` ya actualizado con el Laboratorio 5).

## 1. Autenticación por token

| Acción | Endpoint | Implementación |
|---|---|---|
| Registro | `POST /api/register` | `AuthController::register()` + `RegistrarUsuarioRequest` (nombre, correo único, contraseña con política de complejidad). No devuelve token: registrarse e iniciar sesión quedan como pasos separados. |
| Inicio de sesión | `POST /api/login` | `AuthController::login()`. Token con `expiresAt` (30 min) y `abilities` según el rol del usuario (`comandas:leer` / `comandas:escribir`). |
| Cierre de sesión | `POST /api/logout` | `AuthController::logout()` — revoca el token con el que se hizo la petición. |
| Cambio de contraseña | `PUT /api/password` | `AuthController::cambiarPassword()` — exige la contraseña actual y aplica la misma política de complejidad. |

**Contraseñas:** derivadas con el algoritmo del framework (`'password' => 'hashed'` en `User::casts()`). **Política mínima:** `Password::min(12)->letters()->mixedCase()->numbers()->symbols()`, aplicada en registro y en cambio de contraseña.

## 2. Protección del inicio de sesión

- `throttle:5,1` en `/login`: máximo 5 intentos por minuto por IP; el sexto responde `429` automáticamente.
- El mensaje de error (`"Credenciales incorrectas."`) es el mismo exista o no la cuenta.

## 3. Roles y autorización por políticas (dos capas)

Se migró el sistema de roles propio (`users.rol_id` + tabla `roles` con `nombre`/`descripcion`) a **`spatie/laravel-permission`**. `App\Models\Role` extiende el `Role` del paquete (agregando el campo propio `descripcion`); el contrato externo de la API (`RoleController`, `UserController`) no cambió — sigue aceptando/devolviendo `nombre`.

Tres roles (`administrador`, `mesero`, `cajero`). `App\Policies\ComandaPolicy` decide el acceso por **rol + dueño del recurso** (`comanda->user_id`, el mesero que la abrió):

- `administrador` → cualquier comanda.
- `mesero` → solo las comandas que él abrió (además, `ComandaService::abrir()` ignora cualquier `user_id` que venga en el body y usa siempre al actor autenticado, salvo que sea administrador).
- `cajero` → solo lectura de cualquier comanda (necesita verlas para cobrar), no puede abrir/editar/eliminar.

**Verificación en dos capas:** `ComandaController`/`DetalleComandaController` (`$this->authorize(...)`, Capa 1) y otra vez dentro de `ComandaService` (`Gate::forUser($actor)->authorize(...)`, Capa 2) — invocar el servicio directamente, sin pasar por la ruta, también queda rechazado. Demostrado en `tests/Feature/ComandaAutorizacionTest.php` (llama al servicio directo, sin HTTP).

### Nota técnica: bug real encontrado y corregido

`Sanctum::actingAs()` (helper de pruebas) muta `config('auth.defaults.guard')` a `"sanctum"` mientras dura el test. Como `spatie/laravel-permission` usa ese valor por defecto al crear un rol sin `guard_name` explícito, cualquier rol creado *después* de `actingAs()` terminaba con `guard_name = "sanctum"` — un guard sin `provider` configurado — y `Role->users()->detach()` (que corre automáticamente al eliminar un rol) fallaba con un error 500. Se corrigió fijando `guard_name: 'web'` explícito en `RoleController::store()`, `RoleSeeder` y el helper de pruebas `usuarioConRol()`, en vez de depender del valor por defecto.

## 4. Pruebas automatizadas

| Tipo | Archivo(s) | Cantidad | Exige el enunciado |
|---|---|---|---|
| Unitarias de servicio (reglas de negocio) | `ReglasNegocioTest.php` | 6 | — |
| Unitarias de servicio (autorización) | `ComandaAutorizacionTest.php` | 9 | — |
| **Total unitarias de servicio** | — | **15** | 12 mínimo |
| API (autenticación) | `AuthTest.php` | 13 | — |
| API (autorización por rol) | `ComandaAutorizacionHttpTest.php` | 6 | 6 mínimo |
| Resto de pruebas existentes (Lab 3-5) | `ComandaControllerTest.php`, `CategoriaProductoControllerTest.php`, `CatalogosControllerTest.php` | 19 | — |
| **Total de la suite** | — | **53** | 18 mínimo |
| Base de datos de pruebas | SQLite en memoria + `RefreshDatabase` (aislada de los datos de desarrollo) | — | — |
| Cobertura | `php artisan test --coverage --min=70` | **86.2 %** | ≥ 70 % |

Las pruebas de servicio no usan dobles de prueba (mocks) porque `ComandaService` no recibe ninguna dependencia por constructor que necesite doblarse — se prueban directo contra la base de datos en memoria, igual que las reglas de negocio ya existentes del Laboratorio 4.

## 5. Cambios en código ya fusionado por el equipo

Proteger las rutas con `auth:sanctum` (antes eran todas públicas) rompió 18 pruebas existentes de los Laboratorios 3-5, que no autenticaban ninguna petición. Se actualizaron para autenticar como `administrador` (rol sin restricciones), ya que esas pruebas verifican códigos/estructura del CRUD, no autorización por rol — eso lo cubren los archivos nuevos de este laboratorio:

- `ComandaControllerTest.php`, `CategoriaProductoControllerTest.php`, `CatalogosControllerTest.php`: se agregó `Sanctum::actingAs(usuarioConRol('administrador'))`.
- `ReglasNegocioTest.php`: los métodos de `ComandaService` ahora exigen un actor (para la Capa 2 de autorización); se pasa el mismo mesero dueño de la comanda en cada llamada.
- `DetalleComandaController`: también delega en `ComandaService`, así que también necesitaba el actor y la autorización de Capa 1 (se le había pasado por alto en la primera versión de este laboratorio).

## 6. Evidencia de ejecución (Postman)

Colección actualizada: `docs/postman/bon-appetit.postman_collection.json` — carpeta nueva "Auth" (Registrar, Login, Login con credenciales incorrectas, Cambiar contraseña, Logout) al inicio de la colección. También se corrigió un campo mal escrito en la petición "Abrir comanda" ya existente (`mesero_id` → `user_id`, el nombre real que exige `ComandaStoreRequest`).

Capturas en `docs/evidencia-lab6/`, todas contra `http://127.0.0.1:8000/api` (`php artisan serve`):

### 6.1 Flujo de autenticación

| # | Evidencia | Resultado |
|---|---|---|
| 1 | [Registro de una cuenta nueva](evidencia-lab6/01-registro-201.png) | `201 Created` |
| 2 | [Login correcto](evidencia-lab6/02-login-200.png) | `200 OK` + token |
| 3 | [Login con contraseña incorrecta](evidencia-lab6/03-login-credenciales-incorrectas-401.png) | `401` — mensaje genérico |
| 4 | [Sexto intento de login en el mismo minuto](evidencia-lab6/04-login-limite-intentos-429.png) | `429 Too Many Requests` |
| 5 | [Logout](evidencia-lab6/05-logout-204.png) | `204 No Content` |
| 6 | [Reuso del token ya revocado](evidencia-lab6/06-token-revocado-401.png) | `401` — el token no sirve más aunque no haya expirado |

### 6.2 Autorización (acceso permitido / denegado)

| # | Evidencia | Resultado |
|---|---|---|
| 7 | [Mesero ve su propia comanda](evidencia-lab6/07-ver-comanda-propia-200.png) | `200 OK` |
| 8 | [Mesero intenta ver la comanda de otra persona](evidencia-lab6/08-ver-comanda-ajena-403.png) | **`403 Forbidden`** — la política filtra por dueño |
| 9 | [Cajero intenta abrir una comanda](evidencia-lab6/09-cajero-abrir-comanda-403.png) | `403 Forbidden` — rol de solo lectura |

### 6.3 Suite de pruebas y cobertura

| # | Evidencia | Resultado |
|---|---|---|
| 10 | [`php artisan test --coverage --min=70`](evidencia-lab6/10-cobertura-86.2.png) | **53 passed** · **Total: 86.2 %** |

> **Nota técnica:** las respuestas `429` y `403` de las capturas incluyen la traza de la excepción porque el entorno local corre con `APP_DEBUG=true` (normal en desarrollo). `ThrottleRequestsException` es la única excepción que `bootstrap/app.php` no traduce todavía a un JSON limpio (a diferencia de `AuthenticationException`/`AuthorizationException`, que sí); queda anotado como mejora menor para un futuro laboratorio.
