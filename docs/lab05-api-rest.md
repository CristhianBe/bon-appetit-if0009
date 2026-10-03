# Lab 5 — API orientada a recursos, códigos HTTP y documentación

Este documento explica qué se hizo en `feature/lab5-api-rest` para cumplir el
enunciado del Laboratorio 5. Como en Lab 4, la idea es que quede claro qué
cambió y por qué, antes de que alguien más lo revise o lo mergee.

## 1. Rutas: de `Route::get/post/...` sueltas a `Route::apiResource()`

`routes/api.php` tenía rutas manuales con nombres inconsistentes: `/categories`
(en inglés), `/DetalleComandas` y `/EstadoComandas` (PascalCase, sin anidar),
`/unidad-medida` (singular), `/users` (en inglés). El enunciado pide rutas
"orientadas a recursos" — sustantivos en plural, sin verbos, con recursos
anidados donde hay una relación real.

Se reemplazó todo por `Route::apiResource()`, uno por controlador. El único
detalle es que `Str::singular()` de Laravel no funciona bien con nombres en
español (no sabe que el singular de "categorías" es "categoría"), así que se
usa `->parameters([...])` para fijar el nombre del parámetro de cada ruta sin
tener que tocar el binding que ya usaban los controladores:

```php
Route::apiResource('categorias', CategoriaController::class)
    ->parameters(['categorias' => 'category']);
```

`comandas`, `mesas`, `productos` y `roles` no necesitaron `->parameters()`
porque su singular en inglés/español coincide con el nombre que ya usaban los
controladores.

## 2. `DetalleComanda` como recurso anidado (shallow)

Un detalle de comanda no existe sin una comanda, así que listar y crear
detalles va anidado bajo `/comandas/{comanda}/detalles`. Pero para ver,
actualizar o borrar un detalle puntual no hace falta repetir el id de la
comanda — Laravel tiene justo esto resuelto con `->shallow()`:

```php
Route::apiResource('comandas.detalles', DetalleComandaController::class)
    ->parameters(['detalles' => 'detalleComanda'])
    ->shallow();
```

Esto genera:

- `GET /comandas/{comanda}/detalles` — index (anidado)
- `POST /comandas/{comanda}/detalles` — store (anidado)
- `GET /detalles/{detalleComanda}` — show (plano)
- `PUT/PATCH /detalles/{detalleComanda}` — update (plano)
- `DELETE /detalles/{detalleComanda}` — destroy (plano)

`DetalleComandaController` se ajustó para recibir `Comanda $comanda` en
`index()`/`store()` (viene resuelto por el route model binding) y
`DetalleComanda $detalleComanda` en el resto. También se quitó `comanda_id`
de `DetalleComandaStoreRequest`: ya no hace falta mandarlo en el body porque
la comanda viene de la URL.

## 3. Header `Location` en las respuestas `201`

El enunciado pide que cada operación devuelva el código correcto — `POST`
crea y responde `201` con el header `Location` apuntando al recurso creado.
Los 10 controladores ya devolvían `201` (`Response::HTTP_CREATED`), pero
ninguno mandaba `Location`. Se agregó a los 9 controladores "simples"
(`Categoria`, `Comanda`, `EstadoComanda`, `Mesa`, `MetodoPago`, `Producto`,
`Role`, `UnidadMedida`, `User`) y también a `DetalleComandaController`:

```php
return CategoriaResource::make($categoria)
    ->response()
    ->setStatusCode(Response::HTTP_CREATED)
    ->header('Location', route('categorias.show', $categoria));
```

En `DetalleComandaController::store()` apunta a la ruta plana
(`route('detalles.show', $detalle)`), no a la anidada, porque es la que
existe gracias a `->shallow()`.

## 4. Manejo centralizado de excepciones (`bootstrap/app.php`)

Ya existía un `$exceptions->render()` para `ReglaNegocioException` (409, del
Lab 4). Se agregaron los que pedía el enunciado, cada uno en su propio
`render()` para que la traducción excepción → código HTTP quede en un solo
lugar y ningún controlador necesite `try/catch` propio:

| Excepción | Código | Cuándo se dispara |
|---|---|---|
| `ValidationException` | 422 | Falla una regla de un `FormRequest` (ya lo hacía Laravel; se dejó explícito con el formato `{message, codigo, errors}`) |
| `ModelNotFoundException` / `NotFoundHttpException` | 404 | Route model binding no encuentra el recurso (`Categoria $categoria` que no existe, o ruta que no matchea) |
| `AuthenticationException` | 401 | No autenticado — todavía no hay ninguna ruta protegida con `auth:sanctum` aparte de `/user`, pero el handler queda listo para el Lab 6 |
| `AuthorizationException` | 403 | Autenticado pero sin permiso — mismo caso, listo para cuando existan Policies |
| `ReglaNegocioException` | el que traiga la excepción (409 en los casos actuales) | Reglas de negocio del Lab 4 (categoría con productos, comanda cerrada, etc.) |

Todas responden en el mismo formato (`message` + `codigo`, y `errors` en el
caso de validación) para que sea consistente sin importar qué falló.

**Nota:** autenticación real (Sanctum, login/logout, roles, Policies) es
Lab 6 según la separación que hace el curso — acá solo se dejó listo el
manejo de 401/403 para cuando exista.

## 5. Metadatos de paginación

No hubo que tocar nada: los 10 controladores ya devuelven
`Resource::collection($modelo::paginate($n))` desde el Lab 4, y Laravel arma
automáticamente `meta` (página actual, total de páginas, total de registros)
y `links` (first/last/prev/next) en cada respuesta paginada. Se verificó
revisando los 10 controladores — todos siguen el mismo patrón.

## 6. Colección de pruebas (Postman)

`docs/postman/bon-appetit.postman_collection.json` y
`bon-appetit.postman_environment.json` — 25 solicitudes en 6 carpetas
(Categorías, Productos, Comandas, Detalles de comanda, Catálogos, Usuarios),
cubriendo casos correctos (200, 201+Location, 204) y de error (404, 409,
422) contra los endpoints reales. Usa variables de entorno (`base_url`,
`token`, y los ids de ejemplo) en vez de valores fijos, como pide el
enunciado.

No se incluyó un caso de "401 sin token" porque, como no hay middleware de
autenticación activo todavía (eso es Lab 6), esa solicitud respondería 200 en
vez de 401 — se agregará cuando exista autenticación real.

## 7. Pendiente (no depende de este entorno)

Estas partes necesitan `composer`/`php artisan`, que no tengo disponibles
acá, así que quedan para correr desde tu terminal:

- **Documentación OpenAPI** (punto 6 del enunciado): instalar
  `dedoc/scramble` y exportar a `docs/openapi.yaml`. Los comandos exactos
  te los paso aparte.
- **Verificación final**: `php artisan test` y
  `vendor/bin/pint --dirty --format agent` antes de armar el PR.

## 8. Ya hecho aparte

`docs/lab05-microservicio-notificaciones.md` — propuesta de diseño del
microservicio (punto 8 del enunciado): un servicio de notificaciones en
tiempo real para cocina/meseros, separado del monolito, con su contrato de
endpoints y la justificación técnica de por qué va aparte.
