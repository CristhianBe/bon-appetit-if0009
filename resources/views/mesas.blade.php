<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Mesas - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        dialog.modal-detalle {
            border: none;
            border-radius: 12px;
            padding: 24px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
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

        .modal-body p {
            margin: 8px 0;
            font-size: 1rem;
        }

        .btn-cerrar {
            background: #8b0000;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            float: right;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <nav class="nav-bar">
            <span class="logo">Bon Appétit</span>
            <ul>
                <li><a href="/mesas">Mesas</a></li>
                <li><a href="/pedidos">Pedidos</a></li>
                <li><a href="/reservas">Reservaciones</a></li>
                <li><a href="/login">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard-layout">
        <h1>Control de Mesas</h1>
        
        <section class="mesas-grid">
            <!-- Mesa 01 -->
            <article class="mesa-card disponible">
                <h2>Mesa 01</h2>
                <p>Capacidad: 4 personas</p>
                <span class="badge">Disponible</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa1').showModal()">Ver Detalle</button>
            </article>

            <!-- Mesa 02 -->
            <article class="mesa-card ocupada">
                <h2>Mesa 02</h2>
                <p>Capacidad: 2 personas</p>
                <span class="badge">Ocupada</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa2').showModal()">Ver Detalle</button>
            </article>

            <!-- Mesa 03 -->
            <article class="mesa-card reservada">
                <h2>Mesa 03</h2>
                <p>Capacidad: 6 personas</p>
                <span class="badge">Reservada</span>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-mesa3').showModal()">Ver Detalle</button>
            </article>
        </section>

        <!-- Modales emergentes con los detalles -->
        <dialog id="modal-mesa1" class="modal-detalle">
            <div class="modal-header">
                <h2>Detalle - Mesa 01</h2>
            </div>
            <div class="modal-body">
                <p><strong>Capacidad Máxima:</strong> 4 personas</p>
                <p><strong>Ocupación Actual:</strong> Sin clientes</p>
                <p><strong>Estado:</strong> Disponible</p>
                <p><strong>Comanda:</strong> No hay orden activa</p>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-mesa1').close()">Cerrar</button>
        </dialog>

        <dialog id="modal-mesa2" class="modal-detalle">
            <div class="modal-header">
                <h2>Detalle - Mesa 02</h2>
            </div>
            <div class="modal-body">
                <p><strong>Capacidad Máxima:</strong> 2 personas</p>
                <p><strong>Ocupación Actual:</strong> 2 personas</p>
                <p><strong>Estado:</strong> Ocupada</p>
                <p><strong>Pedido Asociado:</strong> Pedido #101 </p>
                <p><strong>Tiempo en mesa:</strong> 25 min</p>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-mesa2').close()">Cerrar</button>
        </dialog>

        <dialog id="modal-mesa3" class="modal-detalle">
            <div class="modal-header">
                <h2>Detalle - Mesa 03</h2>
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