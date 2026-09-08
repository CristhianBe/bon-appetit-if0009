# Laboratorio 4 — Capa de negocios: validaciones, reglas de dominio y CRUD con paginación

Este documento resume las reglas implementadas y el comportamiento esperado de cada una, tal como
lo pide el enunciado del Laboratorio 4 (entrega: jueves 10 de setiembre, 2026).

Rama de trabajo: `feature/lab4-capa-negocio` (creada aparte de `feature/ControladoresVacios` y
`feature/RoutasMelvin` para no pisar ese trabajo — falta reconciliar/fusionar entre el equipo).

## 1. Validaciones declarativas (Form Requests)

Viven en `app/Http/Requests/` y corren automáticamente antes de que el controlador se ejecute
(si fallan, Laravel responde `422` con un mensaje en español por cada campo).

### `GuardarComandaRequest` (abrir y editar comandas)

| Campo | Reglas | Comportamiento esperado |
|---|---|---|
| `mesa_id` | `required`, `integer`, `exists:mesas,id` | Mesa inexistente → `422` |
| `user_id` | `required`, `integer`, `exists:users,id` | Usuario (mesero) inexistente → `422`. No hay autenticación todavía (Laboratorio 6), así que se manda explícito en el body. |
| `detalles` | `required` al abrir, `sometimes` al editar; `array`, `min:1` | Comanda sin líneas al abrir → `422` |
| `detalles.*.producto_id` | `required_with:detalles`, `integer`, `exists:productos,id` | Producto inexistente en una línea → `422` |
| `detalles.*.cantidad` | `required_with:detalles`, `integer`, `min:1`, `max:50` | Cantidad fuera de rango → `422` |

### `GuardarProductoRequest` (crear y editar productos del menú)

| Campo | Reglas | Comportamiento esperado |
|---|---|---|
| `nombre` | `required`, `string`, `max:100`, `unique` (ignorando el propio producto al editar) | Vacío, muy largo o repetido → `422` |
| `categoria_id` | `required`, `integer`, `exists:categorias,id` | Categoría inexistente → `422` |
| `precio` | `required`, `numeric`, `min:0.01`, `max:999999.99` | Precio en cero o negativo → `422` |
| `disponible` | `sometimes`, `boolean` | — |
| `imagen_url` | `nullable`, `url`, `max:255` | Formato de URL inválido → `422` |
| `disponible_desde` | `nullable`, `date` | Fecha inválida (ej. "31 de febrero") → `422` |

`disponible_desde` es el campo que cubre el requisito de **validación de fechas** que pide el
enunciado (representa un producto de temporada que entra al menú en una fecha futura). Ninguna
otra entidad del modelo (Comanda, Categoria) tiene un campo de fecha editable por el usuario.

### `GuardarCategoriaRequest`

`nombre` (`required`, `max:80`, `unique` ignorando la propia categoría) y `descripcion`
(`nullable`, `max:500`).

## 2. Reglas de negocio (capa de servicio)

Viven en `app/Services/` y se lanzan como `App\Exceptions\ReglaNegocioException` — una excepción
propia que no sabe qué es HTTP, traducida a `409 Conflict` en un solo lugar
(`bootstrap/app.php`).

| # | Regla | Dónde | Prueba |
|---|---|---|---|
| 1 | No se puede abrir una comanda nueva en una mesa que no está libre (regla propia del dominio: cada mesa solo tiene una comanda activa a la vez) | `ComandaService::crear()` | `ComandaServiceTest.php` |
| 2 | No se puede cerrar una comanda sin ninguna línea de detalle | `ComandaService::cerrar()` | `ComandaServiceTest.php` |
| 3 | No se puede volver a cerrar una comanda que ya está cerrada (estado terminal) | `ComandaService::cerrar()` | `ComandaServiceTest.php` |
| 4 | Al cerrar una comanda, si el total supera ₡20 000 se aplica un 10 % de descuento (regla de "umbral") | `ComandaService::cerrar()` | `ComandaServiceTest.php` |
| 5 | No se puede eliminar una comanda ya cerrada (forma parte del historial de ventas) | `ComandaService::eliminar()` | `ComandaServiceTest.php` |
| 6 | No se puede eliminar una categoría que tiene productos asociados (dependencias activas) | `CategoriaService::eliminar()` | `CategoriaServiceTest.php` |

