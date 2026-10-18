<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Platillos - Bon Appétit</title>
    
    <!-- Cargando CSS y JS desde la carpeta resources usando Vite -->
    @vite(['resources/css/app.css', 'resources/js/menu.js'])
</head>
<body>
    <header class="main-header">
        <nav class="nav-bar" aria-label="Navegación principal">
            <span class="logo">Bon Appétit</span>
            <ul>
                <li><a href="/mesas">Mesas</a></li>
                <li><a href="/menu" aria-current="page">Menú</a></li>
                <li><a href="/pedidos">Pedidos</a></li>
                <li><a href="/reservas">Reservaciones</a></li>
                <li><a href="/login">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main class="menu-container">
        <h1>Nuestra Carta</h1>

        <div class="search-container">
            <label for="buscar-platillo" class="sr-only">Buscar platillo</label>
            <input
                type="search"
                id="buscar-platillo"
                placeholder="Buscar platillo por nombre..."
                aria-label="Buscar platillo"
            >
        </div>

        <!-- Contenedor dinámico donde ListadoView renderizará los platillos -->
        <div id="app-container"></div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>