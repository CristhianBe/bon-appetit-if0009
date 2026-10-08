<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>
    </style>
</head>
<body>
    <main class="auth-container">
        <h1>Iniciar Sesión</h1>
        <form action="/mesas" method="GET" class="auth-form">
            <fieldset>
                <legend>Datos de Usuario</legend>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <button type="submit" class="btn-primary">Ingresar</button>
            </fieldset>
        </form>
    </main>
</body>
</html>