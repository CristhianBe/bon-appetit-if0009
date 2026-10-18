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
        
        <!-- Botón para agregar -->
        <div class="header-actions">
            <button id="btn-crear" class="btn-card btn-crear">➕ Agregar Platillo</button>
        </div>
        
        <div id="app-container"></div>

        <!-- El Formulario (Modal Oculto) -->
        <dialog id="modal-formulario" class="modal-dialog">
            <h2 id="modal-titulo" class="modal-title">Nuevo Platillo</h2>
            <form id="form-platillo" class="modal-form">
                <input type="hidden" id="platillo-id">
                
                <div class="form-group">
                    <label for="platillo-categoria">Categoría:</label>
                    <select id="platillo-categoria" required class="form-control">
                        <option value="">Cargando categorías...</option>
                    </select>
                    <span id="error-categoria_id" class="error-msg"></span>
                </div>
                
                <div class="form-group">
                    <label for="platillo-nombre">Nombre:</label>
                    <input type="text" id="platillo-nombre" required class="form-control">
                    <span id="error-nombre" class="error-msg"></span>
                </div>
                
                <div class="form-group">
                    <label for="platillo-descripcion">Descripción:</label>
                    <textarea id="platillo-descripcion" required class="form-control"></textarea>
                    <span id="error-descripcion" class="error-msg"></span>
                </div>
                
                <div class="form-group">
                    <label for="platillo-precio">Precio (₡):</label>
                    <input type="number" id="platillo-precio" required class="form-control">
                    <span id="error-precio" class="error-msg"></span>
                </div>
                
                <div class="form-group">
                    <label for="platillo-imagen">URL de Imagen:</label>
                    <input type="url" id="platillo-imagen" class="form-control">
                    <span id="error-imagen" class="error-msg"></span>
                </div>
                
                <div class="modal-actions">
                    <button type="button" id="btn-cancelar" class="btn-cancelar">Cancelar</button>
                    <button type="submit" class="btn-card">Guardar</button>
                </div>
            </form>
        </dialog>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>