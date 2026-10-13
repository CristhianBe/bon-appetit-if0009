<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        /* - CSS Grid: Contenedor principal centrado bidimensionalmente en la pantalla
           - Flexbox: Estructuración unidimensional para los campos del formulario y botones*/

        body {
            background-color: #fdf8f5;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            display: grid;
            place-items: center;
            min-height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            box-sizing: border-box;
        }

        .auth-container h1 {
            color: #5a1217;
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 20px;
        }

        .auth-form {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
        }

        .auth-form fieldset {
            border: none;
            padding: 0;
            margin: 0;
        }

        .auth-form legend {
            font-size: 1.2rem;
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

        .btn-primary {
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

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #8b0000;
            outline: 2px solid #5a1217;
        }
    </style>
</head>
<body>
    <main class="auth-container">
        <h1>Iniciar Sesión</h1>
        
        <form action="/mesas" method="GET" class="auth-form" aria-label="Formulario de inicio de sesión">
            <fieldset>
                <legend>Datos de Usuario</legend>

                <div class="form-group">
                    <label for="usuario">Nombre de Usuario:</label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        required 
                        aria-required="true" 
                        placeholder="Ej: usuario123"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        aria-required="true" 
                        placeholder="ejemplo@correo.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        aria-required="true" 
                        minlength="6"
                    >
                </div>

                <button type="submit" class="btn-primary">Ingresar</button>
            </fieldset>
        </form>
    </main>
</body>
</html>