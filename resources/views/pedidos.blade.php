<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        dialog.modal-detalle {
            border: none;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            font-family: inherit;
        }

        dialog::backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .modal-header h2 {
            margin: 0;
            color: #5a1217;
            font-size: 1.4rem;
        }

        .tabla-pedido {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .tabla-pedido th, 
        .tabla-pedido td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .tabla-pedido th {
            background-color: #fdf8f5;
            color: #5a1217;
        }

        .resumen-total {
            text-align: right;
            font-size: 1.1rem;
            margin-top: 15px;
            color: #333;
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
            font-weight: bold;
        }

        .btn-cerrar:hover {
            background: #a00000;
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
        <h1>Gestión de Pedidos</h1>
        
        <div class="mesas-grid">
            <!-- Pedido #101 -->
            <article class="mesa-card">
                <h2>Pedido #101 </h2>
                <p><strong>Estado del Pedido:</strong> En proceso </p>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-pedido101').showModal()">Ver Detalle</button>
            </article>

            <!-- Pedido #102 -->
            <article class="mesa-card">
                <h2>Pedido #102 </h2>
                <p><strong>Estado:</strong> Entregado</p>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-pedido102').showModal()">Ver Detalle</button>
            </article>
        </div>


        <dialog id="modal-pedido101" class="modal-detalle">
            <div class="modal-header">
                <h2>Detalle Orden #101</h2>
            </div>
            <div class="modal-body">
                <p><strong>Estado del Pedido:</strong> En preparación
             </p>
                
                <table class="tabla-pedido">
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Producto</th>
                            <th>Precio Unit.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Hamburguesa de Pollo</td>
                            <td>₡5,500</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Papas Fritas Pequeñas</td>
                            <td>₡1,800</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Coca Cola 600ml</td>
                            <td>₡2,400</td>
                        </tr>
                    </tbody>
                </table>

                <div class="resumen-total">
                    <strong>Total Acomulado: ₡9,700</strong>
                </div>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-pedido101').close()">Cerrar</button>
        </dialog>

        <!-- MODAL DETALLE PEDIDO #102 -->
        <dialog id="modal-pedido102" class="modal-detalle">
            <div class="modal-header">
                <h2>Detalle Orden #102</h2>
            </div>
            <div class="modal-body">
                <p><strong>Estado del Pedido:</strong> Entregado </p>
                
                <table class="tabla-pedido">
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Producto</th>
                            <th>Precio Unit.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2</td>
                            <td>Pasta Alfredo con Pollo</td>
                            <td>₡13,000</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Ensalada César</td>
                            <td>₡4,200</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jugo Natural de Fresa</td>
                            <td>₡3,000</td>
                        </tr>
                    </tbody>
                </table>

                <div class="resumen-total">
                    <strong>Total Cobrado: ₡20,200</strong>
                </div>
            </div>
            <button class="btn-cerrar" onclick="document.getElementById('modal-pedido102').close()">Cerrar</button>
        </dialog>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>