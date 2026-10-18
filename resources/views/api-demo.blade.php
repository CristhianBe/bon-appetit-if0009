<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApiClient — página de uso (Lab 9, Persona 2)</title>
    @vite(['resources/css/app.css', 'resources/js/api-demo.js'])
    <style>
        .demo-page { max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: system-ui, sans-serif; }
        .demo-panel { background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; }
        .demo-panel h2 { margin-top: 0; font-size: 1.1rem; }
        .demo-panel pre { background: #f5f5f5; padding: 10px; border-radius: 6px; overflow-x: auto; font-size: 0.85rem; }
        .demo-status { font-weight: bold; margin: 0 0 8px; }
        .demo-status--ok { color: #1e5e3a; }
        .demo-status--error { color: #b91c1c; }
        .demo-status--cancel { color: #904d00; }
    </style>
</head>
<body>
    <main class="demo-page">
        <h1>ApiClient / ApiError — página corta de uso</h1>
        <p>Laboratorio 9, Persona 2. Cada panel llama a la API real del proyecto al cargar la página.</p>

        <section class="demo-panel">
            <h2>1. Ejemplo exitoso — POST /login con credenciales válidas</h2>
            <div id="demo-exito">Cargando…</div>
        </section>

        <section class="demo-panel">
            <h2>2. Ejemplo con error — POST /login con datos inválidos (422)</h2>
            <div id="demo-error">Cargando…</div>
        </section>

        <section class="demo-panel">
            <h2>3. Cancelación de solicitud (AbortSignal)</h2>
            <div id="demo-cancelacion">Cargando…</div>
        </section>

        <section class="demo-panel">
            <h2>4. Token inválido → 401 real (GET /user)</h2>
            <div id="demo-401">Cargando…</div>
        </section>

        <section class="demo-panel">
            <h2>5. Evento de aviso (ApiClient.UNAUTHORIZED_EVENT)</h2>
            <div id="demo-401-evento">Esperando el 401 del panel 4…</div>
        </section>
    </main>
</body>
</html>
