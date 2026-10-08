<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        .form-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            margin: 20px auto;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border: 1px solid #eee;
        }

        .form-card fieldset {
            border: none;
            padding: 0;
            margin: 0;
        }

        .form-card legend {
            font-size: 1.3rem;
            font-weight: bold;
            color: #5a1217;
            margin-bottom: 20px;
            padding: 0;
            width: 100%;
            border-bottom: 2px solid #fdf8f5;
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        .form-group input {
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #5a1217;
        }

        .btn-submit {
            background-color: #5a1217;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #8b0000;
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
        <h1>Reservar una Mesa</h1>
        
        <div class="form-card">
            <form action="/mesas" method="GET">
                <fieldset>
                    <legend>Datos de la Reserva</legend>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Peréz Lopez">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" required placeholder="@gmail.com">
                    </div>

                    <div class="form-group">
                        <label for="personas">Número de Personas:</label>
                        <input type="number" id="personas" name="personas" min="1" max="12" required placeholder="3">
                    </div>

                    <button type="submit" class="btn-submit">Confirmar Reserva</button>
                </fieldset>
            </form>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>