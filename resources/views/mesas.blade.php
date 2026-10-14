<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Mesas - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        /* - CSS Grid: Se utiliza para la maquetación del contenedor principal (.dashboard-layout)
             y la cuadrícula responsiva de tarjetas (.mesas-grid).
           - Flexbox: Se utiliza para la distribución unidimensional dentro de las tarjetas (.mesa-card),
             la alineación de la barra de navegación (.nav-bar) y los diálogos (.modal-header).*/

        body {
            background-color: #fdf8f5;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .dashboard-layout {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .dashboard-layout h1 {
            color: #5a1217;
            font-size: 2.2rem;
            margin-bottom: 25px;
        }

        /* Cuadrícula principal de Mesas en CSS Grid */
        .mesas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Tarjeta individual con Flexbox */
        .mesa-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mesa-card h2 {
            color: #5a1217;
            margin: 0;
            font-size: 1.5rem;
        }

        .mesa-card p {
            margin: 0;
            color: #555;
            font-size: 0.95rem;
        }

        /* Badges de Estado estilizadas */
        .badge {
            display: inline-block;
            align-self: flex-start;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            margin: 6px 0 10px 0;
        }

        .disponible .badge {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .ocupada .badge {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .reservada .badge {
            background-color: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffe0b2;
        }

        /* Botón Secundario Estilizado */
        .btn-secondary {
            background-color: #ffffff;
            color: #5a1217;
            border: 1.5px solid #5a1217;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-secondary:hover,
        .btn-secondary:focus {
            background-color: #5a1217;
            color: #ffffff;
            outline: 2px solid #5a1217;
        }

        /* Estilos para Modales/Diálogos emergentes */
        dialog.modal-detalle {
            border: none;
            border-radius: 12px;
            padding: 24px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            font-family: inherit;
        }

        dialog::backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .modal-header h2 {
            color: #5a1217;
            margin: 0;
            font-size: 1.4rem;
        }

        .modal-body p {
            margin: 8px 0;
            font-size: 1rem;
        }

        .btn-cerrar {
            background: #8b0000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            float: right;
            margin-top: 15px;
            transition: background-color 0.2s;
        }

        .btn-cerrar:hover,
        .btn-cerrar:focus {
            background-color: #5a1217;
            outline: 2px solid #5a1217;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <nav class="nav-bar" aria-label="Navegación principal">
            <span class="logo">Bon Appétit</span>
            <ul>
                <li><a href="/mesas" aria-current="page">Mesas</a></li>
                <li><a href="/menu">Menú</a></li>
                <li><a href="/pedidos">Pedidos</a></li>
                <li><a href="/reservas">Reservaciones</a></li>
                <li><a href="/login">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard-layout">
        <h1>Control de Mesas</h1>
        
        <section class="mesas-grid" aria-label="Listado de mesas del salón">
            <!-- Mesa 01 -->
            <article class="mesa-card disponible">
                <h2>Mesa 01</h2>
                <p>Capacidad: 4 personas</p>
                <span class="badge">Disponible</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa1').showModal()" aria-haspopup="dialog">Ver Detalle</button>
            </article>

            <!-- Mesa 02 -->
            <article class="mesa-card ocupada">
                <h2>Mesa 02</h2>
                <p>Capacidad: 2 personas</p>
                <span class="badge">Ocupada</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa2').showModal()" aria-haspopup="dialog">Ver Detalle</button>
            </article>

            <!-- Mesa 03 -->
            <article class="mesa-card reservada">
                <h2>Mesa 03</h2>
                <p>Capacidad: 6 personas</p>
                <span class="badge">Reservada</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa3').showModal()" aria-haspopup="dialog">Ver Detalle</button>
            </article>
        </section>

        <!-- Modales emergentes con los detalles -->
        <dialog id="modal-mesa1" class="modal-detalle" aria-labelledby="title-modal-1">
            <div class="modal-header">
                <h2 id="title-modal-1">Detalle - Mesa 01</h2>
            </div>
            <div class="modal-body">
                <p><strong>Capacidad Máxima:</strong> 4 personas</p>
                <p><strong>Ocupación Actual:</strong> Sin clientes</p>
                <p><strong>Estado:</strong> Disponible</p>
                <p><strong>Comanda:</strong> No hay orden activa</p>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-mesa1').close()">Cerrar</button>
        </dialog>

        <dialog id="modal-mesa2" class="modal-detalle" aria-labelledby="title-modal-2">
            <div class="modal-header">
                <h2 id="title-modal-2">Detalle - Mesa 02</h2>
            </div>
            <div class="modal-body">
                <p><strong>Capacidad Máxima:</strong> 2 personas</p>
                <p><strong>Ocupación Actual:</strong> 2 personas</p>
                <p><strong>Estado:</strong> Ocupada</p>
                <p><strong>Pedido Asociado:</strong> Pedido #101</p>
                <p><strong>Tiempo en mesa:</strong> 25 min</p>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-mesa2').close()">Cerrar</button>
        </dialog>

        <dialog id="modal-mesa3" class="modal-detalle" aria-labelledby="title-modal-3">
            <div class="modal-header">
                <h2 id="title-modal-3">Detalle - Mesa 03</h2>
            </div>
            <div class="modal-body">
                <p><strong>Capacidad Máxima:</strong> 6 personas</p>
                <p><strong>Ocupación Actual:</strong> Reservada</p>
                <p><strong>Nombre del Cliente:</strong> Carlos Mendoza</p>
                <p><strong>Hora de Reserva:</strong> 7:00 PM</p>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-mesa3').close()">Cerrar</button>
        </dialog>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>