# Guía de Estilos de Interfaz — Bon Appétit

## 1. Paleta de Colores y Verificación WCAG

Sistema de color principal basado en la identidad gastronómica del restaurante (tonos vino, borgoña y acentos dorados).

### Colores Base

| Uso / Variable | Nombre | Hex | Propósito | Contraste (Fondo Blanco `#FFF`) | Cumplimiento |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `--color-primary` | Wine Burgundy | `#6B1D2F` | Encabezados, botones primarios, elementos de marca. | 7.5:1 | AAA |
| `--color-primary-hover` | Deep Wine | `#4D1220` | Estados hover y activo de botones primarios. | 11.2:1 | AAA |
| `--color-secondary` | Warm Gold | `#C5A059` | Acentos, bordes de enfoque y detalles visuales. | 3.1:1 (*1) | AA (Para UI) |
| `--color-bg-main` | Off-White / Cream | `#FAF8F5` | Fondo general de la aplicación. | N/A | N/A |
| `--color-surface` | Pure White | `#FFFFFF` | Fondos de tarjetas, modales y contenedores. | N/A | N/A |
| `--color-text-main` | Charcoal | `#1C1917` | Texto principal (cuerpo y títulos). | 15.8:1 | AAA |
| `--color-text-muted` | Muted Gray | `#666666` | Metadatos, fechas y textos secundarios. | 5.7:1 | AA |

*Note (*1): El tono dorado `#C5A059` se utiliza exclusivamente como fondo para badges con texto `#1C1917` (Contraste 8.2:1 - AAA) o como borde decorativo.*

### Colores de Estado

| Estado | Nombre | Hex | Uso | Contraste | Cumplimiento |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Success | Forest Green | `#1E5E3A` | Comandas listas, confirmaciones, estado "Libre". | 7.1:1 | AAA |
| Warning | Amber Gold | `#B45309` | Alertas, comandas en proceso, estado "Ocupada". | 5.4:1 | AA |
| Danger / Error | Crimson Red | `#B91C1C` | Errores de formulario, cancelaciones, alertas críticas. | 7.4:1 | AAA |
| Disabled | Neutral Gray | `#E5E7EB` | Fondos inactivos. Texto en `#9CA3AF` (3.0:1). | N/A | Cumple UI |

---

## 2. Tipografía y Escala

- **Títulos y Display:** `Playfair Display`, serif.
- **Cuerpo e Interfaz:** `Inter`, sans-serif.

| Nivel | Fuente | Tamaño | Font Weight | Line Height |
| :--- | :--- | :--- | :--- | :--- |
| `h1` | Playfair Display | `32px` (`2rem`) | 700 (Bold) | 1.2 |
| `h2` | Playfair Display | `24px` (`1.5rem`) | 600 (SemiBold) | 1.3 |
| `h3` | Inter | `20px` (`1.25rem`) | 600 (SemiBold) | 1.4 |
| `body` / `p` | Inter | `16px` (`1rem`) | 400 (Regular) | 1.5 |
| `small` / `caption` | Inter | `14px` (`0.875rem`) | 400 (Regular) | 1.4 |
| `button` / `badge` | Inter | `14px` (`0.875rem`) | 500 (Medium) | 1.0 |

---

## 3. Escala de Espaciado (Base 8px)

El layout y los componentes siguen la retícula estándar de 8px:

* `4px` (`xs`): Separación entre icono y texto dentro de un botón o badge.
* `8px` (`sm`): Espacio interno de badges, separaciones pequeñas.
* `16px` (`md`): Padding en botones, inputs y espacio interno de cards.
* `24px` (`lg`): Separación entre bloques dentro de un mismo contenedor.
* `32px` (`xl`): Margen exterior de secciones principales.
* `48px` (`2xl`): Separación entre bloques grandes de la pantalla.

---

## 4. Estados de Componentes

### Botones (`.btn`)
* **Normal:** Fondo `#6B1D2F`, texto `#FFFFFF`, borde redondeado `6px`.
* **Foco (Focus):** `outline: 2px solid #C5A059`, con `outline-offset: 2px`.
* **Error / Destructivo:** Fondo `#B91C1C`, texto `#FFFFFF`.
* **Deshabilitado:** Fondo `#E5E7EB`, texto `#9CA3AF`, `cursor: not-allowed`, `pointer-events: none`.

### Campos de Texto (`input`, `select`, `textarea`)
* **Normal:** Fondo `#FFFFFF`, borde `1px solid #D1D5DB`, texto `#1C1917`.
* **Foco (Focus):** Borde `#6B1D2F` con sombra suave `box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.15)`.
* **Error:** Borde `#B91C1C`. Se incluye mensaje inferior en tono `#B91C1C` y fuente `14px`.
* **Deshabilitado:** Fondo `#F3F4F6`, borde `#E5E7EB`, texto `#9CA3AF`, `cursor: not-allowed`.

### Tarjetas / Mesas (`.card`)
* **Libre / Disponible:** Borde lateral izquierdo en verde `#1E5E3A`.
* **Ocupada / Comanda Activa:** Borde lateral izquierdo en ámbar `#B45309`.
* **Seleccionada / Foco:** Borde perimetral en `#C5A059` de `2px`.
* **Deshabilitada / Fuera de servicio:** Fondo `#F9FAFB`, borde e icono en `#9CA3AF`.