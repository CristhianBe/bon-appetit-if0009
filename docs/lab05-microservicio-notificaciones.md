# Lab 5 — Propuesta de microservicio: notificaciones de cocina

El enunciado pide diseñar y documentar el contrato de un microservicio para
una responsabilidad acotada (ej. notificaciones o generación de reportes),
justificando por qué conviene separarla del monolito. Esto es una propuesta
de diseño — no se implementa en este laboratorio, solo se documenta el
contrato para que quede listo si el equipo decide construirlo más adelante.

## 1. Responsabilidad que se separa

Cuando una comanda cambia de estado (se abre, se marca "en preparación", se
marca "lista"), alguien tiene que enterarse casi en tiempo real: la cocina
necesita ver los pedidos nuevos apenas entran, y el mesero necesita saber
cuándo un plato está listo para llevarlo a la mesa.

Ahora mismo la API de Bon Appetit no notifica a nadie — el cliente (una app
de cocina, un tablero, el celular del mesero) tendría que hacer polling
constante a `GET /comandas` o `GET /comandas/{id}` para enterarse de un
cambio. Eso funciona, pero es ineficiente y no escala si hay varios
dispositivos consultando a la vez.

La propuesta es un **microservicio de notificaciones** separado, encargado
solo de: recibir eventos de cambio de estado desde la API principal, y
empujarlos a los dispositivos conectados (cocina, meseros) en tiempo real.

## 2. Por qué separarlo del monolito (justificación técnica)

- **Naturaleza distinta del trabajo.** La API principal es request/response
  clásico (CRUD, validación, reglas de negocio). Las notificaciones en tiempo
  real necesitan conexiones persistentes (WebSockets o Server-Sent Events)
  que se mantienen abiertas — es un patrón de concurrencia distinto, y
  mezclarlo en el mismo proceso PHP-FPM que atiende la API complica el
  manejo de conexiones largas.

- **Aislar el fallo.** Si el servicio de notificaciones se cae o se satura
  (por ejemplo, muchos clientes conectados a la vez), no debe tumbar ni
  volver lenta la creación/actualización de comandas, que es la operación
  crítica del negocio. Separarlo en su propio proceso significa que un
  problema ahí no afecta el resto de la API.

- **Escalan distinto.** La API principal escala según tráfico de pedidos
  (horas pico de un restaurante). El servicio de notificaciones escala según
  cantidad de dispositivos conectados simultáneamente (pantallas de cocina,
  celulares de meseros), que es un patrón de carga diferente y conviene
  poder escalar cada uno por separado.

- **No comparte el modelo de datos.** El microservicio no necesita acceso a
  la base de datos completa de Bon Appetit (categorías, productos, usuarios,
  etc.) — solo necesita recibir el evento ya resuelto (qué comanda, qué
  estado nuevo, qué mesa) y reenviarlo. Eso reduce el acoplamiento: puede
  vivir sin conocer el esquema de la base de datos principal.

## 3. Cómo se comunican los dos servicios

La API principal (Laravel) sigue siendo la única dueña de los datos y de las
reglas de negocio. Cuando `ComandaService` cambia el estado de una comanda
(en `abrir()`, `cambiarEstado()`, `cerrar()`), dispara un evento hacia el
microservicio de notificaciones — no al revés. El microservicio nunca
escribe en la base de datos de Bon Appetit ni llama de vuelta a la API para
pedir datos: todo lo que necesita viene en el evento.

```
Laravel (API Bon Appetit)              Microservicio de notificaciones
─────────────────────────              ────────────────────────────────
ComandaService::cambiarEstado()
        │
        ▼
POST /eventos/comanda-actualizada  ──▶  recibe el evento, lo valida
                                         y lo reenvía por WebSocket a los
                                         clientes suscritos a esa mesa/cocina
```

Se usa HTTP simple (POST con un webhook) en vez de un broker de mensajes
(RabbitMQ, Redis pub/sub) porque el volumen de eventos de un restaurante es
bajo (decenas de comandas por hora, no miles por segundo) — un broker
agregaría infraestructura que no se justifica todavía. Si el volumen crece,
este único punto (el `POST /eventos/...`) es fácil de cambiar por una
publicación a una cola sin tocar el resto de la API.

## 4. Contrato del microservicio

### 4.1 Endpoint que expone el microservicio (lo llama la API principal)

**`POST /eventos/comanda-actualizada`**

Autenticación: header `Authorization: Bearer <token-de-servicio>` (un token
fijo compartido entre los dos servicios, no un usuario — es comunicación
servidor a servidor).

Body:

```json
{
  "comanda_id": 42,
  "mesa_numero": 7,
  "estado": {
    "codigo": "en_preparacion",
    "nombre": "En preparación"
  },
  "ocurrido_en": "2026-09-17T14:32:00-06:00"
}
```

Respuestas:

| Código | Cuándo |
|---|---|
| `202 Accepted` | El evento se recibió y se encoló para reenviar por WebSocket. |
| `400 Bad Request` | Falta un campo requerido o el JSON es inválido. |
| `401 Unauthorized` | El token de servicio no es válido. |

Se responde `202` y no `200` porque el microservicio no espera a que todos
los clientes reciban la notificación antes de responder — solo confirma que
aceptó el evento para procesarlo.

### 4.2 Canal que exponen los clientes finales (cocina, meseros)

**`WS /suscripcion?canal=cocina`** ó **`WS /suscripcion?canal=mesero:{id}`**

Un cliente (pantalla de cocina, app del mesero) abre una conexión WebSocket
y se suscribe a un canal. El microservicio reenvía cada evento que le llega
de la API a todos los clientes suscritos al canal correspondiente:

```json
{
  "tipo": "comanda_actualizada",
  "comanda_id": 42,
  "mesa_numero": 7,
  "estado": { "codigo": "en_preparacion", "nombre": "En preparación" }
}
```

### 4.3 Endpoint de salud

**`GET /salud`** → `200 OK` con `{"status": "ok"}` si el proceso está vivo y
puede aceptar conexiones. Sirve para que la API principal (o un balanceador)
sepa si el microservicio está disponible antes de intentar notificarle.

## 5. Qué pasa si el microservicio no está disponible

La API principal nunca debe bloquear una operación de negocio (abrir/cerrar
una comanda) esperando a que el microservicio de notificaciones responda. La
llamada a `POST /eventos/comanda-actualizada` se hace de forma asíncrona
(en background, ej. un Job de cola de Laravel) y si falla o no responde, se
reintenta un par de veces y luego se descarta — el peor caso es que la
cocina se entera un poco tarde o tiene que revisar la pantalla manualmente,
pero nunca se pierde ni se retrasa la comanda en sí.

## 6. Fuera de alcance de esta propuesta

Este documento solo cubre el contrato y la justificación de la separación,
como pide el enunciado. Quedan fuera (para una futura implementación):

- Tecnología concreta del microservicio (Node.js con `ws`, o Laravel
  Reverb, son candidatos razonables — no se decide aquí).
- Autenticación/autorización de los clientes finales del WebSocket (quién
  puede suscribirse a qué canal) — depende de cómo quede la autenticación
  general del sistema en el Lab 6.
- Persistencia de notificaciones perdidas (qué pasa si un cliente estuvo
  desconectado y se perdió un evento).