Son 6 reglas (el enunciado exige mínimo 4), cubriendo el camino feliz, cada regla y casos límite.
Nota: la base de datos también protege la regla 6 a nivel de esquema
(`restrictOnDelete()` en `productos.categoria_id`), pero sin el chequeo en la capa de servicio el
cliente vería un error crudo de SQL en vez de un `409` con mensaje en español.

## 3. Transacciones

Toda escritura que toca más de una tabla está envuelta en `DB::transaction(...)`:

- `ComandaService::crear()` — crea la comanda, cada línea de `detalle_comandas` y marca la mesa
  como "ocupada" en la misma transacción; si un producto no existe a mitad de camino, no queda
  nada de eso guardado.
- `ComandaService::cerrar()` — actualiza el total (con descuento si aplica) y el estado de la
  comanda, y libera la mesa a "libre", en la misma transacción.

## 4. CRUD con paginación, orden y filtros

`GET /api/comandas`, `GET /api/productos` y `GET /api/categorias`:

- **Paginación:** `paginate($porPagina)` — devuelve `data`, `links` y `meta`.
- **Tope de página:** `min((int) $request->input('per_page', 15), 100)` — nunca se sirven más de
  100 registros por página, sin importar lo que pida el cliente.
- **Ordenamiento por al menos dos campos:** `orderBy($request->input('sort', ...), ...)` acepta
  cualquier columna que el cliente pida (ej. `?sort=precio&dir=asc`, `?sort=nombre&dir=desc`).
- **Filtros combinables:** en comandas, `mesa_id` y `estado` se combinan en la misma petición; en
  productos, `q` (nombre), `categoria_id` y `disponible`.

## 5. Pruebas

Una prueba por cada regla de negocio implementada (y varias adicionales de validación/HTTP),
37 pruebas en total:

- `tests/Feature/ComandaServiceTest.php` — reglas 1-5 directo contra el servicio.
- `tests/Feature/CategoriaServiceTest.php` — regla 6.
- `tests/Feature/ComandaHttpTest.php`, `ProductoHttpTest.php`, `CategoriaHttpTest.php` —
  validación, códigos de estado (`201`, `404`, `409`, `422`) y estructura de respuesta
  end-to-end.
- Base de datos de pruebas aislada: SQLite en memoria + `RefreshDatabase` (ver `phpunit.xml` y
  `tests/Pest.php` — **se re-habilitó `RefreshDatabase`, que estaba comentado**).

## 6. Cambios adicionales hechos en esta rama (fuera del alcance estricto del Lab4, pero necesarios)

- `App\Models\User::rol()` y `App\Models\Role::usuarios()` no compilaban en tiempo de ejecución
  (`BelongsTo`/`HasMany` sin importar, resolvían al namespace equivocado). Se corrigió el import.
- `App\Models\Mesa` no tenía el trait `HasFactory` (bloqueaba `Mesa::factory()` en las pruebas).
- Las fábricas `CategoriaFactory`, `ProductoFactory`, `MesaFactory` y `DetalleComandaFactory`
  estaban vacías (`// `); se completaron para poder usarlas en las pruebas.
- `tests/Pest.php` tenía `RefreshDatabase` comentado; se reactivó.

## 7. Evidencia pendiente (a completar por el equipo)

Ejecución de cada operación CRUD desde Postman/cliente HTTP, comportamiento ante datos inválidos
y ante la violación de cada regla de negocio, y reversión de una transacción ante un fallo
intermedio — según pide el enunciado.
