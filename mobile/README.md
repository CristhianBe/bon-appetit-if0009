# Bon Appétit — App móvil (Laboratorio 11)

App móvil del proyecto, hecha con **Ionic + Vue 3 + Capacitor**, apuntando a la API real de Laravel (la misma del backend del proyecto).

## Qué incluye

- **4 páginas**: login, listado de productos, detalle de producto, formulario de creación/edición.
- **Enrutamiento** (`src/router/index.ts`): carga diferida por página, parámetro de ruta (`/productos/:id`), y un guardián global (`router.beforeEach`) que bloquea las rutas privadas sin sesión. El login usa `router.replace` (nunca queda en el historial) y el botón atrás de Android (`src/App.vue`) está manejado a mano: desde login o el listado, atrás cierra la app en vez de intentar volver al login.
- **Cliente de la API con un único punto de intercepción** (`src/services/http.ts`, con axios): adjunta el token a cada request automáticamente y, ante un 401, cierra la sesión y redirige al login. Nada de pegar el token a mano en cada llamada.
- **Consumo real de `/api/productos`**: listado con scroll infinito, detalle, y creación/edición (operación de escritura) — nada de datos simulados.
- **Token en almacenamiento cifrado** (`src/services/almacenSeguro.ts`, plugin `capacitor-secure-storage-plugin` → Android Keystore / iOS Keychain). El token **no** se guarda en Preferences ni en localStorage.
- **Caché offline** (`src/services/cacheProductos.ts`): guarda el último listado consultado; con el plugin `@capacitor/network` detecta si hay conexión y, si no la hay, muestra los datos cacheados con el aviso "Datos no actualizados" + fecha/hora (`src/components/AvisoDesactualizado.vue`).
- **Notificaciones locales** (`src/services/notificaciones.ts`, `@capacitor/local-notifications`): pide el permiso en tiempo de ejecución y contempla el caso de "denegado permanentemente" (hay que explicarle al usuario cómo habilitarlo desde Ajustes).
- **Estados de carga/vacío/error con reintento** en todas las páginas que piden datos (`src/components/EstadoVacio.vue`, `EstadoError.vue`, spinners/skeletons de Ionic).
- **Responsive + áreas seguras**: grilla de 1 a 3 columnas según ancho de pantalla, usando las variables de safe-area que trae Ionic.

## Estructura

```
mobile/
├── src/
│   ├── views/              # LoginPage, ProductosPage, ProductoDetallePage, ProductoFormPage
│   ├── services/
│   │   ├── http.ts         # instancia de axios + interceptores (token + manejo de 401)
│   │   ├── authApi.ts      # login
│   │   ├── productosApi.ts # listado/detalle/crear/editar
│   │   ├── sesion.ts       # estado de sesión (token vigente, logout)
│   │   ├── almacenSeguro.ts# wrapper del storage cifrado
│   │   ├── cacheProductos.ts # caché local del listado + estado de red
│   │   ├── notificaciones.ts # permisos y envío de notificaciones locales
│   │   └── errores.ts      # helpers para mapear errores de la API
│   ├── components/         # EstadoVacio, EstadoError, AvisoDesactualizado
│   └── router/index.ts     # rutas + guardián de sesión
├── android/                 # carpeta nativa (generada por Capacitor, también va en el repo)
├── .env.example             # plantilla de configuración (el .env real NO se sube, está en .gitignore)
└── capacitor.config.ts
```

## Cómo correrla

**1. Backend** — tiene que estar accesible en la red local, no solo en `localhost`:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**2. Configurá la URL de la API** — copiá `.env.example` a `.env` y ajustá según dónde vayas a probar:
```
# Emulador de Android Studio:
VITE_API_URL=http://10.0.2.2:8000/api

# Celular físico (misma red WiFi que la PC):
VITE_API_URL=http://<IP-LAN-de-tu-PC>:8000/api
```
Esta URL se compila dentro del JS, así que **cada vez que la cambies hay que recompilar** (paso 4), no alcanza con guardar el `.env`.

**3. Instalá dependencias** (primera vez, o si cambia el `package.json`):
```bash
cd mobile
npm install
```

**4. Compilá y sincronizá con Android** (repetir cada vez que cambies código, el `.env`, o instales un plugin nuevo):
```bash
npm run build
npx cap sync android
```
O todo junto con:
```bash
npm run android
```

**5. Abrí el proyecto nativo** (si no usaste `npm run android`):
```bash
npx cap open android
```
Se abre Android Studio. Esperá el "Gradle sync", elegí el emulador o un dispositivo físico conectado por USB, y dale ▶ Run.

**6. Login de prueba** (si la base de datos tiene el seeder de siempre):
```
mesero@bonappetit.test / Password123!
```
Si no entra, puede que falte correr `php artisan migrate:fresh --seed` en el backend.

## Pendiente de probar a mano

Esto se armó y compila/corre bien, pero no se probó dentro de un emulador/dispositivo real todavía — quedó para quien siga con esta parte:

- **Permiso de notificaciones denegado permanentemente**: rechazar el permiso dos veces (o bloquearlo desde Ajustes del sistema) y confirmar que se explica cómo activarlo manualmente.
- **Modo sin conexión**: activar el modo avión con un producto ya cargado antes, y confirmar que aparece el aviso de "datos no actualizados" con la fecha correcta.
- **Pantallas y áreas seguras**: probar en un emulador chico y uno grande, y en horizontal, para confirmar que la grilla y el contenido no quedan tapados por el notch/barra de gestos.

## Nota sobre el backend

Al armar esto se confirmó contra el backend real (no se asumió nada):
- El login devuelve 401 con `{"message":"Credenciales incorrectas."}` si las credenciales son malas.
- El token dura 30 minutos.
- `/api/productos` es público para listar/ver, pero crear o editar un producto requiere estar autenticado (no hay restricción de rol todavía — cualquier usuario logueado puede crear/editar).
- El listado de productos viene paginado (`data` + `meta.last_page`).
- CORS ya acepta el origen `http://localhost` (el que usa el WebView de Capacitor), así que no hizo falta tocar `config/cors.php`.
