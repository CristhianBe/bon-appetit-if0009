# Propuesta Técnica: Extracción del Microservicio de Notificaciones de Comandas a Cocina

## 1. Justificación Arquitectónica
El sistema monolítico actual de Bon Appetit maneja la lógica de negocio central (mesas, menú, pedidos y pagos). Sin embargo, el procesamiento de eventos en tiempo real para alertar al personal de cocina cuando una comanda cambia de estado (Pendiente -> En Preparación -> Listo) introduce un acoplamiento innecesario en el hilo principal del servidor HTTP.

Extraer el Módulo de Notificaciones de Comandas a Cocina como un microservicio independiente permite aislarnos de fallos: si la pasarela de notificaciones o las conexiones de red en pantalla de cocina experimentan lag o caídas, la toma de pedidos en las mesas (API principal) no se verá bloqueada ni ralentizada.

## 2. Ventajas del Desacoplamiento
- Escalabilidad Independiente: La cocina y el área de despacho requieren actualizaciones de estado de alta frecuencia (WebSockets/Polling). Escalar solo el servicio de notificaciones reduce costos operativos.
- Tolerancia a Fallos: Un fallo en la entrega de una notificación no interrumpe la persistencia de datos financieros o de inventario en la base de datos principal.
- Despliegue Autónomo: Permite actualizar la lógica de avisos (pantallas KDS, impresoras térmicas, alertas sonoras) sin necesidad de volver a desplegar todo el monolito Laravel.

## 3. Pila Tecnológica Sugerida
- Runtime / Servicio: Node.js (con NestJS o Express) o Go, optimizados para manejar alta concurrencia I/O no bloqueante.
- Capa de Transporte Real-Time: Socket.IO / WebSockets nativos para empujar cambios de estado directamente a las pantallas de cocina.
- Base de Datos Persistente / Caché: Redis para manejar pub/sub en memoria y presencia de sockets activos.

## 4. Mecanismo de Comunicación (Event-Driven con Colas)
Se propone un esquema Event-Driven Asíncrono:

1. Emisión del Evento: Cuando el endpoint POST /api/comandas o PUT /api/comandas/{id} en el monolito Laravel procesa con éxito un pedido, publica un evento ComandaCreada o EstadoComandaCambiado hacia un broker de mensajes (ej. RabbitMQ o Redis Pub/Sub).
2. Consumo y Procesamiento: El Microservicio de Notificaciones está suscrito a la cola de mensajes. Lee el payload del evento y procesa el envío.
3. Distribución: El microservicio emite el mensaje formateado vía WebSockets directamente al cliente web de la pantalla de cocina (KDS).
