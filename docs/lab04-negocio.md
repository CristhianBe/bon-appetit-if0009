# Laboratorio 3 — Decisiones de modelado

## 1. Entidades y relaciones

El sistema tiene 11 tablas: 5 de dominio (`categorias`, `productos`, `mesas`,
`comandas`, `detalle_comandas`) y 6 de catálogo/seguridad (`roles`, `users`,
`estados_mesa`, `estados_comanda`, `metodos_pago`, `unidades_medida`), muy por
encima del mínimo de seis exigido.

| Relación | Tipo | Notas |
|---|---|---|
| `Categoria` — `Producto` | 1 a N | `productos.categoria_id`. Un producto pertenece a una sola categoría. |
| `Role` — `User` | 1 a N | `users.rol_id`. Define si el usuario es administrador, mesero o cajero. |
| `EstadoMesa` — `Mesa` | 1 a N | `mesas.estado_mesa_id` (libre, ocupada, reservada, etc.). |
| `EstadoComanda` — `Comanda` | 1 a N | `comandas.estado_comanda_id` (abierta, en preparación, cerrada, etc.). |
| `Mesa` — `Comanda` | 1 a N | `comandas.mesa_id`. Una mesa tiene muchas comandas a lo largo del tiempo. |
| `User` (mesero) — `Comanda` | 1 a N | `comandas.user_id`. Cada comanda queda asociada al mesero que la abrió. |
| `Comanda` — `Producto` (vía `DetalleComanda`) | N a M con datos en la tabla intermedia | `DetalleComanda` guarda `cantidad`, `precio_unitario` y `notas`. El precio se "congela" en el detalle porque el precio de venta no debe cambiar retroactivamente si el precio del producto se actualiza después. Esta es la relación que exige el enunciado con "datos adicionales en la tabla pivote". |

`metodos_pago` y `unidades_medida` ya están creadas como catálogos, listas para
cuando se agreguen (fuera del alcance de este laboratorio) las entidades
`Pago` e `InsumoInventario`/`Receta`.

## 2. Por qué tablas catálogo en vez de strings/enum

`estados_mesa`, `estados_comanda`, `metodos_pago` y `unidades_medida` se modelaron como
tablas propias en lugar de columnas `enum` o texto libre. Guardar el estado como texto
repetido en cada fila de `mesas` o `comandas` genera una dependencia transitiva (el
nombre visible del estado no depende de la llave primaria de esa fila, sino de un valor
que se repite): eso es justamente lo que la tercera forma normal busca eliminar. Con la
tabla catálogo, cada fila solo referencia un `id`, y agregar un estado nuevo (por
ejemplo, "en espera de pago") no requiere migración de código, solo un registro nuevo.

## 3. Integridad referencial y política de borrado

| Tabla.columna | Referencia | Política | Motivo |
|---|---|---|---|
| `productos.categoria_id` | `categorias` | `restrictOnDelete()` | No se puede borrar una categoría que todavía tiene productos. |
| `users.rol_id` | `roles` | `nullOnDelete()` | Perder el rol de un usuario no debería impedir borrar el registro padre. |
| `mesas.estado_mesa_id` | `estados_mesa` | `restrictOnDelete()` | Protege el catálogo de estados en uso. |
| `comandas.mesa_id` | `mesas` | `restrictOnDelete()` | No se puede borrar una mesa que ya tiene comandas asociadas (integridad histórica). |
| `comandas.user_id` | `users` | `restrictOnDelete()` | No se puede borrar un usuario (mesero) que ya abrió comandas. |
| `comandas.estado_comanda_id` | `estados_comanda` | `restrictOnDelete()` | Protege el catálogo de estados en uso. |
| `detalle_comandas.comanda_id` | `comandas` | `cascadeOnDelete()` | Una línea de detalle no tiene sentido sin su comanda; si se borra la comanda, se borran sus líneas. |
| `detalle_comandas.producto_id` | `productos` | `restrictOnDelete()` | No se puede borrar un producto que ya aparece en el detalle de alguna comanda (protege el historial de ventas). |

## 4. Reversibilidad de las migraciones

Las migraciones se escribieron en orden de dependencia (catálogos y padres primero,
tablas dependientes al final), así que un `php artisan migrate:rollback` las revierte en
orden inverso sin violar ninguna llave foránea. Verificar con:

```bash
php artisan migrate:fresh --seed
php artisan migrate:rollback
php artisan migrate
```

## 5. Modelos Eloquent

- Asignación masiva protegida con `$fillable` explícito en todos los modelos (nunca
  `$guarded = []`).
- Conversores de tipo (`$casts`) en los campos decimales, booleanos y de fecha
  (`precio`, `disponible`, `cerrada_en`), para no depender de comparar strings.
- Campo oculto en serialización: `User::$hidden` excluye `password` y `remember_token`.
- Scopes reutilizables en `app/Models/Producto.php`: `scopeDisponibles` y
  `scopeDeCategoria`, encadenables entre sí (verificado en Tinker:
  `Producto::disponibles()->deCategoria('Entradas')->get()`).

## 6. Datos de prueba

`DatabaseSeeder` puebla los catálogos, 4 categorías, 6 productos (los del contrato
comparativo), 3 usuarios de prueba (uno por rol), 8 mesas, y **25 comandas** generadas
con `ComandaFactory` (66 líneas de detalle en total), por encima del mínimo de 20
registros que pide el enunciado en la entidad principal.

## 7. Las tres consultas del enunciado

### 1. Constructor de consultas con scopes reutilizables

Scopes encadenables en `app/Models/Producto.php`, probados en Tinker:

```php
>>> \App\Models\Producto::disponibles()->count();
= 5
>>> \App\Models\Producto::deCategoria('Bebidas')->get();
// 1 resultado: Refresco natural
>>> \App\Models\Producto::disponibles()->deCategoria('Entradas')->get();
// 2 resultados: Nachos con queso, Ensalada César
```

### 2. Agregación con `GROUP BY` y funciones de agregado

Comando `php artisan reporte:ventas-por-categoria`
(`app/Console/Commands/ReporteVentasPorCategoria.php`): une `detalle_comandas` con
`productos` y `categorias`, agrupa por categoría y suma unidades vendidas y total
facturado.