# Lab 4 — CRUD real de los controladores

Este documento explica qué se agregó en `feature/crud-controladores` para llenar
los 10 controladores que estaban vacíos (`index/store/show/update/destroy` eran
solo comentarios `//`). Complementa `docs/lab04-negocio.md`, que ya explica las
6 reglas de negocio y los Services.

## 1. Categoria y Producto — pasan por su Service

`CategoriaController` y `ProductoController` no tocan Eloquent directo: llaman
a `CategoriaService` / `ProductoService`. Se creó `CategoriaService` nuevo
(`app/Services/CategoriaService.php`), calcado de `ProductoService`: tiene
`listar()`, `crear()`, `actualizar()` y `eliminar()`. En `eliminar()` se agregó
una regla nueva, en el mismo espíritu de "no eliminar un producto ya vendido":

```php
public function eliminar(Categoria $categoria): void
{
    if ($categoria->productos()->exists()) {
        throw new ReglaNegocioException(
            'No se puede eliminar una categoría que tiene productos asociados.',
            'categoria_con_productos',
            409,
        );
    }

    $categoria->delete();
}
```

Esto evita que el `restrictOnDelete()` de la migración de `productos` tire un
error feo de MySQL directo al usuario — ahora se captura antes y se explica.

## 2. Comanda y DetalleComanda — pasan por ComandaService

`ComandaController::store()` llama a `ComandaService::abrir()` (igual que ya
probaban los tests de Lab 4). Lo nuevo es `update()`: si el body incluye
`estado_comanda_id` y ese estado tiene `codigo = 'cerrada'`, se delega en
`ComandaService::cerrar()` (aplica el descuento y libera la mesa). Cualquier
otro cambio se aplica directo con `$comanda->update()`.

`DetalleComandaController` usa `agregarDetalle()` (store), `actualizarDetalle()`
(update, método nuevo que agregué a `ComandaService`) y `quitarDetalle()`
(destroy) — así la regla "no se puede modificar el detalle de una comanda ya
cerrada" se respeta también si alguien llama a esta ruta directamente, no solo
si se abre/cierra la comanda por su propio endpoint.

También extendí esa misma regla a `agregarDetalle()`: antes solo se chequeaba
al quitar un detalle, ahora tampoco se puede agregar un producto a una comanda
ya cerrada.

## 3. Los otros 6 (Mesa, EstadoComanda, MetodoPago, UnidadMedida, Role, User)

Estos no tienen reglas de negocio propias del Lab 4, así que el controlador
llama a Eloquent directo (sin Service intermedio) — sigue siendo "delgado",
solo que no hay lógica que mover a una capa aparte. `UserController` es la
única diferencia: hashea la contraseña con `Hash::make()` antes de guardar.

## 4. Form Requests, Resources y el mapeo de excepciones

- Se crearon los Form Request que faltaban: `CategoriaStore/UpdateRequest`,
  `DetalleComandaStore/UpdateRequest`, `MesaStore/UpdateRequest`,
  `EstadoComandaStore/UpdateRequest`, `MetodoPagoStore/UpdateRequest`,
  `UnidadMedidaStore/UpdateRequest`, `RoleStore/UpdateRequest`,
  `UserStore/UpdateRequest`.
- Se creó `app/Http/Resources/` (no existía) con un Resource por entidad, para
  no exponer directo el modelo de Eloquent en la respuesta JSON (por ejemplo,
  `UserResource` nunca incluye `password`).
- En `bootstrap/app.php` se agregó el mapeo de `ReglaNegocioException` a JSON:

```php
$exceptions->render(function (ReglaNegocioException $e, Request $request) {
    if ($request->is('api/*') || $request->expectsJson()) {
        return response()->json([
            'message' => $e->getMessage(),
            'codigo' => $e->codigo(),
        ], $e->statusSugerido());
    }
});
```

Sin esto, cualquier violación de regla de negocio (mesa ocupada, producto no
disponible, etc.) iba a devolver un 500 genérico en Postman en vez del 409/422
correcto — el enunciado de Lab 4 menciona esto para Lab 5, pero como ya
teníamos el `codigo`/`statusSugerido` listos en `ReglaNegocioException`, lo
conecté ahora para que las pruebas de Postman de este laboratorio muestren el
error real y no un 500 sin explicación.

## 5. Bugs que ya existían y se corrigieron de paso

- `app/Models/Role.php` usaba `HasMany` sin importarlo.
- `app/Models/User.php` usaba `BelongsTo` sin importarlo.
- `ProductoStoreRequest.php` y `ProductoUpdateRequest.php` usaban
  `Rule::unique(...)` sin `use Illuminate\Validation\Rule;` — nunca había
  explotado porque nada llamaba a esos Request todavía.

## 6. Tests nuevos

`tests/Feature/CategoriaProductoControllerTest.php`,
`ComandaControllerTest.php` y `CatalogosControllerTest.php` prueban el CRUD y
las reglas de negocio por HTTP (no solo llamando el Service directo, como los
tests de Lab 4). Los 25 tests pasan (85 assertions).
